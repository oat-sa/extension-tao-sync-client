<?php

use oat\taoSyncClient\model\dataProvider\SyncClientDataProviderService;
use oat\taoSyncClient\model\syncPackage\SyncPackageService;

return new SyncClientDataProviderService([
    SyncClientDataProviderService::OPTION_PROVIDERS => [
        SyncPackageService::PARAM_DELIVERY_LOG => '',
        SyncPackageService::PARAM_LTI_USER     => '',
        SyncPackageService::PARAM_RESULTS      => '',
        SyncPackageService::PARAM_TEST_SESSION => '',
    ]
]);
