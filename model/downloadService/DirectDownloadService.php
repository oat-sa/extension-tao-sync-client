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

namespace oat\taoSyncClient\model\downloadService;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use oat\oatbox\service\ConfigurableService;
use oat\taoPublishing\model\publishing\PublishingService;
use oat\taoSync\scripts\tool\synchronisation\SynchronizeData;
use oat\taoSyncClient\model\downloadService\tasks\DownloadTask;

class DirectDownloadService extends ConfigurableService implements DownloadServiceInterface
{
    /**
     * Basic curl options for resumable downloading
     */
    const CURL_OPTIONS = [
        CURLOPT_RETURNTRANSFER  => true,  //allows curl to resume downloading
        CURLOPT_LOW_SPEED_LIMIT => 10,    //check for disconnect (speed less then 10 bytes per second)
        CURLOPT_LOW_SPEED_TIME  => 10    //check for disconnect (low speed for more then 10 sec)
    ];


    /**
     * @param array $params
     * @return array
     * @throws \common_exception_MissingParameter
     */
    public function download($params)
    {
        $params = $this->checkParams($params);
        $request = $this->getRequest($params);
        $clientOptions = ['curl' => self::CURL_OPTIONS];

        //providing file to continue writing
        $clientOptions['curl'][CURLOPT_FILE] = fopen($params[DownloadTask::PARAMS_FILEPATH], 'ab+');
        $downloaded = false;
        while (!$downloaded) {
            try {
                //providing point to continue download or 0 to start download
                $clientOptions['curl'][CURLOPT_RANGE] = (file_exists($params[DownloadTask::PARAMS_FILEPATH])
                        ? filesize($params[DownloadTask::PARAMS_FILEPATH]) : 0) . '-';
                $this->call($request, $clientOptions);
                $downloaded = true;
            } catch (RequestException $e) {
                // Super slow speed(<10bytes/sec) | disconnect | error
                // no actions, just trying again until success
            }
        }
        return $params;
    }


    /**
     * @param Request $request
     * @param array $clientOptions
     * @return mixed
     */
    private function call($request, $clientOptions)
    {
        return $this->getServiceLocator()
            ->get(PublishingService::SERVICE_ID)
            ->callEnvironment(SynchronizeData::class, $request, $clientOptions);
    }


    /**
     * @param array $params
     * @return array
     * @throws \common_exception_MissingParameter
     */
    private function checkParams($params)
    {
        if (!isset($params[DownloadTask::PARAMS_FILEPATH], $params[DownloadTask::PARAMS_URL])) {
            throw new \common_exception_MissingParameter('Missing parameter in ' . static::class);
        }
        if (!isset($params['method'])) {
            $params['method'] = 'GET';
        }
        return $params;
    }

    /**
     * @param array $params
     * @return Request
     */
    private function getRequest($params)
    {
        return new Request($params['method'], $params['url']);
    }
}