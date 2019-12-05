<?php

use oat\taoSyncClient\model\syncPackage\migration\RdsMigrationService;
use oat\taoSyncClient\model\syncPackage\SyncPackageService;
use oat\taoSync\package\storage\SyncFileSystem;

return new SyncPackageService([
    SyncPackageService::OPTION_MIGRATION => new RdsMigrationService([RdsMigrationService::OPTION_PERSISTENCE => 'default']),
    SyncPackageService::OPTION_STORAGE   => new SyncFileSystem(),
]);
