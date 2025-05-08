<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\LoginAsCustomer\Plugin\Magento\Catalog\Model\Product\Compare;

class Item
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Item constructor.
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
    }

    /**
     * @param \Magento\Catalog\Model\Product\Compare\Item $subject
     * @param \Closure $proceed
     * @return \Magento\Catalog\Model\Product\Compare\Item|mixed
     */
    public function aroundBindCustomerLogin(
        \Magento\Catalog\Model\Product\Compare\Item $subject,
        \Closure $proceed
    ) {
        if ($this->_customerSession->getLoggedAsCustomerPreLogin()) {
            return $subject;
        }

        return $proceed();
    }
}
