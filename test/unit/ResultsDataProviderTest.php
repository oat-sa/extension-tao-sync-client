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


use oat\generis\test\TestCase;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoSync\model\Result\SyncResultDataFormatter;
use oat\taoSyncClient\model\dataProvider\providers\ResultsDataProviderService;

class ResultsDataProviderTest extends TestCase
{
    /**
     * @throws \common_exception_NotFound
     * @throws \core_kernel_persistence_Exception
     */
    public function testGetData()
    {
        $deliveryExecutionProxyService = $this->createMock(ServiceProxy::class);
        $deliveryExecutionProxyService->method('getDeliveryExecution')->willReturn(null);
        $formatterMock = $this->createMock(SyncResultDataFormatter::class);
        $formatterResultValue = ['formatter' => 'result'];
        $formatterMock->expects($this->once())->method('format')->willReturn($formatterResultValue);
        $serviceLocator = $this->getServiceLocatorMock([
            ServiceProxy::SERVICE_ID => $deliveryExecutionProxyService,
            SyncResultDataFormatter::SERVICE_ID => $formatterMock,
        ]);
        $service = new ResultsDataProviderService([]);
        $service->setServiceLocator($serviceLocator);
        self::assertSame([$formatterResultValue], $service->getData([1]));
    }
}
