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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoSyncClient\model\downloadService\tasks;


use oat\oatbox\event\EventManager;
use oat\oatbox\extension\AbstractAction;
use oat\taoSyncClient\model\downloadService\DownloadServiceInterface;
use oat\taoSyncClient\model\downloadService\events\DownloadCompletedEvent;

class DownloadTask extends AbstractAction
{

    /**
     * synchronisation Id
     */
    const PARAMS_SYNCID = 'syncId';
    /**
     * absolute path to file to write in.
     */
    const PARAMS_FILEPATH = 'filePath';
    /**
     *  relative url of needed file. absolute url will be formed with environment options
     */
    const PARAMS_URL = 'url';

    /**
     * @param $params
     * @return \common_report_Report
     * @throws \Exception
     */
    public function __invoke($params)
    {
        try {
            $this->checkParams($params);
            /** @var DownloadServiceInterface $downloadService */
            $downloadService = $this->getServiceLocator()->get(DownloadServiceInterface::SERVICE_ID);
            $downloadService->download($params);
            $report = new \common_report_Report(
                \common_report_Report::TYPE_SUCCESS,
                'Download completed',
                ['params' => $params]
            );
            $event = new DownloadCompletedEvent($params[self::PARAMS_SYNCID], $params[self::PARAMS_FILEPATH]);
            $this->getServiceManager()->get(EventManager::SERVICE_ID)->trigger($event);
        } catch (\Exception $exception) {
            $report = new \common_report_Report(
                \common_report_Report::TYPE_ERROR,
                $exception->getMessage(),
                ['params' => $params]
            );
        }
        return $report;
    }

    /**
     * @param $params
     * @throws \common_exception_MissingParameter
     */
    private function checkParams($params)
    {
        if (!isset($params[self::PARAMS_SYNCID], $params[self::PARAMS_FILEPATH],$params[self::PARAMS_URL])) {
            throw new \common_exception_MissingParameter('Missing parameter in ' . static::class);
        }
    }


}