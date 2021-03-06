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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 *
 *
 */

use \oat\taoSyncClient\scripts\install\RegisterDownloadService;
use oat\taoSyncClient\scripts\install\RegisterSyncPackageService;
use oat\taoSyncClient\scripts\install\RegisterSyncQueueRds;
use oat\taoSyncClient\scripts\install\SetupSyncQueueEventsListener;
use oat\taoSyncClient\scripts\update\Updater;

return array(
    'name'           => 'taoSyncClient',
    'label'          => 'Synchronization Client',
    'description'    => 'Synchronization logic specific only for the client server',
    'license'        => 'GPL-2.0',
    'author'         => 'Open Assessment Technologies SA',
    'managementRole' => 'http://www.tao.lu/Ontologies/generis.rdf#taoSyncClientManager',
    'acl'            => array(
        array('grant', 'http://www.tao.lu/Ontologies/generis.rdf#taoSyncClientManager', array('ext' => 'taoSyncClient')),
    ),
    'install'        => array(
        'php' => [
            RegisterDownloadService::class,
            RegisterSyncQueueRds::class,
            RegisterSyncPackageService::class,
            SetupSyncQueueEventsListener::class,
        ]
    ),
    'uninstall'      => array(),
    'update'         => Updater::class,
    'routes'         => array(
        '/taoSyncClient' => 'oat\\taoSyncClient\\controller'
    ),
    'constants'      => array(
        # views directory
        'DIR_VIEWS' => __DIR__ . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR,

        #BASE URL (usually the domain root)
        'BASE_URL'  => ROOT_URL . 'taoSyncClient/',
    ),
    'extra'          => array(
        'structures' => __DIR__ . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'structures.xml',
    )
);