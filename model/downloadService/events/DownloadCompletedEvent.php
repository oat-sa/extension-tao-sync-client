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
 */

namespace oat\taoSyncClient\model\downloadService\events;


use oat\oatbox\event\Event;

class DownloadCompletedEvent implements Event
{
    const EVENT_NAME = __CLASS__;
    private $syncId;
    private $filePath;

    public function __construct($syncId = 0, $filePath = '')
    {
        $this->syncId = $syncId;
        $this->filePath = $filePath;
    }

    /**
     * @return int
     */
    public function getSyncId()
    {
        return $this->syncId;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::EVENT_NAME;
    }
}
