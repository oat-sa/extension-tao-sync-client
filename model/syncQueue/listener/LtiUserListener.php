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


use oat\taoLti\models\classes\user\events\LtiUserCreatedEvent;
use oat\taoLti\models\classes\user\events\LtiUserUpdatedEvent;
use oat\taoSyncClient\model\syncQueue\exception\SyncClientSyncQueueException;
use oat\taoSyncClient\model\syncQueue\storage\SyncQueueStorageInterface;
use oat\taoSyncClient\model\syncQueue\SyncQueueInterface;

class LtiUserListener extends AbstractSyncQueueListener
{
    const PARAM_USER_ID = 'userId';
    const PARAM_EVENT = 'event';

    /**
     * @param LtiUserCreatedEvent $createdEvent
     * @throws SyncClientSyncQueueException
     */
    public static function create(LtiUserCreatedEvent $createdEvent)
    {
        self::addTask($createdEvent->getUserId());
    }

    /**
     * @param LtiUserUpdatedEvent $updatedEvent
     * @throws SyncClientSyncQueueException
     */
    public static function update(LtiUserUpdatedEvent $updatedEvent)
    {
        self::addTask($updatedEvent->getUserId());
    }

    /**
     * @param string $userId
     * @throws SyncClientSyncQueueException
     */
    private static function addTask($userId)
    {
        self::getSyncQueueService()->addTask([
            SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_ID => $userId,
            SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_TYPE => SyncQueueInterface::PARAM_SYNCHRONIZABLE_TYPE_LTI_USER,
            SyncQueueStorageInterface::PARAM_EVENT_TYPE => SyncQueueInterface::PARAM_EVENT_TYPE_LTI_USER,
            SyncQueueStorageInterface::PARAM_CREATED_AT => date('Y-m-d H:i:s'),
            SyncQueueStorageInterface::PARAM_UPDATED_AT => date('Y-m-d H:i:s'),
        ]);
    }
}
