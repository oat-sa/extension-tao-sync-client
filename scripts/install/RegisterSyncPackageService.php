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
use oat\taoSyncClient\model\dataProvider\SyncClientDataProviderInterface;
use oat\taoSyncClient\model\syncPackage\migration\MigrationInterface;
use oat\taoSyncClient\model\syncPackage\migration\RdsMigrationService;
use oat\taoSyncClient\model\syncPackage\storage\SyncPackageFileSystemStorageService;
use oat\taoSyncClient\model\syncPackage\SyncPackageService;
use oat\taoSyncClient\model\syncQueue\SyncQueueService;

class RegisterSyncPackageService extends InstallAction
{
    /**
     * @param $params
     * @return common_report_Report
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        try {
            $syncPackageService = $this->getServiceManager()->get(SyncPackageService::SERVICE_ID);
        } catch (ServiceNotFoundException $e) {
            $syncPackageService = new SyncPackageService([
                SyncPackageService::OPTION_MIGRATION => RdsMigrationService::class,
                SyncPackageService::OPTION_MIGRATION_PARAMS => ['default'],
                SyncPackageService::OPTION_DATA_PROVIDER => SyncClientDataProviderInterface::class,
                SyncPackageService::OPTION_STORAGE => SyncPackageFileSystemStorageService::class,
            ]);
        }

        $syncPackageClass = $syncPackageService->getOption(SyncPackageService::OPTION_MIGRATION);
        $syncPackageMigrationParams = $syncPackageService->getOption(SyncPackageService::OPTION_MIGRATION_PARAMS);
        /** @var MigrationInterface $storage */
        $storage = new $syncPackageClass([RdsMigrationService::OPTION_PERSISTENCE => current($syncPackageMigrationParams)]);
        $storage->setServiceLocator($this->getServiceManager());
        $storage->createStorage();
        $this->getServiceManager()->register(SyncQueueService::SERVICE_ID, $syncPackageService);
        return new common_report_Report(common_report_Report::TYPE_SUCCESS, __('SyncClient queue storage successfully created'));
    }
}
