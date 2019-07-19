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
use oat\taoSyncClient\model\dataProvider\SyncClientDataProviderInterface;
use oat\taoSyncClient\model\dataProvider\SyncClientDataProviderServiceInterface;
use oat\taoSyncClient\model\exception\SyncClientException;
use oat\taoSyncClient\model\syncPackage\migration\MigrationInterface;
use oat\taoSyncClient\model\syncPackage\storage\SyncPackageStorageInterface;
use oat\taoSyncClient\model\syncQueue\storage\SyncQueueStorageInterface;
use oat\taoSyncClient\model\syncQueue\SyncQueueInterface;
use oat\taoSyncClient\model\syncQueue\SyncQueueService;
use ReflectionClass;
use ReflectionException;

class SyncPackageService extends ConfigurableService implements SyncPackageInterface
{
    const OPTION_STORAGE = 'storage';
    const OPTION_MIGRATION = 'migration';
    const OPTION_MIGRATION_PARAMS = 'migration_params';

    const PARAM_LTI_USER = 'lti-user';
    const PARAM_DELIVERY_LOG = 'delivery-log';
    const PARAM_RESULTS = 'results';
    const PARAM_TEST_SESSION = 'test-session';

    /**
     * @var SyncPackageStorageInterface
     */
    private $storageService;

    /**
     * @var MigrationInterface
     */
    private $migrationService;

    /**
     * @return mixed|storage\SyncPackageStorageInterface
     */
    public function getStorageService()
    {
        if (!$this->storageService) {
            $storageClass = $this->getOption(self::OPTION_STORAGE);
            $this->storageService = new $storageClass;
            $this->storageService->setServiceLocator($this->getServiceLocator());
        }
        return $this->storageService;
    }

    /**
     * @return array|SyncQueueInterface
     */
    public function getSyncQueueService()
    {
        return $this->getServiceLocator()->get(SyncQueueService::SERVICE_ID);
    }

    /**
     * @return mixed|SyncClientDataProviderInterface
     */
    public function getDataProviderService()
    {
        return $this->getServiceLocator()->get(SyncClientDataProviderServiceInterface::SERVICE_ID);
    }

    /**
     * @return MigrationInterface
     * @throws SyncClientException
     */
    public function getMigrationService()
    {
        if (!$this->migrationService) {
            $migrationClass = $this->getOption(self::OPTION_MIGRATION);

            $hasStorage = true;
            try {
                $reflection = new ReflectionClass($migrationClass);
                if ($reflection->implementsInterface(MigrationInterface::class)) {
                    /** @var SyncQueueStorageInterface storageService */
                    $this->migrationService = new $migrationClass($this->getOption(self::OPTION_MIGRATION_PARAMS));
                    $this->migrationService->setServiceLocator($this->getServiceLocator());
                } else {
                    $hasStorage = false;
                }
            } catch (ReflectionException $e) {
                $hasStorage = false;
            }
            if (!$hasStorage) {
                throw new SyncClientException('taoSyncClient MigrationStorage is not initialized');
            }
        }
        return $this->migrationService;
    }

    /**
     * @param array $dataTypes
     * @return common_report_Report
     * @throws SyncClientException
     * @throws common_exception_Error
     */
    public function create($dataTypes = [])
    {
        $report = common_report_Report::createInfo('Package creation started');
        if ($this->getStorageService()->isValid()) {
            $queuedTasks = $this->getSyncQueueService()->getTasks($dataTypes, $this->getOption('limit'));
            if (!count($queuedTasks)) {
                $report->add(common_report_Report::createSuccess('There is no data for migration.'));
            } else {

                $data = $this->getData($queuedTasks);
                if (array_key_exists('test_session', $data)) {
                    $queuedTasks = $this->filterTestSessions($data, $queuedTasks);
                }

                if (!count($queuedTasks)) {
                    $report->add(common_report_Report::createSuccess('There is no data for migration.'));
                } else {
                    $packageFileName = $this->getStorageService()->createPackage($data);
                    $this->getMigrationService()->add($packageFileName);
                    $migrationId = $this->getMigrationService()->getMigrationIdByPackage($packageFileName);
                    $migratedCount = $this->getSyncQueueService()->markAsMigrated($migrationId, $queuedTasks);
                    $report->add(common_report_Report::createSuccess($this->getReportMessage($migrationId,
                        $packageFileName, $migratedCount)));
                }
            }
        }
        return $report;
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
            if ($queuedTask['event_type'] === 'test_session' && !in_array($queuedTask['synchronizable_id'], $data['test_session'], true)) {
                unset($queuedTasks[$key]);
            }
        }
        return $queuedTasks;
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
        return $this->getDataProviderService()
            ->setServiceLocator($this->getServiceLocator())
            ->getData($task);
    }
}
