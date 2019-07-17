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

namespace oat\taoSyncClient\scripts\update;


use common_ext_ExtensionUpdater;
use common_report_Report;
use oat\taoSyncClient\model\dataProvider\SyncClientDataProviderInterface;
use oat\taoSyncClient\model\syncPackage\migration\RdsMigrationService;
use oat\taoSyncClient\model\syncPackage\storage\SyncPackageFileSystemStorageService;
use oat\taoSyncClient\model\syncPackage\SyncPackageService;

class Updater extends common_ext_ExtensionUpdater
{
    public function update($initialVersion)
    {
        if ($this->isVersion('0.1.0')) {
            $this->getServiceManager()->register(
                SyncPackageService::SERVICE_ID,
                new SyncPackageService([
                    SyncPackageService::OPTION_MIGRATION => RdsMigrationService::class,
                    SyncPackageService::OPTION_MIGRATION_PARAMS => ['default'],
                    SyncPackageService::OPTION_DATA_PROVIDER => SyncClientDataProviderInterface::class,
                    SyncPackageService::OPTION_STORAGE => SyncPackageFileSystemStorageService::class,
                ])
            );
            $this->addReport(common_report_Report::createInfo('Create migrations and storage: php index.php \'oat\taoSyncClient\scripts\install\RegisterSyncPackageService\''));
            $this->setVersion('0.2.0');
        }
    }
}
