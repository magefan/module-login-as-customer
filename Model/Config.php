<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\LoginAsCustomer\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class Config
 */
class Config
{
    /**
     * Extension config path
     */
    const XML_PATH_EXTENSION_ENABLED     = 'mfloginascustomer/general/enabled';
    const XML_PATH_KEY                   = 'mfloginascustomer/general/key';
    const STORE_VIEW_TO_LOGIN_IN         = 'mfloginascustomer/general/store_view_login';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $metadata;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ProductMetadataInterface $metadata
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ProductMetadataInterface $metadata
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->metadata = $metadata;
    }

    /**
     * Retrieve store config value
     * @param string $path
     * @param null $storeId
     * @return mixed
     */
    public function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->getConfig(
            self::XML_PATH_EXTENSION_ENABLED
        );
    }

    /**
     * @return mixed
     */
    public function isKeyMissing()
    {
        return false;
        /*
        $path = explode('/', self::XML_PATH_KEY);
        $path = $path[0];

        $section = \Magento\Framework\App\ObjectManager::getInstance()->create(
            \Magefan\Community\Model\Section::class,
            ['name' => $path]
        );

        return !$this->getConfig(
            self::XML_PATH_KEY
        ) && $section->getModule();
        */
    }

    /**
     * @return mixed
     */
    public function getStoreViewLogin()
    {
        return $this->getConfig(
            self::STORE_VIEW_TO_LOGIN_IN
        );
    }
}
