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

class SyncClientDataProviderService extends ConfigurableService implements SyncClientDataProviderInterface
{
    const OPTION_PROVIDERS = 'providers';

    private $availableProviders;


    /**
     * @param array $tasks
     * @return array
     * @throws SyncClientException
     */
    public function getData($tasks)
    {
        $groupedTasks = [];
        $data = [];
        foreach ($tasks as $task){
            $groupedTasks[$task['event_type']][] = $task;
        }
        $this->availableProviders = $this->getOption(self::OPTION_PROVIDERS);
        foreach ($groupedTasks as $type => $items) {
            $data[$type] = $this->getProvider($type)->getData($items);
        }
        return $data;
    }

    /**
     * @param string $type
     * @return SyncClientCustomDataProviderInterface
     * @throws SyncClientException
     */
    private function getProvider($type)
    {
        if (empty($this->availableProviders[$type])
            || $this->availableProviders[$type] instanceof SyncClientCustomDataProviderInterface) {
            throw new SyncClientException('Incorrect data provider');
        }
        $customDataProvider = new $this->availableProviders[$type];
        $customDataProvider->setServiceLocator($this->getServiceLocator());
        return $customDataProvider;
    }

}
