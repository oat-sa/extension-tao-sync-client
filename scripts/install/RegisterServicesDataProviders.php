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

use oat\oatbox\extension\InstallAction;
use oat\taoSync\model\dataProvider\AbstractDataProvider;
use oat\taoSync\model\dataProvider\DataProviderCollection;
use common_Exception;
use oat\taoSyncClient\model\dataProvider\dataFormatter\LtiUser;
use oat\taoSyncClient\model\dataProvider\providers\DeliveryLogDataProviderService;
use oat\taoSyncClient\model\dataProvider\providers\LtiUserDataProviderService;
use oat\taoSyncClient\model\dataProvider\providers\ResultsDataProviderService;
use oat\taoSyncClient\model\dataProvider\providers\TestSessionDataProviderService;
use oat\taoSyncClient\model\syncPackage\SyncPackageService;

/**
 * php index.php 'oat\taoSyncClient\scripts\install\RegisterServicesDataProviders'
 *
 * Class RegisterSyncQueueRds
 * @package oat\taoSyncClient\scripts\install
 */
class RegisterServicesDataProviders extends InstallAction
{
    /**
     * @param $params
     * @throws common_Exception
     */
    public function __invoke($params)
    {
        $providers = [
            SyncPackageService::PARAM_DELIVERY_LOG => new DeliveryLogDataProviderService(),
            SyncPackageService::PARAM_LTI_USER => new LtiUserDataProviderService([
                AbstractDataProvider::OPTION_FORMATTER => new LtiUser()
            ]),
            SyncPackageService::PARAM_RESULTS => new ResultsDataProviderService(),
            SyncPackageService::PARAM_TEST_SESSION => new TestSessionDataProviderService(),
        ];

        $dataProviders = new DataProviderCollection([
            DataProviderCollection::OPTION_DATA_PROVIDERS => $providers
        ]);

        $this->getServiceManager()->register(DataProviderCollection::SERVICE_ID, $dataProviders);
    }
}
