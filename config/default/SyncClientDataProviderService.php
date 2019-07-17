<?php

use oat\taoSyncClient\model\dataProvider\SyncClientDataProviderService;
use oat\taoSyncClient\model\syncPackage\SyncPackageService;
use \oat\taoSyncClient\model\dataProvider\providers\DeliveryLogDataProviderService;
use \oat\taoSyncClient\model\dataProvider\providers\LtiUserDataProviderService;
use \oat\taoSyncClient\model\dataProvider\providers\ResultDataProviderService;
use \oat\taoSyncClient\model\dataProvider\providers\TestSessionDataProviderService;

return new SyncClientDataProviderService([
    SyncClientDataProviderService::OPTION_PROVIDERS => [
        SyncPackageService::PARAM_DELIVERY_LOG => DeliveryLogDataProviderService::class,
        SyncPackageService::PARAM_LTI_USER     => LtiUserDataProviderService::class,
        SyncPackageService::PARAM_RESULTS      => ResultDataProviderService::class,
        SyncPackageService::PARAM_TEST_SESSION => TestSessionDataProviderService::class,
    ]
]);
