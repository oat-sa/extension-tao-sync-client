<?php

use oat\taoSyncClient\model\dataProvider\SyncClientDataProviderService;
use \oat\taoSyncClient\model\dataProvider\providers\DeliveryLogDataProviderService;
use \oat\taoSyncClient\model\dataProvider\providers\LtiUserDataProviderService;
use \oat\taoSyncClient\model\dataProvider\providers\ResultsDataProviderService;
use \oat\taoSyncClient\model\dataProvider\providers\TestSessionDataProviderService;
use oat\taoSyncClient\model\syncQueue\SyncQueueInterface;

return new SyncClientDataProviderService([
    SyncClientDataProviderService::OPTION_PROVIDERS => [
        SyncQueueInterface::PARAM_EVENT_TYPE_DELIVERY_LOG => new DeliveryLogDataProviderService(),
        SyncQueueInterface::PARAM_EVENT_TYPE_LTI_USER     => new LtiUserDataProviderService(),
        SyncQueueInterface::PARAM_EVENT_TYPE_RESULTS      => new ResultsDataProviderService(),
        SyncQueueInterface::PARAM_EVENT_TYPE_TEST_SESSION => new TestSessionDataProviderService(),
    ]
]);
