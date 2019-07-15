<?php

use oat\taoSyncClient\model\dataProvider\SyncClientDataProviderInterface;
use oat\taoSyncClient\model\syncPackage\migration\RdsMigrationService;
use oat\taoSyncClient\model\syncPackage\storage\SyncPackageFileSystemStorageService;
use oat\taoSyncClient\model\syncPackage\SyncPackageService;

return new SyncPackageService([
    SyncPackageService::OPTION_MIGRATION => RdsMigrationService::class,
    SyncPackageService::OPTION_MIGRATION_PARAMS => ['default'],
    SyncPackageService::OPTION_DATA_PROVIDER => SyncClientDataProviderInterface::class,
    SyncPackageService::OPTION_STORAGE => SyncPackageFileSystemStorageService::class,
]);
