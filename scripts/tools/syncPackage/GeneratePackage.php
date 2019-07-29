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

namespace oat\taoSyncClient\scripts\tools\syncPackage;


use common_exception_Error;
use common_report_Report;
use oat\oatbox\extension\script\ScriptAction;
use oat\taoSyncClient\model\syncPackage\SyncPackageService;

/**
 * php index.php 'oat\taoSyncClient\scripts\tools\syncPackage\GeneratePackage'
 *
 * Class GeneratePackage
 * @package oat\taoSyncClient\scripts\tools\syncPackage
 */
class GeneratePackage extends ScriptAction
{
    const OPTION_ALL = 'all';

    /**
     * @var common_report_Report
     */
    private $report;

    /**
     * @return string
     */
    protected function provideDescription()
    {
        return 'Creating new file with prepared data which have to be sent to the server.';
    }

    /**
     * @return array
     */
    protected function provideOptions()
    {
        return [
            'verbose' => [
                'prefix' => 'v',
                'flag' => true,
                'longPrefix' => 'verbose',
                'description' => 'Force script to be more details',
            ],
            self::OPTION_ALL => [
                'prefix' => 'a',
                'flag' => true,
                'longPrefix' => self::OPTION_ALL,
                'description' => 'Sync ALL data that was not synchronized',
            ],
            SyncPackageService::PARAM_LTI_USER => [
                'prefix' => 'l',
                'flag' => true,
                'longPrefix' => SyncPackageService::PARAM_LTI_USER,
                'description' => 'Sync lti user data',
            ],
            SyncPackageService::PARAM_DELIVERY_LOG => [
                'prefix' => 'd',
                'flag' => true,
                'longPrefix' => SyncPackageService::PARAM_DELIVERY_LOG,
                'description' => 'Sync delivery log data',
            ],
            SyncPackageService::PARAM_RESULTS => [
                'prefix' => 'r',
                'flag' => true,
                'longPrefix' => SyncPackageService::PARAM_RESULTS,
                'description' => 'Sync results',
            ],
            SyncPackageService::PARAM_TEST_SESSION => [
                'prefix' => 'r',
                'flag' => true,
                'longPrefix' => SyncPackageService::PARAM_TEST_SESSION,
                'description' => 'Sync test sessions',
            ],
        ];
    }

    /**
     * @return array
     */
    protected function provideUsage()
    {
        return [
            'prefix' => 'h',
            'longPrefix' => 'help',
            'description' => 'Prints a help statement'
        ];
    }

    /**
     * @return common_report_Report
     * @throws \oat\taoSyncClient\model\exception\SyncClientException
     * @throws common_exception_Error
     */
    protected function run()
    {
        $this->report = common_report_Report::createInfo('Script execution started');
        $report = $this->getSyncPackageService()->create($this->getRequiredDataTypes());
        $this->report->add($report);
        $this->report->add(common_report_Report::createSuccess('Done'));
        return $this->report;
    }

    /**
     * @return array|object|SyncPackageService
     */
    private function getSyncPackageService()
    {
        return $this->getServiceLocator()->get(SyncPackageService::SERVICE_ID);
    }

    /**
     * @return bool
     */
    protected function showTime()
    {
        return $this->hasOption('verbose');
    }

    /**
     * @return array
     */
    private function getRequiredDataTypes()
    {
        $dataTypes = [
            SyncPackageService::PARAM_RESULTS,
            SyncPackageService::PARAM_LTI_USER,
            SyncPackageService::PARAM_TEST_SESSION,
            SyncPackageService::PARAM_DELIVERY_LOG,
        ];

        if(!$this->hasOption(self::OPTION_ALL)) {
            foreach ($dataTypes as $key => $dataType) {
                if (!$this->hasOption($dataType)) {
                    unset($dataTypes[$key]);
                }
            }
        }

        return $dataTypes;
    }
}
