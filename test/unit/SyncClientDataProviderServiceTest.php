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
use oat\taoSyncClient\model\dataProvider\SyncPackageDataProviderInterface;
use oat\taoSyncClient\model\dataProvider\SyncClientDataProviderService;
use oat\taoSyncClient\model\exception\SyncClientException;
use oat\taoSyncClient\model\syncQueue\storage\SyncQueueStorageInterface;
use ReflectionException;
use stdClass;
use Zend\ServiceManager\ServiceLocatorInterface;

class Provider implements SyncPackageDataProviderInterface
{
    public function getData($data = [])
    {
        return ['data'];
    }

    public function getServiceLocator()
    {
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator=null)
    {
    }
}

class SyncClientDataProviderServiceTest extends TestCase
{
    /**
     * @throws ReflectionException
     * @throws SyncClientException
     */
    public function testGetData()
    {
        $providerService = new SyncClientDataProviderService([
            SyncClientDataProviderService::OPTION_PROVIDERS => [
                'typeName' => new Provider(),
            ]
        ]);

        $data = $providerService->getData([
            [
                SyncQueueStorageInterface::PARAM_EVENT_TYPE => 'typeName',
                SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_ID => 1
            ]
        ]);

        self::assertSame(['typeName' => ['data']], $data);
    }

    /**
     * @throws ReflectionException
     * @throws SyncClientException
     * @expectedException \oat\taoSyncClient\model\exception\SyncClientException
     * @expectedExceptionMessage Incorrect task format #0
     */
    public function testGetDataExceptionTaskFormat()
    {
        $providerService = new SyncClientDataProviderService();
        $providerService->getData([
            [SyncQueueStorageInterface::PARAM_EVENT_TYPE => 'typeName']
        ]);
    }

    /**
     * @throws ReflectionException
     * @throws SyncClientException
     * @expectedExceptionMessage Data providers not configured
     * @expectedException \oat\taoSyncClient\model\exception\SyncClientException
     */
    public function testGetDataExceptionNoProvider()
    {
        $providerService = new SyncClientDataProviderService();
        $providerService->getData([
            [
                SyncQueueStorageInterface::PARAM_EVENT_TYPE => 'typeName',
                SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_ID => 123,
            ]
        ]);
    }

    /**
     * @throws ReflectionException
     * @throws SyncClientException
     * @expectedExceptionMessage Data provider typeName is not defined
     * @expectedException \oat\taoSyncClient\model\exception\SyncClientException
     */
    public function testGetDataExceptionNoSuitableProvider()
    {
        $providerService = new SyncClientDataProviderService([
            SyncClientDataProviderService::OPTION_PROVIDERS => [
                'typeName2' => Provider::class,
            ]
        ]);
        $providerService->getData([
            [
                SyncQueueStorageInterface::PARAM_EVENT_TYPE => 'typeName',
                SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_ID => 123,
            ]
        ]);
    }

    /**
     * @throws ReflectionException
     * @throws SyncClientException
     * @expectedExceptionMessage Type typeName has to implement interface oat\taoSyncClient\model\dataProvider\SyncPackageDataProviderInterface
     * @expectedException \oat\taoSyncClient\model\exception\SyncClientException
     */
    public function testGetDataExceptionIncorrectProviderClass()
    {
        $providerService = new SyncClientDataProviderService([
            SyncClientDataProviderService::OPTION_PROVIDERS => [
                'typeName' => 'class::provider',
            ]
        ]);
        $providerService->getData([
            [
                SyncQueueStorageInterface::PARAM_EVENT_TYPE => 'typeName',
                SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_ID => 123,
            ]
        ]);
    }

    /**
     * @throws ReflectionException
     * @throws SyncClientException
     * @expectedExceptionMessage Type typeName has to implement interface oat\taoSyncClient\model\dataProvider\SyncPackageDataProviderInterface
     * @expectedException \oat\taoSyncClient\model\exception\SyncClientException
     */
    public function testGetDataExceptionIncorrectProviderInterface()
    {
        $providerService = new SyncClientDataProviderService([
            SyncClientDataProviderService::OPTION_PROVIDERS => [
                'typeName' => stdClass::class,
            ]
        ]);
        $providerService->getData([
            [
                SyncQueueStorageInterface::PARAM_EVENT_TYPE => 'typeName',
                SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_ID => 123,
            ]
        ]);
    }
}