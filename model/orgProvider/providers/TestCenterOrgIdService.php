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
 * @author Oleksandr Zagovorychev <zagovorichev@gmail.com>
 */

namespace oat\taoSyncClient\model\orgProvider\providers;


use common_exception_NotFound;
use core_kernel_classes_Container;
use core_kernel_classes_Resource;
use core_kernel_persistence_Exception;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ConfigurableService;
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoSyncClient\model\orgProvider\OrgIdProviderInterface;
use oat\taoTestCenter\model\EligibilityService;

class TestCenterOrgIdService extends ConfigurableService implements OrgIdProviderInterface
{
    use OntologyAwareTrait;

    /**
     * @param string $deliveryExecutionId
     * @return core_kernel_classes_Container
     * @throws core_kernel_persistence_Exception
     * @throws common_exception_NotFound
     */
    public function getOrgIdByDeliveryExecution($deliveryExecutionId = '')
    {
        /** @var ServiceProxy $deliveryExecutionService */
        $deliveryExecutionService = $this->getServiceLocator()->get(ServiceProxy::SERVICE_ID);
        /** @var DeliveryExecution $deliveryExecution */
        $deliveryExecution = $deliveryExecutionService->getDeliveryExecution($deliveryExecutionId);
        /** @var string $testTakerUri */
        $testTakerUri = $deliveryExecution->getUserIdentifier();
        /** @var EligibilityService $eligibilityService */
        $eligibilityService = $this->getServiceLocator()->get(EligibilityService::SERVICE_ID);
        $eligibility = $eligibilityService->getEligibilityByTestTaker($testTakerUri);
        $orgId = '';
        if (count($eligibility)) {
            /** @var core_kernel_classes_Resource $testCenter */
            $testCenter = $eligibilityService->getTestCenterByEligibility(current($eligibility));
            $orgId = $testCenter->getOnePropertyValue($this->getProperty(OrgIdProviderInterface::ORGANISATION_ID_PROPERTY));
        }
        return $orgId;
    }
}
