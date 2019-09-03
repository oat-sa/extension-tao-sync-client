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

use common_exception_NotFound;
use core_kernel_persistence_Exception;
use oat\oatbox\service\ConfigurableService;
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoSync\model\Result\SyncResultDataFormatter;
use oat\taoSyncClient\model\dataProvider\SyncPackageDataProviderInterface;

/**
 * Class ResultDataProviderService
 * @package oat\taoSyncClient\model\dataProvider\providers
 */
class ResultsDataProviderService extends ConfigurableService implements SyncPackageDataProviderInterface
{
    /**
     * @param array $deliveryExecutionIds
     * @return array
     * @throws common_exception_NotFound
     * @throws core_kernel_persistence_Exception
     */
    public function getData($deliveryExecutionIds = [])
    {
        $results = [];
        $formatter = $this->getDataFormatter();
        foreach ($deliveryExecutionIds as $deliveryExecutionId) {
            /** @var DeliveryExecution $deliveryExecution */
            $deliveryExecution = $this->getServiceProxy()->getDeliveryExecution($deliveryExecutionId);
            $results[] = $formatter->format($deliveryExecution);
        }
        return $results;
    }

    /**
     * @return SyncResultDataFormatter
     */
    private function getDataFormatter()
    {
        return $this->getServiceLocator()->get(SyncResultDataFormatter::SERVICE_ID);
    }

    /**
     * @return array|ServiceProxy
     */
    private function getServiceProxy()
    {
        return $this->getServiceLocator()->get(ServiceProxy::SERVICE_ID);
    }
}
