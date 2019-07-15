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
namespace oat\taoSyncClient\test\tools\syncPackage;

use common_report_Report;
use oat\generis\test\TestCase;
use oat\oatbox\filesystem\FileSystemService;
use oat\taoSyncClient\scripts\tools\syncPackage\GeneratePackage;

class GeneratePackageTest extends TestCase
{

    /**
     * @var GeneratePackage
     */
    private $generatePackageTool;

    protected function setUp()
    {
        parent::setUp();

        // $this->taskLogMock = $this->createMock(TaskLogInterface::class);
        $serviceLocatorMock = $this->getServiceLocatorMock([
            FileSystemService::SERVICE_ID => $this->createMock(FileSystemService::class),
        ]);
        $this->generatePackageTool = new GeneratePackage();
        $this->generatePackageTool->setServiceLocator($serviceLocatorMock);
    }

    public function testHelp()
    {
        $output = $this->generatePackageTool->__invoke(['--help']);

        $this->assertInstanceOf(common_report_Report::class, $output);
        $this->assertSame('Creating new file with prepared data which have to be sent to the server.', $this->getReportMessage($output));
    }

    public function testLtiUserData()
    {
        $output = $this->generatePackageTool->__invoke(['--lti-user']);
        $this->assertInstanceOf(common_report_Report::class, $output);
        $this->assertSame('Creating new file with prepared data which have to be sent to the server.',
            $this->getReportMessage($output));
    }

    private function getReportMessage(common_report_Report $output)
    {
        $msg = $output->getMessage();
        $iterator = $output->getIterator();
        while($iterator->valid()) {
            $report = $iterator->current();
            if (!is_a($report, common_report_Report::class)) {
                continue;
            }
            $msg .= "\n".$report->getMessage();
            $iterator->next();
        }
        return $msg;
    }
}
