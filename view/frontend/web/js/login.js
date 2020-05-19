/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'Magento_Customer/js/section-config'
], function ($, customerData, sectionConfig) {

    'use strict';

    return function (config) {
        $('body').trigger('processStart');
        customerData.reload(sectionConfig.getSectionNames ? sectionConfig.getSectionNames() : 'customer').done(function () {
            window.location.href = config.redirectUrl;
        });
    };
});
