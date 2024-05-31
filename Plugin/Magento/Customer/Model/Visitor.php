<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */
declare(strict_types=1);

namespace Magefan\LoginAsCustomer\Plugin\Magento\Customer\Model;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Event\ManagerInterface;

class Visitor
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var SessionManagerInterface
     */
    protected $session;

    /**
     * Application Event Dispatcher
     *
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * Visitor constructor.
     * @param RequestInterface $request
     * @param SessionManagerInterface $session
     * @param ManagerInterface $_eventManager
     */
    public function __construct(
        RequestInterface $request,
        SessionManagerInterface $session,
        ManagerInterface $_eventManager,

    ) {
        $this->request = $request;
        $this->session = $session;
        $this->_eventManager = $_eventManager;
    }

    /**
     * @param \Magento\Customer\Model\Visitor $subject
     * @param $result
     * @param $observer
     * @return mixed
     */
    public function afterInitByRequest(
        \Magento\Customer\Model\Visitor $subject,
        $result,
        $observer
    ) {
        if ($this->request->getFullActionName() == 'loginascustomer_login_index')  {
            if (!$result->getId()) {
                $result->setSessionId($this->session->getSessionId());
                $result->save();
                $this->_eventManager->dispatch('visitor_init', ['visitor' => $result]);
                $this->session->setVisitorData($result->getData());
            }
        }

        return $result;
    }
}

