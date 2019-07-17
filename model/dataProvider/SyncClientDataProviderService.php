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

class SyncClientDataProviderService extends ConfigurableService implements SyncClientDataProviderInterface
{
    const OPTION_PROVIDERS = 'providers';


    /**
     * array of available providers from config.
     * @var array
     */
    private $availableProviders;
    /**
     * array of created providers
     * @var SyncClientDataProviderInterface[]
     */
    private $providers;


    /**
     * @param array $tasks
     * @return array
     * @throws SyncClientException
     */
    public function getData($tasks = [])
    {
        $groupedTasks = [];
        $data = [];
        foreach ($tasks as $task) {
            $groupedTasks[$task['event_type']][] = $task[SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_ID];
        }
        foreach ($groupedTasks as $type => $items) {
            $data[$type] = $this->getProvider($type)->getData($items);
        }
        return $data;
    }

    /**
     * @param string $type
     * @return SyncClientDataProviderInterface
     * @throws SyncClientException
     */
    private function getProvider($type)
    {
        if (empty($this->availableProviders)) {
            $this->availableProviders = $this->getOption(self::OPTION_PROVIDERS);
        }
        if (!array_key_exists($type, $this->providers)) {
            if (!array_key_exists($type, $this->availableProviders)
                || !$this->availableProviders[$type] instanceof SyncClientDataProviderInterface) {
                throw new SyncClientException('Incorrect data provider');
            }
            $this->providers[$type] = new $this->availableProviders[$type];
            $this->providers[$type]->setServiceLocator($this->getServiceLocator());
        }
        return $this->providers[$type];
    }

}
