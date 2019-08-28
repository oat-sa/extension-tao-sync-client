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
use oat\taoSyncClient\model\dataProvider\SyncPackageDataProviderServiceInterface;
use oat\taoSyncClient\model\downloadService\DirectDownloadService;
use oat\taoSyncClient\model\downloadService\DownloadServiceInterface;
use oat\taoSyncClient\model\orgProvider\OrgIdProviderInterface;
use oat\taoSyncClient\model\orgProvider\providers\TestCenterOrgIdService;
use oat\taoSyncClient\model\syncPackage\migration\RdsMigrationService;
use oat\taoSyncClient\model\syncPackage\storage\SyncPackageFileSystemStorageService;
use oat\taoSyncClient\model\syncPackage\SyncPackageInterface;
use oat\taoSyncClient\model\syncPackage\SyncPackageService;
use oat\taoSyncClient\model\syncQueue\SyncQueueInterface;

class Updater extends common_ext_ExtensionUpdater
{
    public function update($initialVersion)
    {
        if ($this->isVersion('0.1.0')) {
            $this->getServiceManager()->register(SyncPackageDataProviderServiceInterface::SERVICE_ID,
                new SyncClientDataProviderService([
                    SyncPackageDataProviderServiceInterface::OPTION_PROVIDERS => [
                        SyncQueueInterface::PARAM_EVENT_TYPE_DELIVERY_LOG => new DeliveryLogDataProviderService(),
                        SyncQueueInterface::PARAM_EVENT_TYPE_LTI_USER     => new LtiUserDataProviderService(),
                        SyncQueueInterface::PARAM_EVENT_TYPE_RESULTS      => new ResultsDataProviderService(),
                        SyncQueueInterface::PARAM_EVENT_TYPE_TEST_SESSION => new TestSessionDataProviderService(),
                    ]
                ]));
            $this->getServiceManager()->register(OrgIdProviderInterface::SERVICE_ID, new TestCenterOrgIdService());
            $this->getServiceManager()->register(
                SyncPackageInterface::SERVICE_ID,
                new SyncPackageService([
                    SyncPackageService::OPTION_MIGRATION => new RdsMigrationService([RdsMigrationService::OPTION_PERSISTENCE => 'default']),
                    SyncPackageService::OPTION_STORAGE   => new SyncPackageFileSystemStorageService(),
                ])
            );
            $this->addReport(common_report_Report::createInfo('Create migrations and storage: php index.php \'oat\taoSyncClient\scripts\install\RegisterSyncPackageService\''));
            $this->setVersion('0.2.0');
        }
        if ($this->isVersion('0.2.0')) {
            $this->getServiceManager()->register(DownloadServiceInterface::SERVICE_ID, new DirectDownloadService());
            $this->addReport(common_report_Report::createInfo('Added taoPublishing dependency'));
            $this->setVersion('1.0.0');
        }

        $this->skip('1.0.0', '1.0.1');
    }
}
