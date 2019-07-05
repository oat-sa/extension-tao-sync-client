<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2019  (original work) Open Assessment Technologies SA;
 *
 * @author Oleksandr Zagovorychev <zagovorichev@1pt.com>
 */

namespace oat\taoSyncClient\model\syncQueue\listener;


use oat\taoProctoring\model\deliveryLog\event\DeliveryLogEvent;
use oat\taoSyncClient\model\syncQueue\exception\SyncClientSyncQueueException;
use oat\taoSyncClient\model\syncQueue\storage\SyncQueueStorageInterface;
use oat\taoSyncClient\model\syncQueue\SyncQueueService;

class DeliveryLogListener extends AbstractSyncQueueListener
{

    /**
     * On record created
     * @param DeliveryLogEvent $event
     * @throws SyncClientSyncQueueException
     */
    public static function create(DeliveryLogEvent $event)
    {
        self::getSyncQueueService()->addTask([
            SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_ID => $event->getId(),
            SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_TYPE => SyncQueueService::TYPE_SYNCHRONIZABLE_TYPE_DELIVERY_LOG,
            SyncQueueStorageInterface::PARAM_EVENT_TYPE => SyncQueueService::PARAM_EVENT_TYPE_DELIVERY_LOG,
            SyncQueueStorageInterface::PARAM_CREATED_AT => date('Y-m-d H:i:s'),
            SyncQueueStorageInterface::PARAM_UPDATED_AT => date('Y-m-d H:i:s'),
        ]);
    }
}
