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


use oat\oatbox\service\ConfigurableService;
use oat\taoProctoring\model\deliveryLog\DeliveryLog;
use oat\taoSyncClient\model\syncQueue\exception\SyncClientSyncQueueException;
use oat\taoSyncClient\model\syncQueue\storage\SyncQueueStorageInterface;
use ReflectionClass;
use ReflectionException;

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
    }

    /**
     * @return SyncQueueStorageInterface
     * @throws SyncClientSyncQueueException
     * @return SyncQueueStorageInterface
     * @throws SyncClientSyncQueueException
     */
    protected function getStorageService()
    {
        if (!$this->storageService) {
            $storageServiceClass = $this->getOption(self::OPTION_SYNC_QUEUE_STORAGE);
            $hasStorage = true;
            try {
                $reflection = new ReflectionClass($storageServiceClass);
                if ($reflection->implementsInterface(SyncQueueStorageInterface::class)) {
                    /** @var SyncQueueStorageInterface storageService */
                    $this->storageService = new $storageServiceClass($this->getOption(self::OPTION_SYNC_QUEUE_STORAGE_PARAMS));
                    $this->storageService->setServiceLocator($this->getServiceLocator());
                } else {
                    $hasStorage = false;
                }
            } catch (ReflectionException $e) {
                $hasStorage = false;
            }
            if (!$hasStorage) {
                throw new SyncClientSyncQueueException('taoSyncClient SyncQueueStorage is not initialized');
            }
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
    public function getTasks(array $dataTypes = [], $limit = 0, $synchronized = false)
    {
        return $this->getStorageService()->getQueued($dataTypes, $limit);
    }

    public function markAsMigrated($migrationId = 0, $queuedTasks = [])
    {
        $updatedCount = 0;
        // @todo
        return $updatedCount;
    }

    /**
     * @param $deliveryExecutionId
     * @return bool
     * @throws SyncClientSyncQueueException
     */
    public function isDeliveryLogSynchronized($deliveryExecutionId)
    {
        $deliveryLogIds = $this->getDeliveryLogService()->get($deliveryExecutionId);
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
