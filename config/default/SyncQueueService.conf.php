<?php

use oat\taoSyncClient\model\syncQueue\storage\SyncQueueStorageRds;
use oat\taoSyncClient\model\syncQueue\SyncQueueInterface;
use oat\taoSyncClient\model\syncQueue\SyncQueueService;

return new SyncQueueService([
    SyncQueueInterface::OPTION_SYNC_QUEUE_STORAGE => new SyncQueueStorageRds([SyncQueueStorageRds::OPTION_PERSISTENCE => 'default']),
]);
