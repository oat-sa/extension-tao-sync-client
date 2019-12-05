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
 * @author Yuri Filippovich
 */

namespace oat\taoSyncClient\model\dataProvider\dataFormatter;

use oat\taoSync\export\dataProvider\dataFormatter\AbstractDataFormatter;
use oat\taoSync\model\Result\SyncResultDataFormatter;

class Results extends AbstractDataFormatter
{
    /**
     * @var SyncResultDataFormatter
     */
    private $syncResultDataFormatter;

    /**
     * @inheritDoc
     */
    public function format($deliveryExecution)
    {
        return $this->getDeliveryFormatter()->format($deliveryExecution);
    }

    /**
     * @return SyncResultDataFormatter
     */
    private function getDeliveryFormatter()
    {
        if (!$this->syncResultDataFormatter) {
            $this->syncResultDataFormatter = $this->getServiceLocator()->get(SyncResultDataFormatter::SERVICE_ID);
        }
        return $this->syncResultDataFormatter;
    }
}
