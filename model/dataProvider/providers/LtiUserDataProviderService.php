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

use core_kernel_classes_Resource;
use oat\generis\model\OntologyAwareTrait;
use oat\taoLti\models\classes\user\LtiUserService;
use oat\taoSync\model\dataProvider\AbstractDataProvider;
use oat\taoSyncClient\model\syncPackage\SyncPackageService;

/**
 * Class LtiUserDataProviderService
 * @package oat\taoSyncClient\model\dataProvider\providers
 */
class LtiUserDataProviderService extends AbstractDataProvider
{
    use OntologyAwareTrait;

    /**
     * @inheritDoc
     */
    public function getType()
    {
        return SyncPackageService::PARAM_LTI_USER;
    }

    /**
     * @inheritDoc
     */
    public function getResources(array $usersId = [])
    {
        $usersId = array_unique($usersId);
        $users = [];

        foreach ($usersId as $userId) {
            $users[] = $this->getResource($userId);
        }

        return $users;
    }
}
