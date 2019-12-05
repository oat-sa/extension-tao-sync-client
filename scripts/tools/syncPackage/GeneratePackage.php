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
 * Parameters Example:
 *  `-l 1` - only 1 queued synchronization
 *
 * Class GeneratePackage
 * @package oat\taoSyncClient\scripts\tools\syncPackage
 */
class GeneratePackage extends ScriptAction
{
    /**
     * All data types have to be synchronized
     */
    const OPTION_ALL_TYPES = 'all-types';

    /**
     * Endless migrate process - it will create as many packages as needed to migrate everything
     */
    const OPTION_MIGRATE_EVERYTHING = 'migrate-everything';

    /**
     * More details about the process
     */
    const OPTION_VERBOSE = 'verbose';

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
            self::OPTION_VERBOSE => [
                'prefix' => 'v',
                'flag' => true,
                'longPrefix' => self::OPTION_VERBOSE,
                'description' => 'Force script to see more details',
            ],
            self::OPTION_MIGRATE_EVERYTHING => [
                'prefix' => 'e',
                'flag' => true,
                'longPrefix' => self::OPTION_MIGRATE_EVERYTHING,
                'description' => 'Migrate as many packages as needed to synchronize everything'
            ],
            self::OPTION_ALL_TYPES => [
                'prefix' => 'a',
                'flag' => true,
                'longPrefix' => self::OPTION_ALL_TYPES,
                'description' => 'Sync ALL data types that was not synchronized',
            ],
            SyncPackageService::PARAM_LTI_USER => [
                'prefix' => 'u',
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
                'prefix' => 's',
                'flag' => true,
                'longPrefix' => SyncPackageService::PARAM_TEST_SESSION,
                'description' => 'Sync test sessions',
            ],
            SyncPackageService::PARAM_LIMIT => [
                'prefix'       => 'l',
                'flag'         => false,
                'cast'         => 'integer',
                'longPrefix'   => SyncPackageService::PARAM_LIMIT,
                'description'  => 'Limit of the data for one package (it means that only `limit` rows will be taken from the sync queue for the package)',
                'defaultValue' => 5000
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
     * @throws common_exception_Error
     */
    protected function run()
    {
        $this->report = common_report_Report::createInfo('Script execution started');
        /** @var SyncPackageService */
        $packageService = $this->getSyncPackageService();
        do {
            $count = $packageService->create(
                $this->getRequiredDataTypes(),
                $this->getOption(SyncPackageService::PARAM_LIMIT)
            );
            $this->report->add($packageService->getReport());
        } while ($count && $this->hasOption(self::OPTION_MIGRATE_EVERYTHING));
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
        return $this->hasOption(self::OPTION_VERBOSE);
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

        if(!$this->hasOption(self::OPTION_ALL_TYPES)) {
            foreach ($dataTypes as $key => $dataType) {
                if (!$this->hasOption($dataType)) {
                    unset($dataTypes[$key]);
                }
            }
        }

        return $dataTypes;
    }
}
