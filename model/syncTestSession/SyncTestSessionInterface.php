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
 * @author Oleksandr Zagovorychev <zagovorichev@gmail.com>
 */

namespace oat\taoSyncClient\model\syncResults;


interface SyncTestSessionInterface
{
    const SERVICE_ID = 'taoSyncClient/SyncTestSessionInterface';

    /**
     * Unique array of the delivery executions which test sessions weren't synced
     * @return array
     */
    public function getUniqueNotSyncedDeliveryExecutions();

    /**
     * Mark all records of the `test session` sync action as synced for the provided delivery execution
     * @param string $deliveryExecutionId
     * @return mixed
     */
    public function markSyncedDeliveryExecution($deliveryExecutionId);
}
