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

namespace oat\taoSyncClient\model\syncPackage\storage;


use ArrayIterator;
use oat\oatbox\filesystem\Directory;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ConfigurableService;

class SyncPackageFileSystemStorageService extends ConfigurableService implements SyncPackageStorageInterface
{
    const STORAGE_NAME = 'packages';

    public function get($packageName = '')
    {
        // TODO: Implement get() method.
    }

    public function create()
    {
        // TODO: Implement create() method.
    }

    /**
     * List of the available packages
     * @return array
     */
    public function getList()
    {
        $packages = [];
        /** @var ArrayIterator $iterator */
        $iterator = $this->getStorageDir()->getFlyIterator(Directory::ITERATOR_FILE | Directory::ITERATOR_RECURSIVE);
        while ($iterator->valid()) {
            $packages[] = $iterator->current();
            $iterator->next();
        }

        return $packages;
    }

    /**
     * @return FileSystemService
     */
    public function getFileSystemService()
    {
        return $this->getServiceLocator()
            ->get(FileSystemService::SERVICE_ID);
    }

    /**
     * @return Directory
     */
    public function getStorageDir()
    {
        return $this->getFileSystemService()
            ->getDirectory('taoSyncClient')
            ->getDirectory(self::STORAGE_NAME);

    }
}
