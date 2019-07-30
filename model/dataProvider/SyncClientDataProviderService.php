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

namespace oat\taoSyncClient\model\dataProvider;


use oat\oatbox\service\ConfigurableService;
use oat\taoSyncClient\model\exception\SyncClientException;
use oat\taoSyncClient\model\syncQueue\storage\SyncQueueStorageInterface;
use ReflectionClass;
use ReflectionException;

class SyncClientDataProviderService extends ConfigurableService implements SyncPackageDataProviderServiceInterface
{
    /**
     * array of created providers
     * @var SyncPackageDataProviderInterface[]
     */
    private $providers = [];

    /**
     * @param array $tasks
     * @return array
     * @throws ReflectionException
     * @throws SyncClientException
     */
    public function getData($tasks = [])
    {
        $data = [];
        foreach ($this->getGroupedTasks($tasks) as $type => $items) {
            $data[$type] = $this->getProvider($type)->getData($items);
        }
        return $data;
    }

    /**
     * Group tasks by their data type
     * @param array $tasks
     * @return array
     * @throws SyncClientException
     */
    private function getGroupedTasks($tasks = [])
    {
        $groupedTasks = [];
        foreach ($tasks as $key => $task) {
            if (!is_array($task)
                || !array_key_exists(SyncQueueStorageInterface::PARAM_EVENT_TYPE, $task)
                || !array_key_exists(SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_ID, $task)
            ) {
                throw new SyncClientException('Incorrect task format #'.$key);
            }
            $groupedTasks[$task[SyncQueueStorageInterface::PARAM_EVENT_TYPE]][] = $task[SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_ID];
        }
        return $groupedTasks;
    }

    /**
     * @param string $type
     * @return SyncPackageDataProviderInterface
     * @throws SyncClientException
     * @throws ReflectionException
     */
    public function getProvider($type = '')
    {
        if (!array_key_exists($type, $this->providers)) {

            if (!$this->hasOption(self::OPTION_PROVIDERS)) {
                throw new SyncClientException('Data providers not configured');
            }

            if (!array_key_exists($type, $this->getOption(self::OPTION_PROVIDERS))) {
                throw new SyncClientException('Data provider ' . $type . ' is not defined');
            }
            /**
             * @var SyncPackageDataProviderInterface
             */
            $provider = $this->getOption(self::OPTION_PROVIDERS)[$type];
            if (!$provider instanceof SyncPackageDataProviderInterface) {
                throw new SyncClientException('Type ' . $type . ' has to implement interface ' . SyncPackageDataProviderInterface::class);
            }
            $this->providers[$type] = $provider;
            $this->providers[$type]->setServiceLocator($this->getServiceLocator());
        }
        return $this->providers[$type];
    }

}
