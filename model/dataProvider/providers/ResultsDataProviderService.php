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
 */

namespace oat\taoSyncClient\model\dataProvider\providers;

use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoSync\model\dataProvider\AbstractDataProvider;
use oat\taoSyncClient\model\syncPackage\SyncPackageService;

/**
 * Class ResultDataProviderService
 * @package oat\taoSyncClient\model\dataProvider\providers
 */
class ResultsDataProviderService extends AbstractDataProvider
{
    /**
     * @inheritDoc
     */
    public function getType()
    {
        return SyncPackageService::PARAM_RESULTS;
    }

    /**
     * @param array $deliveryExecutionIds
     * @return array
     */
    public function getResources(array $deliveryExecutionIds = [])
    {
        $results = [];
        foreach ($deliveryExecutionIds as $deliveryExecutionId) {
            /** @var DeliveryExecution $deliveryExecution */
            $results[] = $this->getServiceProxy()->getDeliveryExecution($deliveryExecutionId);
        }
        return $results;
    }

    /**
     * @return array|ServiceProxy
     */
    private function getServiceProxy()
    {
        return $this->getServiceLocator()->get(ServiceProxy::SERVICE_ID);
    }
}
