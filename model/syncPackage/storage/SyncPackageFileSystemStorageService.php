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


use oat\oatbox\filesystem\Directory;
use oat\oatbox\filesystem\FileSystemService;
use oat\oatbox\service\ConfigurableService;

class SyncPackageFileSystemStorageService extends ConfigurableService implements SyncPackageStorageInterface
{
    const FILESYSTEM_ID = 'taoSyncClient';
    const STORAGE_NAME = 'packages';

    public function isValid()
    {
        return $this->getStorageDir()->exists();
    }

    /**
     * @param string $packageName
     * @return string Path to the File
     */
    public function getPackagePath($packageName = '')
    {
        return $packageName;
    }

    public function createPackage($name = '', $data = [])
    {
        // TODO: Implement create() method.
        $this->addMigration();
        $path = $this->createFile();
    }

    /**
     * @return FileSystemService
     */
    private function getFileSystemService()
    {
        return $this->getServiceLocator()
            ->get(FileSystemService::SERVICE_ID);
    }

    /**
     * @return Directory
     */
    private function getStorageDir()
    {
        return $this->getFileSystemService()
            ->getDirectory(self::FILESYSTEM_ID)
            ->getDirectory(self::STORAGE_NAME);

    }

    public function createStorage()
    {
        $this->getFileSystemService()
            ->createFileSystem(self::FILESYSTEM_ID)
            ->createDir(self::STORAGE_NAME);
    }

    public function getStorageName()
    {
        return static::FILESYSTEM_ID;
    }
}
