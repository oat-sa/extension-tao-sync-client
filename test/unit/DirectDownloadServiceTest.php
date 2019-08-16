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
 */

namespace oat\taoSyncClient\test\model;


use oat\generis\test\TestCase;
use oat\taoPublishing\model\publishing\PublishingService;
use oat\taoSyncClient\model\downloadService\DirectDownloadService;

class DirectDownloadServiceTest extends TestCase
{

    private $service;
    public function setup(){
        $publishingTest = $this->getMock(PublishingService::class);
        $publishingTest->method('callEnvironment')->willReturn(true);
        $serviceLocator = $this->getServiceLocatorMock([
            PublishingService::SERVICE_ID => $publishingTest,
        ]);
        $this->service = new DirectDownloadService([]);
        $this->service->setServiceLocator($serviceLocator);
    }
    public function testSuccessDownload()
    {
        $params = ['url' => 'test', 'method' => 'test', 'filePath' => 'test'];
        self::assertSame($params, $this->service->download($params));
    }

    public function testMissedParamsDownload()
    {
        $params = ['url' => 'test'];
        $this->expectException(\common_exception_MissingParameter::class);
        $this->service->download($params);
    }
}
