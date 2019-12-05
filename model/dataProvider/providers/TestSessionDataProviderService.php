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
 */

namespace oat\taoSyncClient\model\dataProvider\providers;

use oat\taoSync\model\dataProvider\AbstractDataProvider;
use oat\taoSyncClient\model\syncPackage\SyncPackageService;
use oat\taoSyncClient\model\syncQueue\SyncQueueInterface;

class TestSessionDataProviderService extends AbstractDataProvider
{
    /**
     * @inheritDoc
     */
    public function getType()
    {
        return SyncPackageService::PARAM_TEST_SESSION;
    }

    public function getResources(array $deliveryExecutionIds = [])
    {
        return $this->getValidDeliveryExecutions($deliveryExecutionIds);
    }

    /**
     * Preconditions to have possibility to sync test sessions
     * @param array $deliveryExecutionIds
     * @return array
     */
    private function getValidDeliveryExecutions($deliveryExecutionIds = [])
    {
        $deliveryExecutionIds = array_unique($deliveryExecutionIds);
        foreach ($deliveryExecutionIds as $key => $deliveryExecutionId) {
            // all delivery logs need to be synchronized before test session sending
            if (!$this->getSyncQueueService()->isDeliveryLogSynchronized($deliveryExecutionId)) {
                unset($deliveryExecutionIds[$key]);
            }
        }

        return $deliveryExecutionIds;
    }

    /**
     * @return array|object|SyncQueueInterface
     */
    private function getSyncQueueService()
    {
        return $this->getServiceLocator()->get(SyncQueueInterface::SERVICE_ID);
    }
}
