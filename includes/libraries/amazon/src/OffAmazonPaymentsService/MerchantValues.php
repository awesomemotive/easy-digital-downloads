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
require_once 'OffAmazonPayments/OffAmazonPaymentsServiceUtils.php';

class OffAmazonPaymentsService_MerchantValues
{
    private $_config;

    private $_regionSpecificProperties;

    private $_utils;

    public function __construct($config, $regionSpecificProperties) {
        if (!isset($config)) {
            throw new InvalidArgumentException("no configuration specificed, please set variables with $config variable");
        }

        if (!isset($regionSpecificProperties)) {
            throw new InvalidArgumentException("no regionSpecificProperties object injected, check caller of OffAmazonPaymentsService_MerchantValues");
        }

        $this->_config = $config;
        $this->_regionSpecificProperties = $regionSpecificProperties;

        $this->_utils = new OffAmazonPaymentsServiceUtils();

        if (empty($this->_config['merchantId'])) {
            throw new InvalidArgumentException("merchantId not set in the properties file");
        }

        if (empty($this->_config['accessKey'])) {
            throw new InvalidArgumentException("accessKey not set in the properties file");
        }
        
        if (empty($this->_config['secretKey'])) {
            throw new InvalidArgumentException("secretKey not set in the properties file");
        }

        if (empty($this->_config['cnName'])) {
            throw new InvalidArgumentException("cnName not set in the properties file");
        }

        if (empty($this->_config['applicationName'])) {
            throw new InvalidArgumentException(
                "applicationName not set in the properties file"
            );
        }

        if (empty($this->_config['applicationVersion'])) {
            throw new InvalidArgumentException(
                "applicationVersion not set in the properties file"
            );
        }
        
        if (empty($this->_config['region'])) {
            throw new InvalidArgumentException("region not set in the properties file");
        } 

        $this->_config['region'] = $this->_validateRegion($this->_config['region']);
        
        if (empty($this->_config['environment'])) {
            throw new InvalidArgumentException("environment not set in the properties file");
        }
        $this->_config['environment'] = $this->_validateEnvironment($this->_config['environment']);

        if (empty($this->_config['caBundleFile'])) {
            $this->_config['caBundleFile'] = null;
        }

        if (empty($this->_config['serviceUrl'])) {
            $this->_config['serviceUrl'] = null;
        }

        if (empty($this->_config['widgetUrl'])) {
            $this->_config['widgetUrl'] = null;
        }

        if (empty($this->_config['proxyHost'])) {
            $this->_config['proxyHost'] = null;
        }

        if (empty($this->_config['proxyPort'])) {
            $this->_config['proxyPort'] = null;
        }

        if (empty($this->_config['proxyUsername'])) {
            $this->_config['proxyUsername'] = null;
        }

        if (empty($this->_config['proxyPassword'])) {
            $this->_config['proxyPassword'] = null;
        }
    }

    public function getMerchantId()
    {
        return $this->_config['merchantId'];
    }

    public function getAccessKey()
    {
        return $this->_config['accessKey'];
    }

    public function getSecretKey()
    {
        return $this->_config['secretKey'];
    }

    public function getServiceUrl()
    {
        return $this->_regionSpecificProperties->getServiceUrlFor(
            $this->getRegion(), 
            $this->getEnvironment(), 
            $this->_config['serviceUrl']
        );
    }
    
    public function getWidgetUrl()
    {
        return $this->_regionSpecificProperties->getWidgetUrlFor(
            $this->getRegion(), 
            $this->getEnvironment(),
            $this->getMerchantId(),
            $this->_config['widgetUrl']
        );
    }
    
    public function getCurrency()
    {
        return $this->_regionSpecificProperties->getCurrencyFor($this->getRegion());
    }
    
    public function getApplicationName()
    {
        return $this->_config['applicationName'];
    }

    public function getApplicationVersion()
    {
        return $this->_config['applicationVersion'];
    }
    
    public function getRegion()
    {
        return $this->_config['region'];
    }
    
    public function getEnvironment()
    {
        return $this->_config['environment'];
    }

    public function getCaBundleFile()
    {
        return $this->_config['caBundleFile'];
    }
    
    public function getClientId()
    {
        return $this->_config['clientId'];
    }
    
    public function getProxyUsername()
    {
        return $this->_config['proxyUsername'];
    }

    public function getProxyPassword()
    {
        return $this->_config['proxyPassword'];
    }

    public function getProxyHost()
    {
        return $this->_config['proxyHost'];
    }

    public function getProxyPort()
    {
        return $this->_config['proxyPort'];
    }

    public function getCnName()
    {
        return $this->_config['cnName'];
    }

    public function isProxyConfigured() {
        return !empty($this->getProxyHost()) && !empty($this->getProxyPort());
    }

    public function isProxyAuthenticationConfigured() {
        return $this->isProxyConfigured() && 
            !empty($this->getProxyUsername()) && 
            !empty($this->getProxyPassword());
    }

    public function isCaBundleConfigured() {
        return !empty($this->getCaBundleFile()); 
    }

    public function getUserAgentString() {
        return $this->_utils->buildUserAgentString(
            $this->getApplicationName(),
            $this->getApplicationVersion()
        );
    }

    private function _validateRegion($region)
    {
        include_once 'Regions.php';
        return self::_getValueForConstant($region, new OffAmazonPaymentsService_Regions());
    }
    
    private static function _validateEnvironment($environment)
    {
        include_once 'Environments.php';
        return self::_getValueForConstant($environment, new OffAmazonPaymentsService_Environments());
    }
    
    private static function _getValueForConstant($constant, $valuesClass)
    {
        $rc = new ReflectionClass($valuesClass);
        $value = $rc->getConstant(strtoupper($constant));
        if ($value == null) {
            $allowedValues = implode(",", array_keys($rc->getConstants()));
            throw new InvalidArgumentException(
                "check your property file: " . $constant . " is not a valid option.  Available options are: " . $allowedValues
            );
        } 
        
        return $value;
    }
}
?>
