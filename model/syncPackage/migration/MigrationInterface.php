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

namespace oat\taoSyncClient\model\syncPackage\migration;


use Zend\ServiceManager\ServiceLocatorAwareInterface;

interface MigrationInterface extends ServiceLocatorAwareInterface
{
    const PARAM_ID = 'id';
    // unique package name
    const PARAM_PACKAGE_NAME = 'package_name';
    // synchronization session id
    const PARAM_SYNC_ID = 'sync_id';
    const PARAM_CREATED_AT = 'created_at';
    const PARAM_UPDATED_AT = 'updated_at';

    /**
     * Getting first migration that was not synchronized
     * @return array
     */
    public function getNextMigration();

    /**
     * add new migration
     * @param $name string
     * @return bool
     */
    public function add($name);

    /**
     * mark migration as synchronized
     * @param $id
     * @param $syncId
     * @return mixed
     */
    public function sync($id, $syncId);

    /**
     * Creating storage if needed
     * @return void
     */
    public function createStorage();

    /**
     * Remove storage with data
     * @return void
     */
    public function dropStorage();
}
