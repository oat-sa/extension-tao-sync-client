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

use oat\oatbox\service\ConfigurableService;
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoDeliveryRdf\helper\DetectTestAndItemIdentifiersHelper;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoSyncClient\model\dataProvider\SyncClientDataProviderInterface;

/**
 * TODO: rewrite receiving data from ResultService. In old code it is using SyncEncryptedResultService
 *
 * Class ResultDataProviderService
 * @package oat\taoSyncClient\model\dataProvider\providers
 */
class ResultDataProviderService extends ConfigurableService implements SyncClientDataProviderInterface
{

    private $serviceProxy;

    /**
     * @param array $synchronizableIds
     * @return array
     * @throws \common_exception_NotFound
     * @throws \core_kernel_persistence_Exception
     */
    public function getData($synchronizableIds = [])
    {
        foreach ($synchronizableIds as $deliveryExecutionId) {
            /** @var DeliveryExecution $deliveryExecution */
            $deliveryExecution = $this->getServiceProxy()->getDeliveryExecution($deliveryExecutionId);
            $variables = $this->getDeliveryExecutionVariables($deliveryExecution->getDelivery()->getUri(), $deliveryExecutionId);
            $results[] = [
                'deliveryId'          => $deliveryExecution->getDelivery()->getUri(),
                'deliveryExecutionId' => $deliveryExecutionId,
                'details'             => $this->getDeliveryExecutionDetails($deliveryExecutionId),
                'variables'           => $variables,
            ];
        }
        return $results ?? [];
    }

    /**
     * Get details of a delivery execution
     *
     * @param $deliveryExecutionId
     * @return array
     */
    protected function getDeliveryExecutionDetails($deliveryExecutionId)
    {
        /** @var DeliveryExecution $deliveryExecution */
        $deliveryExecution = $this->getServiceProxy()->getDeliveryExecution($deliveryExecutionId);
        try {
            return [
                'identifier' => $deliveryExecution->getIdentifier(),
                'label'      => $deliveryExecution->getLabel(),
                'test-taker' => $deliveryExecution->getUserIdentifier(),
                'starttime'  => $deliveryExecution->getStartTime(),
                'finishtime' => $deliveryExecution->getFinishTime(),
                'state'      => $deliveryExecution->getState()->getUri(),
            ];
        } catch (\common_exception_NotFound $e) {
            return [];
        }
    }

    /**
     * Get variables of a delivery execution
     *
     * @param $deliveryId
     * @param $deliveryExecutionId
     * @return array
     * @throws \core_kernel_persistence_Exception
     */
    protected function getDeliveryExecutionVariables($deliveryId, $deliveryExecutionId)
    {
        $variables = $this->getResultStorage($deliveryId)->getDeliveryVariables($deliveryExecutionId);
        $deliveryExecutionVariables = [];
        foreach ($variables as $variable) {
            $variable = (array)$variable[0];
            list($testIdentifier, $itemIdentifier) = (new DetectTestAndItemIdentifiersHelper())
                ->detect($deliveryId, $variable['test'] ?? null, $variable['item'] ?? null);
            $deliveryExecutionVariables[] = [
                'type'       => $variable['class'],
                'callIdTest' => $variable['callIdTest'] ?? null,
                'callIdItem' => $variable['callIdItem'] ?? null,
                'test'       => $testIdentifier,
                'item'       => $itemIdentifier,
                'data'       => $variable['variable'],
            ];
        }
        return $deliveryExecutionVariables;
    }

    /**
     * @param $deliveryId
     * @return mixed
     */
    protected function getResultStorage($deliveryId)
    {
        return $this->getServiceLocator()->get(ResultServerService::SERVICE_ID)->getResultStorage($deliveryId);
    }

    /**
     * @return ServiceProxy
     */
    protected function getServiceProxy()
    {
        if (!$this->serviceProxy) {
            $this->serviceProxy = $this->getServiceLocator()->get(ServiceProxy::SERVICE_ID);
        }
        return $this->serviceProxy;
    }
}
