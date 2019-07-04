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

namespace oat\taoSyncClient\scripts\install;


use common_report_Report;
use oat\oatbox\extension\InstallAction;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionCreated;
use oat\taoDelivery\models\classes\execution\event\DeliveryExecutionState;
use oat\taoLti\models\classes\user\events\LtiUserCreatedEvent;
use oat\taoLti\models\classes\user\events\LtiUserUpdatedEvent;
use oat\taoProctoring\model\deliveryLog\event\DeliveryLogEvent;
use oat\taoSyncClient\model\syncQueue\listener\DeliveryLogListener;
use oat\taoSyncClient\model\syncQueue\listener\LtiUserListener;
use oat\taoSyncClient\model\syncQueue\listener\ResultsListener;
use oat\taoSyncClient\model\syncQueue\listener\TestSessionListener;

class SetupSyncQueueEventsListener extends InstallAction
{
    public function __invoke($params)
    {
        // lti user
        $this->registerEvent(LtiUserCreatedEvent::class, [LtiUserListener::class, 'create']);
        $this->registerEvent(LtiUserUpdatedEvent::class, [LtiUserListener::class, 'update']);

        // delivery log
        $this->registerEvent(DeliveryLogEvent::class, [DeliveryLogListener::class, 'create']);

        // results
        $this->registerEvent(DeliveryExecutionState::class, [ResultsListener::class, 'deliveryExecutionStateChanged']);

        // test session
        $this->registerEvent(DeliveryExecutionState::class, [TestSessionListener::class, 'deliveryExecutionStateChanged']);
        $this->registerEvent(DeliveryExecutionCreated::class, [TestSessionListener::class, 'deliveryExecutionStateChanged']);

        return common_report_Report::createSuccess(__('Registered SyncQueue Events listeners'));
    }
}
