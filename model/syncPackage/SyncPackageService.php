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

namespace oat\taoSyncClient\model\syncPackage;


use oat\oatbox\filesystem\FileSystem;
use oat\oatbox\service\ConfigurableService;

class SyncPackageService extends ConfigurableService implements SyncPackageInterface
{
    const OPTION_PATH = 'path';

    public function getPath()
    {
        return $this->getOption(self::OPTION_PATH);
    }

    public function setPath($path = '')
    {
        $oldPath = $this->getPath();
        $this->setOption(self::OPTION_PATH, $path);
        if (!$this->checkPath()) {
            // reset if new path can't be used
            $this->setOption(self::OPTION_PATH, $oldPath);
        }
    }

    public function checkPath()
    {
        $path = $this->getPath();
        FileSystem::class;
    }
}
