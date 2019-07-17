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

/**
 * TODO: rewrite without using taoEncryption
 * Class LtiUserDataProviderService
 * @package oat\taoSyncClient\model\dataProvider\providers
 */
class LtiUserDataProviderService extends ConfigurableService implements SyncClientCustomDataProviderInterface
{

    /**
     * @param array $synchronizableIds
     * @return array
     * @throws \common_exception_Error
     * @throws \common_exception_InvalidArgumentType
     */
    public function getData($synchronizableIds = [])
    {
        /** @var EncryptedLtiLaunchDataStorage $encryptedLtiStorage */
        $encryptedLtiStorage = $this->getServiceLocator()->get(EncryptedLtiLaunchDataStorage::SERVICE_ID);
        foreach ($synchronizableIds as $userId) {
            try {
                $userResource = new \core_kernel_classes_Resource($userId);
                $properties = $userResource->getPropertiesValues([
                    LtiUserService::PROPERTY_USER_LTIKEY,
                    LtiUserService::PROPERTY_USER_LTICONSUMER
                ]);
                $users[] = [
                    EncryptedLtiLaunchDataStorage::COLUMN_USER_ID    => $properties[LtiUserService::PROPERTY_USER_LTIKEY][0],
                    EncryptedLtiLaunchDataStorage::COLUMN_CONSUMER   => $properties[LtiUserService::PROPERTY_USER_LTICONSUMER][0],
                    EncryptedLtiLaunchDataStorage::COLUMN_SERIALIZED => $encryptedLtiStorage->getEncrypted($properties[LtiUserService::PROPERTY_USER_LTIKEY][0]),
                    'client_user_id'                                 => $userId,
                ];
            } catch (\Exception $e) {
                // no log system described for this.
                continue;
            }
        }
        return $users ?? [];
    }

}
