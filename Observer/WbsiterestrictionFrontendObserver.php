<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\LoginAsCustomer\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * LoginAsCustomer observer
 */
class WbsiterestrictionFrontendObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param \Magento\Framework\App\RequestInterface|null $request
     */
    public function __construct(
        ?\Magento\Framework\App\RequestInterface $request = null
    ) {
        $this->request = $request ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\App\RequestInterface::class
        );
    }

    /**
     * Disable website stub or private sales restriction for loginascustomer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->request->getModuleName() == 'loginascustomer') {
            $result = $observer->getResult();
            $result->setData('should_proceed', false);
        }
    }
}
