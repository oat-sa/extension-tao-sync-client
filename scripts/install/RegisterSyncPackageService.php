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
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ServiceNotFoundException;
use oat\taoSync\package\storage\SyncFileSystem;
use oat\taoSyncClient\model\syncPackage\migration\MigrationInterface;
use oat\taoSyncClient\model\syncPackage\migration\RdsMigrationService;
use oat\taoSyncClient\model\syncPackage\SyncPackageInterface;
use oat\taoSyncClient\model\syncPackage\SyncPackageService;

/**
 * php index.php 'oat\taoSyncClient\scripts\install\RegisterSyncPackageService'
 *
 * Class RegisterSyncPackageService
 * @package oat\taoSyncClient\scripts\install
 */
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
                SyncPackageService::OPTION_MIGRATION => new RdsMigrationService([RdsMigrationService::OPTION_PERSISTENCE => 'default']),
                SyncPackageService::OPTION_STORAGE   => new SyncFileSystem(),
            ]);
            $syncPackageService->setServiceLocator($this->getServiceLocator());
        }
        /** @var MigrationInterface $storage */
        $migration = $syncPackageService->getOption(SyncPackageService::OPTION_MIGRATION);
        $migration->setServiceLocator($this->getServiceManager());
        $migration->createStorage();

        // storage for packages
        $storagePackageService = $syncPackageService->getOption(SyncPackageService::OPTION_STORAGE);

        if ($storagePackageService->getStorageName()) {
            $serviceManager = $this->getServiceManager();
            $service = $serviceManager->get(FileSystemService::SERVICE_ID);
            $service->createFileSystem($storagePackageService->getStorageName());
            $serviceManager->register(FileSystemService::SERVICE_ID, $service);
        }
        $storagePackageService->setServiceLocator($this->getServiceLocator());
        $storagePackageService->createStorage();

        $this->getServiceManager()->register(SyncPackageInterface::SERVICE_ID, $syncPackageService);
        return new common_report_Report(common_report_Report::TYPE_SUCCESS, __('SyncClient queue storage successfully created'));
    }
}
