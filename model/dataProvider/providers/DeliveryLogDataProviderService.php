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

use oat\oatbox\service\ConfigurableService;
use oat\taoProctoring\model\deliveryLog\DeliveryLog;
use oat\taoSyncClient\model\dataProvider\SyncClientCustomDataProviderInterface;

class DeliveryLogDataProviderService extends ConfigurableService implements SyncClientCustomDataProviderInterface
{
    /**
     * @param array $synchronizableIds
     * @return array
     */
    public function getData($synchronizableIds = [])
    {
        return $this->getDeliveryLog()->search(
                [DeliveryLog::DELIVERY_EXECUTION_ID => $synchronizableIds],
                ['shouldDecodeData' => false]);
    }

    /**
     * @return array|DeliveryLog
     */
    protected function getDeliveryLog()
    {
        return $this->getServiceLocator()->get(DeliveryLog::SERVICE_ID);
    }
}