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

namespace oat\taoSyncClient\scripts\install;

use common_report_Report;
use oat\oatbox\extension\InstallAction;
use oat\oatbox\service\ServiceNotFoundException;
use oat\taoSyncClient\model\downloadService\DirectDownloadService;
use oat\taoSyncClient\model\downloadService\DownloadServiceInterface;

/**
 * php index.php 'oat\taoSyncClient\scripts\install\RegisterDownloadService'
 *
 * Class RegisterDownloadService
 * @package oat\taoSyncClient\scripts\install
 */
class RegisterDownloadService extends InstallAction
{
    /**
     * @param $params
     * @return common_report_Report
     * @throws \common_Exception
     */
    public function __invoke($params)
    {
        try {
            $this->getServiceManager()->get(DownloadServiceInterface::SERVICE_ID);
        } catch (ServiceNotFoundException $e) {
            $downloadService = new DirectDownloadService();
            $this->getServiceManager()->register(DownloadServiceInterface::SERVICE_ID, $downloadService);
        }
        return new common_report_Report(common_report_Report::TYPE_SUCCESS, __('DownloadService successfully registered'));
    }
}
