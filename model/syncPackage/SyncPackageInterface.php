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
 * @author Oleksandr Zagovorychev <zagovorichev@gmail.com>
 */

namespace oat\taoSyncClient\model\syncPackage;


use oat\taoSyncClient\model\dataProvider\SyncPackageDataProviderInterface;
use oat\taoSyncClient\model\syncPackage\migration\MigrationInterface;
use oat\taoSyncClient\model\syncPackage\storage\SyncPackageStorageInterface;
use oat\taoSyncClient\model\syncQueue\SyncQueueInterface;

interface SyncPackageInterface
{
    const SERVICE_ID = 'taoSyncClient/SyncPackageService';

    /**
     * Getting path to the folder with Generated packages for synchronization
     * @return SyncPackageStorageInterface
     */
    public function getStorageService();

    /**
     * @return SyncQueueInterface
     */
    public function getSyncQueueService();

    /**
     * @return SyncPackageDataProviderInterface
     */
    public function getDataProviderService();

    /**
     * @return MigrationInterface
     */
    public function getMigrationService();
}
