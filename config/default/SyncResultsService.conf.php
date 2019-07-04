<?php

use oat\taoDelivery\model\execution\DeliveryExecutionInterface;
use oat\taoProctoring\model\execution\DeliveryExecution;
use oat\taoSyncClient\model\syncResults\SyncResultsInterface;
use oat\taoSyncClient\model\syncResults\SyncResultsService;

return new SyncResultsService([
    SyncResultsInterface::OPTION_STATUS_EXECUTIONS_TO_SYNC => [
        DeliveryExecutionInterface::STATE_FINISHED,
        DeliveryExecutionInterface::STATE_TERMINATED,
        DeliveryExecution::STATE_CANCELED,
    ]
]);
