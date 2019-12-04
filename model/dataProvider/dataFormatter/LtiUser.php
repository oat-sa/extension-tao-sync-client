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

namespace oat\taoSyncClient\model\dataProvider\dataFormatter;

use oat\generis\model\OntologyAwareTrait;
use oat\taoLti\models\classes\user\LtiUserService;
use oat\taoSync\export\dataProvider\dataFormatter\AbstractDataFormatter;

class LtiUser extends AbstractDataFormatter
{
    use OntologyAwareTrait;

    /**
     * @inheritDoc
     */
    public function format($resource)
    {
        $properties = $resource->getPropertiesValues([
            $this->getProperty(LtiUserService::PROPERTY_USER_LTICONSUMER),
            $this->getProperty(LtiUserService::PROPERTY_USER_LTIKEY),
        ]);
        $user['client_user_id'] = $resource->getUri();
        $user['consumer'] = array_key_exists(LtiUserService::PROPERTY_USER_LTICONSUMER, $properties)
            ? current($properties[LtiUserService::PROPERTY_USER_LTICONSUMER])->getUri()
            : '';
        $user['user_id'] = array_key_exists(LtiUserService::PROPERTY_USER_LTIKEY, $properties)
            ? (string) current($properties[LtiUserService::PROPERTY_USER_LTIKEY])
            : '';
        return $user;
    }
}
