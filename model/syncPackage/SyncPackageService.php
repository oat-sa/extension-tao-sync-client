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
use oat\taoSyncClient\model\syncPackage\migration\MigrationInterface;
use oat\taoSyncClient\model\syncPackage\migration\RdsMigrationService;
use oat\taoSyncClient\model\syncPackage\storage\SyncPackageStorageInterface;
use oat\taoSyncClient\model\syncQueue\SyncQueueInterface;
use oat\taoSyncClient\model\syncQueue\SyncQueueService;

class SyncPackageService extends ConfigurableService implements SyncPackageInterface
{
    const OPTION_STORAGE = 'storage';
    const OPTION_DATA_PROVIDER = 'dataProvider';
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
     * @var SyncClientDataProviderInterface
     */
    private $dataProviderService;

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
     * @return SyncQueueInterface
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
        if (!$this->dataProviderService) {
            $dataProviderClass = $this->getOption(self::OPTION_DATA_PROVIDER);
            $this->dataProviderService = new $dataProviderClass;
            $this->dataProviderService->setServiceLocator($this->getServiceLocator());

        }
        return $this->dataProviderService;
    }

    /**
     * @return MigrationInterface
     */
    public function getMigrationService()
    {
        if (!$this->migrationService) {
            $migrationClass = $this->getOption(self::OPTION_MIGRATION);
            $this->migrationService = new $migrationClass([RdsMigrationService::OPTION_PERSISTENCE => current($this->getOption(self::OPTION_MIGRATION_PARAMS))]);
            $this->migrationService->setServiceLocator($this->getServiceLocator());
        }
        return $this->migrationService;
    }

    /**
     * @param array $dataTypes
     * @return common_report_Report
     * @throws common_exception_Error
     */
    public function create($dataTypes = [])
    {
        $report = common_report_Report::createInfo('Package creation started');
        if ($this->getStorageService()->isValid()) {
            $reportCounts = [];
            $queuedTasks = $this->getSyncQueueService()->getTasks($dataTypes, $this->getOption('limit'));
            $data = $this->getData($queuedTasks);
            foreach ($data as $type => $items) {
                $reportCounts[$type] = count($items);
            }
            $packageFileName = $this->getStorageService()->createPackage($data);
            $this->getMigrationService()->add($packageFileName);
            $migrationId = $this->getMigrationService()->getMigrationIdByPackage($packageFileName);
            $migratedCount = $this->getSyncQueueService()->markAsMigrated($migrationId, $queuedTasks);
            $report->add(common_report_Report::createInfo($this->getReportMessage($migrationId, $migratedCount, $reportCounts)));
        }
        return $report;
    }

    private function getReportMessage($migrationId, $migratedCount, $reportCounts)
    {
        $reportMessage = 'Within migration ' . (int)$migrationId . ' were migrated ' . (int)$migratedCount . ' records from the SyncQueue';
        $reportMessage .= "\nMigrated types:\n";
        foreach ($reportCounts as $key => $reportCount) {
            $reportMessage .= $key . ': ' . (int)$reportCount . "\n";
        }
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
