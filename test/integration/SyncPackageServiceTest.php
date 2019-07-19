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

namespace oat\taoSyncClient\test\integration;

use common_exception_Error;
use oat\generis\test\TestCase;
use oat\taoSyncClient\model\dataProvider\SyncClientDataProviderInterface;
use oat\taoSyncClient\model\dataProvider\SyncClientDataProviderServiceInterface;
use oat\taoSyncClient\model\syncPackage\migration\MigrationInterface;
use oat\taoSyncClient\model\syncPackage\storage\SyncPackageStorageInterface;
use oat\taoSyncClient\model\syncPackage\SyncPackageService;
use oat\taoSyncClient\model\syncQueue\storage\SyncQueueStorageInterface;
use oat\taoSyncClient\model\syncQueue\SyncQueueInterface;

class SyncPackageServiceTest extends TestCase
{
    /**
     * @throws common_exception_Error
     */
    public function testCreate()
    {
        $syncQueueService = $this->createMock(SyncQueueInterface::class);
        $syncQueueService->method('getTasks')->willReturn([
            [SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_TYPE => 1],
            [SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_TYPE => 1],
            [SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_TYPE => 2],
            [SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_TYPE => 3],
            [SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_TYPE => 3],
            [SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_TYPE => 3],
            [SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_TYPE => 3],
            [SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_TYPE => 4],
        ]);
        $syncQueueService->method('markAsMigrated')->willReturn(150);
        $syncClientDataProviderService = $this->createMock(SyncClientDataProviderServiceInterface::class);

        $serviceLocatorMock = $this->getServiceLocatorMock([
            SyncQueueInterface::SERVICE_ID => $syncQueueService,
            SyncClientDataProviderServiceInterface::SERVICE_ID => $syncClientDataProviderService,
        ]);

        $syncPackageStorageService = $this->createMock(SyncPackageStorageInterface::class);
        $syncPackageStorageService->method('isValid')->willReturn(true);
        $syncPackageStorageService->method('save')->willReturn(101);
        $syncPackageStorageService->method('setServiceLocator')->willReturn($syncPackageStorageService);

        $syncDataProviderService = $this->createMock(SyncClientDataProviderInterface::class);
        $syncDataProviderService->method('setServiceLocator')->willReturn($syncDataProviderService);

        $syncPackageService = new SyncPackageService([
            SyncPackageService::OPTION_STORAGE => $syncPackageStorageService,
            SyncPackageService::OPTION_MIGRATION => $this->createMock(MigrationInterface::class),
        ]);
        $syncPackageService->setServiceLocator($serviceLocatorMock);

        $report = $syncPackageService->create();
        $json = json_encode($report->JsonSerialize());
        self::assertSame('{"type":"info","message":"Package creation started","data":null,"children":[]}', $json);
    }
}
