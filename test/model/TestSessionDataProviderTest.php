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
use oat\taoSyncClient\model\dataProvider\providers\TestSessionDataProviderService;
use oat\taoSyncClient\model\syncQueue\SyncQueueInterface;

class TestSessionDataProviderTest extends TestCase
{
    public function testGetData()
    {
        $syncQueueService = $this->getMock(SyncQueueInterface::class);
        $syncQueueService->method('isDeliveryLogSynchronized')->willReturnCallback(static function($val) {
            return $val !== 2;
        });
        $serviceLocator = $this->getServiceLocatorMock([
            SyncQueueInterface::SERVICE_ID => $syncQueueService,
        ]);
        $service = new TestSessionDataProviderService([]);
        $service->setServiceLocator($serviceLocator);
        self::assertSame([0 => 1, 2 => 3], $service->getData([1,2,3]));
    }
}
