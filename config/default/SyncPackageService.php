<?php

use oat\taoSyncClient\model\syncPackage\SyncPackageService;

return new SyncPackageService([
    SyncPackageService::OPTION_SERVICE_PROVIDER => SyncDataProvider::class,
    SyncPackageService::OPTION_STORAGE => SyncStorage::class
]);
