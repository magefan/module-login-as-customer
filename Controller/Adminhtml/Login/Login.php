<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\LoginAsCustomer\Controller\Adminhtml\Login;

/**
 * Class Login
 * @package Magefan\LoginAsCustomer\Controller\Adminhtml\Login
 */
class Login extends \Magento\Backend\App\Action
{
    /**
     * @var \Magefan\LoginAsCustomer\Model\Login
     */
    protected $loginModel;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession  = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager  = null;

    /**
     * @var \Magento\Framework\Url
     */
    protected $url = null;

    /**
     * @var \Magefan\LoginAsCustomer\Model\Config
     */
    protected $config = null;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository = null;

    /**
     * Login constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magefan\LoginAsCustomer\Model\Login|null $loginModel
     * @param \Magento\Backend\Model\Auth\Session|null $authSession
     * @param \Magento\Store\Model\StoreManagerInterface|null $storeManager
     * @param \Magento\Framework\Url|null $url
     * @param \Magefan\LoginAsCustomer\Model\Config|null $config
     * @param \Magento\Customer\Api\CustomerRepositoryInterface|null $customerRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magefan\LoginAsCustomer\Model\Login $loginModel = null,
        \Magento\Backend\Model\Auth\Session $authSession = null,
        \Magento\Store\Model\StoreManagerInterface $storeManager = null,
        \Magento\Framework\Url $url = null,
        \Magefan\LoginAsCustomer\Model\Config $config = null,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository = null
    ) {
        parent::__construct($context);
        $this->loginModel = $loginModel ?: $this->_objectManager->get(\Magefan\LoginAsCustomer\Model\Login::class);
        $this->authSession = $authSession ?: $this->_objectManager->get(\Magento\Backend\Model\Auth\Session::class);
        $this->storeManager = $storeManager ?: $this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $this->url = $url ?: $this->_objectManager->get(\Magento\Framework\Url::class);
        $this->config = $config ?: $this->_objectManager->get(\Magefan\LoginAsCustomer\Model\Config::class);
        $this->customerRepository = $customerRepository ?: $this->_objectManager->get(\Magento\Customer\Api\CustomerRepositoryInterface::class);
    }
    /**
     * Login as customer action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $request = $this->getRequest();
        $customerId = (int) $request->getParam('customer_id');
        if (!$customerId) {
            $customerId = (int) $request->getParam('entity_id');
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->config->isEnabled()) {
            $msg = __(strrev('.remotsuC sA nigoL > snoisnetxE nafegaM > noitarugifnoC > serotS ot etagivan esaelp noisnetxe eht elbane ot ,delbasid si remotsuC sA nigoL nafegaM'));
            $this->messageManager->addErrorMessage($msg);
            return $resultRedirect->setPath('customer/index/index');
        } elseif ($this->config->isKeyMissing()) {
            $msg = __(strrev('.remotsuC sA nigoL > snoisnetxE nafegaM > noitarugifnoC > serotS ni yek tcudorp eht yficeps esaelP .noos delbasid yllacitamotua eb lliw noisnetxE remotsuC sA nigoL .gnissim si yeK tcudorP remotsuC sA nigoL nafegaM'));
            $this->messageManager->addErrorMessage($msg);
            return $resultRedirect->setPath('customer/index/index');
        }

        $customerStoreId = $request->getParam('store_id');

        if (!isset($customerStoreId) && $this->config->getStoreViewLogin()) {
            $this->messageManager->addNoticeMessage(__('Please select a Store View to login in.'));
            return $resultRedirect->setPath('loginascustomer/login/manual', ['entity_id' => $customerId ]);
        }

        $login = $this->loginModel->setCustomerId($customerId);

        $login->deleteNotUsed();

        $customer = $login->getCustomer();

        if (!$customer->getId()) {
            $this->messageManager->addErrorMessage(__('Customer with this ID are no longer exist.'));
            return $resultRedirect->setPath('customer/index/index');
        }

        /* Check if customer's company is active */
        $tmpCustomer = $this->customerRepository->getById($customer->getId());
        if ($tmpCustomer->getExtensionAttributes() !== null) {
            $companyAttributes = null;
            if (method_exists($tmpCustomer->getExtensionAttributes(), 'getCompanyAttributes')) {
                $companyAttributes = $tmpCustomer->getExtensionAttributes()->getCompanyAttributes();
            }

            if ($companyAttributes !== null) {
                $companyId = $companyAttributes->getCompanyId();
                if ($companyId) {
                    try {
                        $company = $this->getCompanyRepository()->get($companyId);
                        if ($company->getStatus() != 1) {
                            $this->messageManager->addErrorMessage(__('You cannot login as customer. Customer\'s company is not active.'));
                            return $resultRedirect->setPath('customer/index/index');
                        }
                    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {}
                }
            }
        }
        /* End check */

        $user = $this->authSession->getUser();
        $login->generate($user->getId());

        if (!$customerStoreId) {
            $customerStoreId = $this->getCustomerStoreId($customer);
        }

        if ($customerStoreId) {
            $store = $this->storeManager->getStore($customerStoreId);
        } else {
            $store = $this->storeManager->getDefaultStoreView();
        }

        $redirectUrl = $this->url->setScope($store)
            ->getUrl('loginascustomer/login/index', ['secret' => $login->getSecret(), '_nosid' => true]);

        $this->getResponse()->setRedirect($redirectUrl);
    }

    /**
     * We're not using the $customer->getStoreId() method due to a bug where it returns the store for the customer's website
     * @param $customer
     * @return string
     */
    public function getCustomerStoreId(\Magento\Customer\Model\Customer $customer)
    {
        return $customer->getData('store_id');
    }

    /**
     * Check is allowed access
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magefan_LoginAsCustomer::login_button');
    }

    /**
     * Retrieve Company Repository
     * @return \Magento\Company\Api\CompanyRepositoryInterface
     */
    protected function getCompanyRepository()
    {
        return $this->_objectManager->get(\Magento\Company\Api\CompanyRepositoryInterface::class);
    }
}
