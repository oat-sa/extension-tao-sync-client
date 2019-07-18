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

namespace oat\taoSyncClient\model\syncQueue\storage;


interface SyncQueueStorageInterface
{
    const PARAM_ID = 'id';
    const PARAM_SYNCHRONIZABLE_ID = 'synchronizable_id';
    const PARAM_SYNCHRONIZABLE_TYPE = 'synchronizable_type';
    const PARAM_EVENT_TYPE = 'event_type';
    // id from the migrationService
    const PARAM_SYNC_MIGRATION_ID = 'sync_migration_id';
    const PARAM_CREATED_AT = 'created_at';
    const PARAM_UPDATED_AT = 'updated_at';

    /**
     * Get all the data synchronized and not
     * @param int $limit
     * @param int $offset
     * @return mixed
     */
    public function getAll($limit = 1000, $offset = 0);

    /**
     * Adding new record to the queue
     * @param array $action [all self::Params]
     * @return mixed
     */
    public function insert(array $action);


    /**
     * * Mark the record as synchronized
     * @param int $migrationId
     * @param array $taskIds
     * @return bool
     */
    public function setMigrationId($migrationId, $taskIds = []);

    /**
     * @param array $dataTypes
     * @param int $limit
     * @return array
     */
    public function getAggregatedQueued($dataTypes, $limit);
}
