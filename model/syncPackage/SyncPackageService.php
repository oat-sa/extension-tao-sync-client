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


use common_exception_Error;
use common_report_Report;
use oat\oatbox\service\ConfigurableService;
use oat\taoLti\models\classes\user\LtiUserService;
use oat\taoSyncClient\model\syncQueue\SyncQueueInterface;

class SyncPackageService extends ConfigurableService implements SyncPackageInterface
{
    const OPTION_STORAGE = 'storage';
    const OPTION_DATA_PROVIDER = 'dataProvider';
    const OPTION_MIGRATION = 'migration';
    const OPTION_MIGRATION_PARAMS = 'migration_params';

    const PARAM_LTI_USER = 'lti-user';
    const PARAM_DELIVERY_LOG = 'delivery-log';
    const PARAM_RESULTS = 'results';
    const PARAM_TEST_SESSION = 'test-session';

    /**
     * @return mixed|storage\SyncPackageStorageInterface
     */
    public function getStorageService()
    {
        return $this->getOption(self::OPTION_STORAGE);
    }

    /**
     * @return SyncQueueInterface
     */
    public function getSyncQueueService()
    {
        return $this->getOption(self::OPTION_SYNC_QUEUE);
    }

    public function getDataProviderService()
    {
        return $this->getOption(self::OPTION_DATA_PROVIDER);
    }

    public function getMigrationService()
    {
        return $this->getOption(self::OPTION_MIGRATION);
    }

    /**
     * @param $dataTypes
     * @return common_report_Report
     * @throws common_exception_Error
     */
    public function create($dataTypes)
    {
        $data = [];
        $report = common_report_Report::createInfo('Package creation started');
        if ($this->getStorageService()->isValid()) {

            $queuedData = $this->getSyncQueueService()->getTasks($dataTypes, $this->getOption('limit'));
            $this->getData($task);
            /*foreach ($queuedData as $task) {
                $data[] = $this->fillData($task);
            }*/

            $this->getStorageService()->save($data);
        }

        return $report;
    }

    private function getData()
    {
        $this->getDataProviderService()
            ->setServiceLocator($this->getServiceLocator())
            ->getData($task);

    }

    /**
     * Collect the data by the task
     * @param $task
     */
    private function fillData($task)
    {
        switch ($task[SyncQueueInterface::]) {
            case self::PARAM_LTI_USER:
                $this->getLtiUserDataProvider
                $data[self::PARAM_LTI_USER][] = $this->getLtiUser();
                break;
            case self::PARAM_DELIVERY_LOG:
                $data[self::PARAM_DELIVERY_LOG][] = $this->getDeliveryLog();
                break;
            case self::PARAM_RESULTS:
                $data[self::PARAM_RESULTS][] = $this->getResults();
                break;
            case self::PARAM_TEST_SESSION:
                $data[self::PARAM_TEST_SESSION][] = $this->getTestSession();
                break;
            default:
                $report->add(common_report_Report::createFailure('Data type ' . $requiredDataType . ' not found'));
        }
    }

    /**
     * Lti User data
     * @return array
     */
    private function getLtiUser()
    {
        /** @var LtiUserService $ltiUserService */
        $ltiUserService = $this->getServiceLocator()->get(LtiUserService::SERVICE_ID);

        return $data;
    }



}
