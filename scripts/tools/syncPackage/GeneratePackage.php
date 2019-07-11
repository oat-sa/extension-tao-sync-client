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

class GeneratePackage extends ScriptAction
{
    const OPTION_LTI_USER = 'lti-user';
    const OPTION_ALL = 'all';
    const OPTION_DELIVERY_LOG = 'delivery-log';
    const OPTION_RESULTS = 'results';
    const OPTION_TEST_SESSION = 'test-session';

    /**
     * @var common_report_Report
     */
    private $report;

    protected function provideDescription()
    {
        return 'Creating new file with prepared data which have to be sent to the server.';
    }

    protected function provideOptions()
    {
        return [
            'wet-run' => [
                'prefix' => 'w',
                'flag' => true,
                'longPrefix' => 'wet-run',
                'description' => 'Create new package with data.',
            ],
            'verbose' => [
                'prefix' => 'v',
                'flag' => true,
                'longPrefix' => 'verbose',
                'description' => 'Force script to be more details',
            ],
        ];
    }

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
        $data = [];
        if ($this->checkPath()) {
            foreach ($this->getRequiredDataTypes() as $requiredDataType) {
                switch ($requiredDataType) {
                    case self::OPTION_LTI_USER:
                        $data[self::OPTION_LTI_USER] = $this->getLtiUser();
                        break;
                    case self::OPTION_DELIVERY_LOG:
                        $data[self::OPTION_DELIVERY_LOG] = $this->getDeliveryLog();
                        break;
                    case self::OPTION_RESULTS:
                        $data[self::OPTION_RESULTS] = $this->getResults();
                        break;
                    case self::OPTION_TEST_SESSION:
                        $data[self::OPTION_TEST_SESSION] = $this->getTestSession();
                        break;
                    default:
                        $this->report->add(common_report_Report::createFailure('Data type ' . $requiredDataType . ' not found'));
                }
            }
            $this->save($data);
        }

        $this->report->add(common_report_Report::createSuccess('Done'));
        return $this->report;
    }

    private function checkPath()
    {
        $this->report->add(common_report_Report::createFailure('not implemented'));
        return false;
    }

    protected function showTime()
    {
        return $this->hasOption('verbose');
    }

    private function getRequiredDataTypes()
    {
        $dataTypes = [
            self::OPTION_RESULTS,
            self::OPTION_LTI_USER,
            self::OPTION_TEST_SESSION,
            self::OPTION_DELIVERY_LOG,
        ];

        if(!$this->hasOption(self::OPTION_ALL)) {
            foreach ($dataTypes as $key => $dataType) {
                if (!$this->hasOption($dataType)) {
                    unset($dataType[$key]);
                }
            }
        }

        return $dataTypes;
    }
}
