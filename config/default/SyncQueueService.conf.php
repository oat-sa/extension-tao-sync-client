<?php

use oat\taoSyncClient\model\syncQueue\storage\SyncQueueStorageRds;
use oat\taoSyncClient\model\syncQueue\SyncQueueInterface;
use oat\taoSyncClient\model\syncQueue\SyncQueueService;

return new SyncQueueService([
    SyncQueueInterface::OPTION_SYNC_QUEUE_STORAGE => SyncQueueStorageRds::class,
    SyncQueueInterface::OPTION_SYNC_QUEUE_STORAGE_PARAMS => ['default'], // persistence - default
]);
