<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\LoginAsCustomer\Controller\Adminhtml\Login;

/**
 * LoginAsCustomer login action
 */
class Login extends \Magento\Backend\App\Action
{
    /**
     * Login as customer action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $customerId = (int) $this->getRequest()->getParam('customer_id');

        $login = $this->_objectManager
            ->create(\Magefan\LoginAsCustomer\Model\Login::class)
            ->setCustomerId($customerId);

        $login->deleteNotUsed();

        $customer = $login->getCustomer();

        if (!$customer->getId()) {
            $this->messageManager->addError(__('Customer with this ID are no longer exist.'));
            $this->_redirect('customer/index/index');
            return;
        }

        $user = $this->_objectManager->get(\Magento\Backend\Model\Auth\Session::class)->getUser();
        $login->generate($user->getId());
        $customerStoreId = $this->getCustomerStoreId($customer);

        $storeManager = $this->_objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);

        if ($customerStoreId) {
            $store = $storeManager->getStore($customerStoreId);    
        } else {
            $store = $storeManager->getDefaultStoreView();    
        }
        
        $url = $this->_objectManager->get(\Magento\Framework\Url::class)
            ->setScope($store);

        $redirectUrl = $url->getUrl('loginascustomer/login/index', ['secret' => $login->getSecret(), '_nosid' => true]);

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
}
