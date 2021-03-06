<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2019  (original work) Open Assessment Technologies SA;
 *
 * @author Oleksandr Zagovorychev <zagovorichev@1pt.com>
 */

namespace oat\taoSyncClient\model\syncPackage;


use common_exception_Error;
use common_report_Report;
use oat\oatbox\service\ConfigurableService;
use oat\taoSyncClient\model\dataProvider\SyncPackageDataProviderInterface;
use oat\taoSyncClient\model\dataProvider\SyncPackageDataProviderServiceInterface;
use oat\taoSyncClient\model\exception\SyncClientException;
use oat\taoSyncClient\model\syncPackage\migration\MigrationInterface;
use oat\taoSyncClient\model\syncPackage\storage\SyncPackageStorageInterface;
use oat\taoSyncClient\model\syncQueue\SyncQueueInterface;
use oat\taoSyncClient\model\syncQueue\SyncQueueService;

class SyncPackageService extends ConfigurableService implements SyncPackageInterface
{
    /**
     *  FileStorageService implements SyncPackageStorageInterface
     */
    const OPTION_STORAGE = 'storage';
    /**
     *  MigrationService implements MigrationInterface
     */
    const OPTION_MIGRATION = 'migration';
    /**
     *  Data limit per one package.
     */

    const PARAM_LTI_USER = 'lti_user';
    const PARAM_DELIVERY_LOG = 'delivery_log';
    const PARAM_RESULTS = 'results';
    const PARAM_TEST_SESSION = 'test_session';
    const PARAM_LIMIT = 'limit';

    /**
     * @var SyncPackageStorageInterface
     */
    private $storageService;

    /**
     * @var MigrationInterface
     */
    private $migrationService;

    /**
     * @var SyncPackageDataProviderInterface
     */
    private $dataProviderService;

    /**
     * Report of the last create operation
     * @var common_report_Report
     */
    private $report;

    /**
     * @param array $options
     * @throws SyncClientException
     * @throws common_exception_Error
     */
    public function setOptions(array $options)
    {
        $this->checkOptions($options);
        parent::setOptions($options);
    }

    /**
     * @param $name
     * @param $value
     * @throws SyncClientException
     */
    public function setOption($name, $value)
    {
        $this->checkOptions([$name => $value]);
        parent::setOption($name, $value);
    }

    /**
     * @param array $options
     * @throws SyncClientException
     */
    private function checkOptions(array $options)
    {
        if (array_key_exists(self::OPTION_STORAGE, $options)
            && !($options[self::OPTION_STORAGE] instanceof SyncPackageStorageInterface)) {
            throw new SyncClientException(self::OPTION_STORAGE . ' parameter has to be instance of SyncPackageStorageInterface');
        }
        if (array_key_exists(self::OPTION_MIGRATION, $options)
            && !($options[self::OPTION_MIGRATION] instanceof MigrationInterface)) {
            throw new SyncClientException(self::OPTION_MIGRATION . ' parameter has to be instance of MigrationInterface');
        }
    }

    /**
     * Getting path to the folder with Generated packages for synchronization
     * @return mixed|storage\SyncPackageStorageInterface
     */
    private function getStorageService()
    {
        if (!$this->storageService) {
            $this->storageService = $this->propagate($this->getOption(self::OPTION_STORAGE));
        }
        return $this->storageService;
    }

    /**
     * @return array|SyncQueueInterface
     */
    private function getSyncQueueService()
    {
        return $this->getServiceLocator()->get(SyncQueueService::SERVICE_ID);
    }

    /**
     * @return mixed|SyncPackageDataProviderInterface
     */
    private function getDataProviderService()
    {
        if (!$this->dataProviderService) {
            $service = $this->getServiceLocator()->get(SyncPackageDataProviderServiceInterface::SERVICE_ID);
            $this->dataProviderService = $this->propagate($service);
        }
        return $this->dataProviderService;
    }

    /**
     * @return MigrationInterface
     */
    private function getMigrationService()
    {
        if (!$this->migrationService) {
            $this->migrationService = $this->propagate($this->getOption(self::OPTION_MIGRATION));
        }
        return $this->migrationService;
    }

    /**
     * @param array $dataTypes
     * @param integer $limit
     * @return int count of the selected for the package data
     * @throws common_exception_Error
     */
    public function create($dataTypes = [], $limit = 0)
    {
        $this->report = common_report_Report::createInfo('Package creation started');
        $dataCount = 0;
        if ($this->getStorageService()->isValid()) {
            $queuedTasks = $this->getSyncQueueService()->getTasks($dataTypes, $limit);
            $data = $this->getData($queuedTasks);
            if (array_key_exists('test_session', $data)) {
                // excluded tasks won't be marked as migrated
                $queuedTasks = $this->filterTestSessions($data, $queuedTasks);
            }
            $dataCount = count($data);
            if (!$dataCount || !count($queuedTasks)) {
                $this->report->add(common_report_Report::createSuccess('There is no data for migration.'));
            } else {
                $packageFileName = $this->getStorageService()->createPackage($data);
                if ($packageFileName) {
                    $this->getMigrationService()->add($packageFileName);
                    $migrationId = $this->getMigrationService()->getMigrationIdByPackage($packageFileName);
                    $migratedCount = $this->getSyncQueueService()->markAsMigrated($migrationId, $queuedTasks);
                    $this->report->add(common_report_Report::createSuccess($this->getReportMessage($migrationId,
                        $packageFileName, $migratedCount)));
                } else {
                    $this->report->add(common_report_Report::createFailure('Package file can not be created'));
                }
            }
        }
        return $dataCount;
    }

    /**
     * Test sessions can be skipped if delivery log was not synchronized
     * as importing could be done as split to parts, that is possible that not all of
     * delivery_log records were synchronized (so we do need to wait until all delivery log were migrated or prepared to migration)
     * @param $data
     * @param $queuedTasks
     * @return array
     */
    private function filterTestSessions(array $data, array $queuedTasks)
    {
        foreach ($queuedTasks as $key => $queuedTask) {
            if ($queuedTask['event_type'] === 'test_session'
                && !in_array($queuedTask['synchronizable_id'], $data['test_session'], true)) {
                unset($queuedTasks[$key]);
            }
        }
        return $queuedTasks;
    }

    /**
     * Report of the last create operation
     * @return common_report_Report
     */
    public function getReport()
    {
        return $this->report;
    }

    private function getReportMessage($migrationId, $packageFileName, $migratedCount)
    {
        $reportMessage = 'Within migration ' . (int)$migrationId . ' ('.$packageFileName.') were migrated ' . (int)$migratedCount . ' records from the SyncQueue';
        return $reportMessage;
    }

    /**
     * Get prepared data from the data providers
     * @param $task
     * @return array
     */
    private function getData($task)
    {
        return $task ? $this->getDataProviderService()->getData($task) : [];
    }
}
