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
use oat\taoDeliveryRdf\helper\DetectTestAndItemIdentifiersHelper;
use oat\taoResultServer\models\classes\ResultManagement;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoSyncClient\model\dataProvider\SyncPackageDataProviderInterface;
use taoResultServer_models_classes_ReadableResultStorage;
use taoResultServer_models_classes_WritableResultStorage;

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
        foreach ($deliveryExecutionIds as $deliveryExecutionId) {
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
        return $results;
    }

    /**
     * Get details of a delivery execution
     * we don't prevent exceptions, because if something can't be synchronized then we have
     * data inconsistency and this is an error
     *
     * @param $deliveryExecutionId
     * @return array
     * @throws common_exception_NotFound
     */
    private function getDeliveryExecutionDetails($deliveryExecutionId)
    {
        /** @var DeliveryExecution $deliveryExecution */
        $deliveryExecution = $this->getServiceProxy()->getDeliveryExecution($deliveryExecutionId);
        return [
            'identifier' => $deliveryExecution->getIdentifier(),
            'label'      => $deliveryExecution->getLabel(),
            'test-taker' => $deliveryExecution->getUserIdentifier(),
            'starttime'  => $deliveryExecution->getStartTime(),
            'finishtime' => $deliveryExecution->getFinishTime(),
            'state'      => $deliveryExecution->getState()->getUri(),
        ];
    }

    /**
     * Get variables of a delivery execution
     *
     * @param $deliveryId
     * @param $deliveryExecutionId
     * @return array
     * @throws core_kernel_persistence_Exception
     */
    private function getDeliveryExecutionVariables($deliveryId, $deliveryExecutionId)
    {
        $variables = $this->getResultStorage($deliveryId)->getDeliveryVariables($deliveryExecutionId);
        $deliveryExecutionVariables = [];
        foreach ($variables as $variable) {
            $variable = (array) current($variable);
            $test = $this->getVariable('test', $variable);
            $item = $this->getVariable('item', $variable);
            list($testIdentifier, $itemIdentifier) = (new DetectTestAndItemIdentifiersHelper())
                ->detect($deliveryId, $test, $item);
            $deliveryExecutionVariables[] = [
                'type'       => $this->getVariable('class', $variable),
                'callIdTest' => $this->getVariable('callIdTest', $variable),
                'callIdItem' => $this->getVariable('callIdItem', $variable),
                'test'       => $testIdentifier,
                'item'       => $itemIdentifier,
                'data'       => $this->getVariable('variable', $variable),
            ];
        }
        return $deliveryExecutionVariables;
    }

    /**
     * Getting a variable from the array with variables
     * @param string $name
     * @param array $variables
     * @return mixed|null
     */
    private function getVariable($name = '', array $variables = [])
    {
        return array_key_exists($name, $variables) ? $variables[$name] : null;
    }

    /**
     * @param $deliveryId
     * @return taoResultServer_models_classes_ReadableResultStorage|taoResultServer_models_classes_WritableResultStorage|ResultManagement
     */
    private function getResultStorage($deliveryId)
    {
        return $this->getServiceLocator()->get(ResultServerService::SERVICE_ID)->getResultStorage($deliveryId);
    }

    /**
     * @return array|ServiceProxy
     */
    private function getServiceProxy()
    {
        return $this->getServiceLocator()->get(ServiceProxy::SERVICE_ID);
    }
}
