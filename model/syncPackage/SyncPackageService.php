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
use oat\taoSyncClient\model\syncQueue\storage\SyncQueueStorageInterface;
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
     * @return mixed|storage\SyncPackageStorageInterface
     */
    public function getStorageService()
    {
        return $this->getOption(self::OPTION_STORAGE);
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
        return $this->getOption(self::OPTION_DATA_PROVIDER);
    }

    public function getMigrationService()
    {
        return $this->getOption(self::OPTION_MIGRATION);
    }

    /**
     * @param array $dataTypes
     * @return common_report_Report
     * @throws common_exception_Error
     */
    public function create($dataTypes = [])
    {
        $data = [];
        $report = common_report_Report::createInfo('Package creation started');
        if ($this->getStorageService()->isValid()) {
            $reportCounts = [];
            $queuedTasks = $this->getSyncQueueService()->getTasks($dataTypes, $this->getOption('limit'));
            foreach ($queuedTasks as $task) {
                $data[] = $this->getData($task);
                $reportCounts = $this->increaseTypeCount($reportCounts, $task[SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_TYPE]);
            }
            $migrationId = $this->getStorageService()->save($data);
            $migratedCount = $this->getSyncQueueService()->markAsMigrated($migrationId, $queuedTasks);

            $report->add(common_report_Report::createInfo($this->getReportMessage($migrationId, $migratedCount, $reportCounts)));
        }
        return $report;
    }

    private function increaseTypeCount($reportCounts, $type)
    {
        if (!array_key_exists($type, $reportCounts)) {
            $reportCounts[$type] = 0;
        }
        $reportCounts[$type]++;
        return $reportCounts;
    }

    private function getReportMessage($migrationId, $migratedCount, $reportCounts)
    {
        $reportMessage = 'Within migration '.(int)$migrationId.' were migrated '. (int)$migratedCount. ' records from the SyncQueue';
        $reportMessage .= "\nMigrated types:\n";
        foreach ($reportCounts as $key => $reportCount) {
            $reportMessage .= $key.': '.(int)$reportCount."\n";
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
