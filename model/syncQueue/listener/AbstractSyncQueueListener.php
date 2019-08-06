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

namespace oat\taoSyncClient\model\syncQueue\listener;


use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\ServiceManager;
use oat\taoSyncClient\model\orgProvider\OrgIdProviderInterface;
use oat\taoSyncClient\model\syncQueue\SyncQueueInterface;

class AbstractSyncQueueListener
{
    /**
     * @return ConfigurableService|SyncQueueInterface
     */
    protected static function getSyncQueueService()
    {
        return self::getServiceManager()->get(SyncQueueInterface::SERVICE_ID);
    }

    /**
     * @return ServiceManager
     */
    protected static function getServiceManager()
    {
        return ServiceManager::getServiceManager();
    }

    /**
     * @param string[] $deliveryExecutionIds
     * @return string[]
     */
    protected static function getOrgIdsByDeliveryExecutions(array $deliveryExecutionIds = [])
    {
        if (is_array($deliveryExecutionIds) && count($deliveryExecutionIds)) {
            $ids = [];
            foreach ($deliveryExecutionIds as $deliveryExecutionId) {
                $ids[] = static::getServiceManager()
                    ->get(OrgIdProviderInterface::SERVICE_ID)
                    ->getOrgIdByDeliveryExecution($deliveryExecutionId);
            }
            $orgIds = array_unique($ids);
        }

        // default is empty string to store
        if (!isset($orgIds) || !count($orgIds)) {
            $orgIds = [''];
        }

        return $orgIds;
    }
}