<?php

use oat\taoSyncClient\model\syncPackage\migration\RdsMigrationService;
use oat\taoSyncClient\model\syncPackage\storage\SyncPackageFileSystemStorageService;
use oat\taoSyncClient\model\syncPackage\SyncPackageService;

return new SyncPackageService([
    SyncPackageService::OPTION_MIGRATION => new RdsMigrationService([RdsMigrationService::OPTION_PERSISTENCE => 'default']),
    SyncPackageService::OPTION_STORAGE   => new SyncPackageFileSystemStorageService(),
]);
