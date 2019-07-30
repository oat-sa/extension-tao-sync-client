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

use common_exception_InvalidArgumentType;
use core_kernel_classes_Resource;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\user\LtiUserService;
use oat\taoSyncClient\model\dataProvider\SyncPackageDataProviderInterface;

/**
 * Class LtiUserDataProviderService
 * @package oat\taoSyncClient\model\dataProvider\providers
 */
class LtiUserDataProviderService extends ConfigurableService implements SyncPackageDataProviderInterface
{
    use OntologyAwareTrait;

    /**
     * @param array $usersId
     * @return array
     * @throws common_exception_InvalidArgumentType
     */
    public function getData($usersId = [])
    {
        $usersId = array_unique($usersId);
        $users = [];
        foreach ($usersId as $userId) {
            $user = [];
            $resource = $this->getResource($userId);
            /** @var core_kernel_classes_Resource $consumerResource */
            $properties = $resource->getPropertiesValues([
                $this->getProperty(LtiUserService::PROPERTY_USER_LTICONSUMER),
                $this->getProperty(LtiUserService::PROPERTY_USER_LTIKEY),
            ]);
            $user['client_user_id'] = $userId;
            $user['consumer'] = array_key_exists(LtiUserService::PROPERTY_USER_LTICONSUMER, $properties)
                ? current($properties[LtiUserService::PROPERTY_USER_LTICONSUMER])->getUri()
                : '';
            $user['user_id'] = array_key_exists(LtiUserService::PROPERTY_USER_LTIKEY, $properties)
                ? (string) current($properties[LtiUserService::PROPERTY_USER_LTIKEY])
                : '';
            $users[] = $user;
        }
        return $users;
    }

    public function getLtiUserService()
    {
        return $this->getServiceLocator()->get(LtiUserService::SERVICE_ID);
    }

}
