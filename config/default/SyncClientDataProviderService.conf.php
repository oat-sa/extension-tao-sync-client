<?php

use oat\taoSyncClient\model\dataProvider\SyncClientDataProviderService;
use \oat\taoSyncClient\model\dataProvider\providers\DeliveryLogDataProviderService;
use \oat\taoSyncClient\model\dataProvider\providers\LtiUserDataProviderService;
use \oat\taoSyncClient\model\dataProvider\providers\ResultsDataProviderService;
use \oat\taoSyncClient\model\dataProvider\providers\TestSessionDataProviderService;
use oat\taoSyncClient\model\syncQueue\SyncQueueInterface;

return new SyncClientDataProviderService([
    SyncClientDataProviderService::OPTION_PROVIDERS => [
        SyncQueueInterface::PARAM_EVENT_TYPE_DELIVERY_LOG => DeliveryLogDataProviderService::class,
        SyncQueueInterface::PARAM_EVENT_TYPE_LTI_USER     => LtiUserDataProviderService::class,
        SyncQueueInterface::PARAM_EVENT_TYPE_RESULTS      => ResultsDataProviderService::class,
        SyncQueueInterface::PARAM_EVENT_TYPE_TEST_SESSION => TestSessionDataProviderService::class,
    ]
]);
