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

namespace oat\taoSyncClient\scripts\install;

use common_report_Report;
use oat\oatbox\extension\InstallAction;
use oat\oatbox\service\ServiceNotFoundException;
use oat\taoSyncClient\model\syncQueue\storage\SyncQueueStorageRds;
use oat\taoSyncClient\model\syncQueue\SyncQueueService;

class RegisterSyncQueueRds extends InstallAction
{
    /**
     * @param $params
     * @return common_report_Report
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        try {
            $syncQueueService = $this->getServiceManager()->get(SyncQueueService::SERVICE_ID);
        } catch (ServiceNotFoundException $e) {
            $syncQueueService = new SyncQueueService([
                SyncQueueService::OPTION_SYNC_QUEUE_STORAGE => SyncQueueStorageRds::class,
                SyncQueueService::OPTION_SYNC_QUEUE_STORAGE_PARAMS => ['default'],
            ]);
        }

        $syncQueueStorageClass = $syncQueueService->getOption(SyncQueueService::OPTION_SYNC_QUEUE_STORAGE);
        $syncQueueStorageParams = $syncQueueService->getOption(SyncQueueService::OPTION_SYNC_QUEUE_STORAGE_PARAMS);
        /** @var SyncQueueStorageRds $storage */
        $storage = new $syncQueueStorageClass([SyncQueueStorageRds::OPTION_PERSISTENCE => current($syncQueueStorageParams)]);
        $storage->setServiceLocator($this->getServiceManager());
        $storage->createStorage();
        $this->getServiceManager()->register(SyncQueueService::SERVICE_ID, $syncQueueService);
        return new common_report_Report(common_report_Report::TYPE_SUCCESS, __('SyncClient queue storage successfully created'));
    }
}
