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
use oat\taoEncryption\Service\Lti\LaunchData\EncryptedLtiLaunchDataStorage;
use oat\taoLti\models\classes\user\LtiUserService;
use oat\taoSyncClient\model\dataProvider\SyncClientCustomDataProviderInterface;
use oat\taoSyncClient\model\syncQueue\storage\SyncQueueStorageInterface;

class LtiUserDataProviderService extends ConfigurableService implements SyncClientCustomDataProviderInterface
{

    /**
     * @param array $data
     * @return array
     * @throws \common_exception_Error
     * @throws \common_exception_InvalidArgumentType
     */
    public function getData($data = [])
    {
        /** @var EncryptedLtiLaunchDataStorage $encryptedLtiStorage */
        $encryptedLtiStorage = $this->getServiceLocator()->get(EncryptedLtiLaunchDataStorage::SERVICE_ID);
        foreach ($data as $task) {
            try{
                $userResource = new \core_kernel_classes_Resource($task[SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_ID]);
                $properties = $userResource->getPropertiesValues([
                    LtiUserService::PROPERTY_USER_LTIKEY,
                    LtiUserService::PROPERTY_USER_LTICONSUMER
                ]);
                $users[] = [
                    EncryptedLtiLaunchDataStorage::COLUMN_USER_ID    => $properties[LtiUserService::PROPERTY_USER_LTIKEY][0],
                    EncryptedLtiLaunchDataStorage::COLUMN_CONSUMER   => $properties[LtiUserService::PROPERTY_USER_LTICONSUMER][0],
                    EncryptedLtiLaunchDataStorage::COLUMN_SERIALIZED => $encryptedLtiStorage->getEncrypted($properties[LtiUserService::PROPERTY_USER_LTIKEY][0]),
                    'client_user_id'                                 => $task[SyncQueueStorageInterface::PARAM_SYNCHRONIZABLE_ID],
                ];
            }catch (\Exception $e){
                // no log system described for this.
                continue;
            }

        }
        return $users ?? [];
    }

}
