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


use common_exception_Error;
use oat\oatbox\service\ConfigurableService;
use oat\taoProctoring\model\deliveryLog\DeliveryLog;
use oat\taoSyncClient\model\exception\SyncClientException;
use oat\taoSyncClient\model\syncPackage\storage\SyncPackageStorageInterface;
use oat\taoSyncClient\model\syncQueue\exception\SyncClientSyncQueueException;
use oat\taoSyncClient\model\syncQueue\storage\SyncQueueStorageInterface;

/**
 * Controls synchronisation from client to server
 * Class SyncQueueService
 * @package oat\taoSyncClient\model\syncQueue
 */
class SyncQueueService extends ConfigurableService implements SyncQueueInterface
{

    /**
     * @var SyncQueueStorageInterface
     */
    private $storageService;

    /**
     * @param array $options
     * @throws SyncClientException
     * @throws common_exception_Error
     */
    public function setOptions(array $options)
    {
        $this->checkOptions($options);
        parent::setOptions($options);
    }

    /**
     * @param $name
     * @param $value
     * @throws SyncClientException
     */
    public function setOption($name, $value)
    {
        $this->checkOptions([$name => $value]);
        parent::setOption($name, $value);
    }

    /**
     * @param array $options
     * @throws SyncClientException
     */
    private function checkOptions(array $options)
    {
        if (array_key_exists(self::OPTION_SYNC_QUEUE_STORAGE, $options)
            && is_subclass_of($options[self::OPTION_SYNC_QUEUE_STORAGE], SyncPackageStorageInterface::class)) {
            throw new SyncClientException(self::OPTION_SYNC_QUEUE_STORAGE . ' parameter has to be instance of SyncPackageStorageInterface');
        }
    }

    /**
     * @param array $params
     * @return mixed|void
     * @throws SyncClientSyncQueueException
     */
    public function addTask($params = [])
    {
        $this->validate($params);
        $this->getStorageService()->insert($params);
    }

    /**
     * @param array $params
     * @throws SyncClientSyncQueueException
     */
    protected function validate(array $params)
    {
        if (!array_key_exists(SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_ID, $params)) {
            throw new SyncClientSyncQueueException('Synchronizable Resource Id is not set');
        }
        if (!array_key_exists(SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_TYPE, $params)) {
            throw new SyncClientSyncQueueException('Synchronizable Type is not set');
        }
        if (!array_key_exists(SyncQueueStorageInterface::PARAM_EVENT_TYPE, $params)) {
            throw new SyncClientSyncQueueException('Event Type is not set');
        }
        if (!array_key_exists(SyncQueueStorageInterface::PARAM_ORG_ID, $params)) {
            throw new SyncClientSyncQueueException('Organization ID is not set');
        }
    }

    /**
     * @return SyncQueueStorageInterface
     * @throws SyncClientSyncQueueException
     */
    protected function getStorageService()
    {
        if (!$this->storageService) {
            if (!$this->hasOption(self::OPTION_SYNC_QUEUE_STORAGE)) {
                throw new SyncClientSyncQueueException('taoSyncClient SyncQueueStorage is not initialized');
            }
            $this->storageService = $this->propagate($this->getOption(self::OPTION_SYNC_QUEUE_STORAGE));
        }
        return $this->storageService;
    }

    /**
     * @param array $dataTypes
     * @param int $limit
     * @param bool $synchronized
     * @return array
     * @throws SyncClientSyncQueueException
     */
    public function getTasks(array $dataTypes = [], $limit = 5000, $synchronized = false)
    {
        return $this->getStorageService()->getAggregatedQueued($dataTypes, $limit);
    }

    /**
     * @param int $migrationId
     * @param array $queuedTasks
     * @return int
     * @throws SyncClientSyncQueueException
     */
    public function markAsMigrated($migrationId = 0, $queuedTasks = [])
    {
        $this->getStorageService()->setMigrationId($migrationId, $queuedTasks);
        return count($queuedTasks); //Not exactly count of updated in db.
    }

    /**
     * @param $deliveryExecutionId
     * @return bool
     * @throws SyncClientSyncQueueException
     */
    public function isDeliveryLogSynchronized($deliveryExecutionId)
    {
        $deliveryLog = $this->getDeliveryLogService()->get($deliveryExecutionId);
        $deliveryLogIds = array_map(static function ($row) {
            return $row[DeliveryLog::ID];
        }, $deliveryLog);
        return $this->getStorageService()->isSynchronized(static::PARAM_EVENT_TYPE_DELIVERY_LOG, $deliveryLogIds);
    }

    /**
     * @return array|object|DeliveryLog
     */
    private function getDeliveryLogService()
    {
        return $this->getServiceLocator()->get(DeliveryLog::SERVICE_ID);
    }
}
