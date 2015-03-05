<?php

/*******************************************************************************
 *  Copyright 2013 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *
 *  You may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at:
 *  http://aws.amazon.com/apache2.0
 *  This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
 *  CONDITIONS OF ANY KIND, either express or implied. See the License
 *  for the
 *  specific language governing permissions and limitations under the
 *  License.
 * *****************************************************************************
 */


require_once 'OffAmazonPaymentsService/OffAmazonPaymentsService.config.inc.php';
require_once 'OffAmazonPaymentsService/RegionSpecificProperties.php';
require_once 'OffAmazonPaymentsService/MerchantValues.php';

define('MERCHANT_ID', isset($merchantId) ? $merchantId : null);
define('ACCESS_KEY', isset($accessKey) ? $accessKey : null);
define('SECRET_KEY', isset($secretKey) ? $secretKey : null);
define('APPLICATION_NAME', isset($applicationName) ? $applicationName : null);
define('APPLICATION_VERSION', isset($applicationVersion) ? $applicationVersion : null);
define('REGION', isset($region) ? $region : null);
define('ENVIRONMENT', isset($environment) ? $environment : null);
define('SERVICE_URL', isset($serviceUrl) ? $serviceUrl : null);
define('WIDGET_URL', isset($widgetUrl) ? $widgetUrl : null);
define('CA_BUNDLEFILE', isset($caBundleFile) ? $caBundleFile : null);
define('CLIENT_ID', isset($clientId) ? $clientId : null);
define('PROXY_USERNAME', isset($proxyUsername) ? $proxyUsername : null);
define('PROXY_PASSWORD', isset($proxyPassword) ? $proxyPassword : null);
define('PROXY_HOST', isset($proxyHost) ? $proxyHost : null);
define('PROXY_PORT', isset($proxyPort) ? $proxyPort : null);
define('CN_NAME', isset($cnName) ? $cnName : null);

class OffAmazonPaymentsService_MerchantValuesBuilder
{
    private $_config;

    private $_regionSpecificProperties;

    /**
     * Provide a static function to access the constructor so
     * that a fluent interface can be used to build the merchant
     * values object
     *
     * @param config to use, default to null
     *
     * @return new instance of OffAmazonPaymentsService_MerchantValuesBuilder
     */
    public static function create($config = null) {
        return new OffAmazonPaymentsService_MerchantValuesBuilder($config);
    }

    /**
     * Create a new instance, using global configuraton
     * values if no configuration is define
     *
     * @param config array of property values
     *
     */
    private function __construct($config = null) {
        
        if (isset($config)) {
            $this->_config = $config;
        } else {
            $this->_config = array(
                'merchantId' => MERCHANT_ID,
                'accessKey' => ACCESS_KEY,
                'secretKey' => SECRET_KEY,
                'applicationName' => APPLICATION_NAME,
                'applicationVersion' => APPLICATION_VERSION,
                'region' => REGION,
                'environment' => ENVIRONMENT,
                'serviceUrl' => SERVICE_URL,
                'widgetUrl' => WIDGET_URL,
                'caBundleFile' => CA_BUNDLEFILE,
                'clientId' => CLIENT_ID,
                'proxyUsername' => PROXY_USERNAME,
                'proxyPassword' => PROXY_PASSWORD,
                'proxyHost' => PROXY_HOST,
                'proxyPort' => PROXY_PORT,
                'cnName' => CN_NAME
            );
        }

        $this->_regionSpecificProperties = new OffAmazonPaymentsService_RegionSpecificProperties();
    }
    
    /**
     * Setup the region specific properties file to use for the
     * merchant values class
     *
     * @param OffAmazonPaymentsService_RegionSpecificProperties instance to use
     *
     * @return this
     */
    public function withRegionSpecificProperties(
            $regionSpecificProperties)
    {
        $this->_regionSpecificProperties = $regionSpecificProperties;
        return $this;
    }

    /**
     * Create a new instance of the merchant values object
     * with the configured properties
     *
     * @return OffAmazonPaymentsService_MerchantValues
     */
    public function build() {
        return new OffAmazonPaymentsService_MerchantValues(
            $this->_config, 
            $this->_regionSpecificProperties
        );
    }
}
?>