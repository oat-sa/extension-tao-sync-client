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

namespace oat\taoSyncClient\model\syncQueue;


use oat\taoSyncClient\model\syncQueue\exception\SyncClientSyncQueueException;

/**
 * Controls synchronization from client to server
 * Interface SyncQueueInterface
 * @package oat\taoSyncClient\model\syncQueue
 */
interface SyncQueueInterface
{
    const SERVICE_ID = 'taoSyncClient/SyncQueueService';

    const OPTION_SYNC_QUEUE_STORAGE = 'storage';
    const OPTION_SYNC_QUEUE_STORAGE_PARAMS = 'storage_params';

    const PARAM_EVENT_TYPE_LTI_USER_CREATED = 'lti_user_created';
    const PARAM_EVENT_TYPE_LTI_USER_UPDATED = 'lti_user_updated';
    const PARAM_EVENT_TYPE_RESULTS = 'results';
    const PARAM_EVENT_TYPE_DELIVERY_LOG = 'delivery_log';
    const PARAM_EVENT_TYPE_TEST_SESSION = 'test_session';

    const PARAM_SYNCHRONIZABLE_TYPE_LTI_USER = 'lti_user';
    const PARAM_SYNCHRONIZABLE_TYPE_DELIVERY_LOG = 'delivery_log';
    const PARAM_SYNCHRONIZABLE_TYPE_DELIVERY_EXECUTION = 'delivery_execution';

    /**
     * @param array $params
     * @return mixed
     * @throws SyncClientSyncQueueException
     */
    public function addTask($params = []);

    /**
     * List of tasks
     * @param array $dataTypes - [self::PARAM_SYNCHRONIZABLE_TYPE_]
     * @param int $limit
     * @param bool $synchronized - which data we are looking for (by default that weren't synchronized)
     * @return array
     */
    public function getTasks(array $dataTypes = [], $limit = 0, $synchronized = false);

    /**
     * @param int $migrationId
     * @param array $queuedTasks
     * @return int (count of the updated fields)
     */
    public function markAsMigrated($migrationId = 0, $queuedTasks = []);
}
