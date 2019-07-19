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
 * @author Oleksandr Zagovorychev <zagovorichev@gmail.com>
 */

namespace oat\taoSyncClient\test\model;


use core_kernel_classes_Resource;
use oat\generis\test\TestCase;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoProctoring\model\execution\DeliveryExecution;
use oat\taoResultServer\models\classes\ResultManagement;
use oat\taoResultServer\models\classes\ResultServerService;
use oat\taoSyncClient\model\dataProvider\providers\ResultsDataProviderService;

class ResultsDataProviderTest extends TestCase
{
    /**
     * @throws \common_exception_NotFound
     * @throws \core_kernel_persistence_Exception
     */
    public function testGetData()
    {
        $resource = $this->getMock(core_kernel_classes_Resource::class, [], [], '', false);
        $resource->method('getUri')->willReturn('deliveryExecutionUri');
        $deliveryExecution = $this->getMock(DeliveryExecution::class);
        $deliveryExecution->method('getDelivery')->willReturn($resource);
        $deliveryExecution->method('getIdentifier')->willReturn('identifier');
        $deliveryExecution->method('getLabel')->willReturn('label');
        $deliveryExecution->method('getUserIdentifier')->willReturn('user_id');
        $deliveryExecution->method('getStartTime')->willReturn('start_time');
        $deliveryExecution->method('getFinishTime')->willReturn('finish_time');
        $deliveryExecution->method('getState')->willReturn($resource);

        $deliveryExecutionProxyService = $this->getMock(ServiceProxy::class, [], [], '', false);
        $deliveryExecutionProxyService->method('getDeliveryExecution')->willReturn($deliveryExecution);
        $resultServerService = $this->getMock(ResultServerService::class);
        $resultManagement = $this->getMock(ResultManagement::class);
        $resultManagement->method('getDeliveryVariables')->willReturn([
            [
                [
                    'test' => null,
                    'item' => null,
                    'class' => 'class',
                    'callIdTest' => 'callIdTest',
                    'callIdItem' => 'callIdItem',
                    'variable' => 'variable',
                ]
            ]
        ]);
        $resultServerService->method('getResultStorage')->willReturn($resultManagement);
        $serviceLocator = $this->getServiceLocatorMock([
            ServiceProxy::SERVICE_ID => $deliveryExecutionProxyService,
            ResultServerService::SERVICE_ID => $resultServerService,
        ]);
        $service = new ResultsDataProviderService([]);
        $service->setServiceLocator($serviceLocator);
        self::assertSame([
            [
                'deliveryId' => 'deliveryExecutionUri',
                'deliveryExecutionId' => 1,
                'details' => [
                    'identifier' => 'identifier',
                    'label' => 'label',
                    'test-taker' => 'user_id',
                    'starttime' => 'start_time',
                    'finishtime' => 'finish_time',
                    'state' => 'deliveryExecutionUri',
                ],
                'variables' => [
                    [
                        'type' => 'class',
                        'callIdTest' => 'callIdTest',
                        'callIdItem' => 'callIdItem',
                        'test' => null,
                        'item' => null,
                        'data' => 'variable',
                    ],
                ]
            ]
        ], $service->getData([1]));
    }
}
