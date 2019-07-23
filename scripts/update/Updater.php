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
use oat\taoSyncClient\model\dataProvider\providers\DeliveryLogDataProviderService;
use oat\taoSyncClient\model\dataProvider\providers\LtiUserDataProviderService;
use oat\taoSyncClient\model\dataProvider\providers\ResultsDataProviderService;
use oat\taoSyncClient\model\dataProvider\providers\TestSessionDataProviderService;
use oat\taoSyncClient\model\dataProvider\SyncClientDataProviderService;
use oat\taoSyncClient\model\dataProvider\SyncClientDataProviderServiceInterface;
use oat\taoSyncClient\model\orgProvider\OrgIdProviderInterface;
use oat\taoSyncClient\model\orgProvider\providers\TestCenterOrgIdService;
use oat\taoSyncClient\model\syncPackage\migration\RdsMigrationService;
use oat\taoSyncClient\model\syncPackage\storage\SyncPackageFileSystemStorageService;
use oat\taoSyncClient\model\syncPackage\SyncPackageService;
use oat\taoSyncClient\model\syncQueue\SyncQueueInterface;

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
                    SyncPackageService::OPTION_STORAGE => SyncPackageFileSystemStorageService::class,
                ])
            );

            $this->getServiceManager()->register(SyncClientDataProviderServiceInterface::SERVICE_ID,
                new SyncClientDataProviderService([
                    SyncClientDataProviderServiceInterface::OPTION_PROVIDERS => [
                        SyncQueueInterface::PARAM_EVENT_TYPE_DELIVERY_LOG => DeliveryLogDataProviderService::class,
                        SyncQueueInterface::PARAM_EVENT_TYPE_LTI_USER     => LtiUserDataProviderService::class,
                        SyncQueueInterface::PARAM_EVENT_TYPE_RESULTS      => ResultsDataProviderService::class,
                        SyncQueueInterface::PARAM_EVENT_TYPE_TEST_SESSION => TestSessionDataProviderService::class,
                    ]
                ]));

            $this->getServiceManager()->register(OrgIdProviderInterface::SERVICE_ID, new TestCenterOrgIdService());

            $this->addReport(common_report_Report::createInfo('Create migrations and storage: php index.php \'oat\taoSyncClient\scripts\install\RegisterSyncPackageService\''));
            $this->setVersion('0.2.0');
        }
    }
}
