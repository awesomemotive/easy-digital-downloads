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


require_once 'OffAmazonPaymentsService.config.inc.php';
require_once 'Client.php';

/**
 * Encapsulation of properties that are tied to a region/environment pairing
 * 
 * Provides mappings for:
 *   - widget url
 *   - mws service url
 *   - currency code
 */
class OffAmazonPaymentsService_RegionSpecificProperties
{
	private $_widgetUrls = array(
		'eu' => 'https://static-eu.payments-amazon.com',
		'na' => 'https://static-na.payments-amazon.com'
	);

	private $_serviceUrls = array(
		'eu' => 'https://mws-eu.amazonservices.com',
		'na' => 'https://mws.amazonservices.com'
	);
	
	private $_currencyCodes = array(
		'de' => 'EUR',
		'uk' => 'GBP',
		'us' => 'USD',
		'na' => 'USD'
	);
	
	private $_regionMappings = array(
		'de' => 'eu',
		'na' => 'na',
		'uk' => 'eu',
		'us' => 'na'
	);
	
	const WIDGET_FORMAT_STRING = '%s/OffAmazonPayments/%s%s/js/Widgets.js';
	const SERVICE_FORMAT_STRING = '%s/OffAmazonPayments%s/%s';
	
	/**
	 * Return the correct widget url for the javascript widget
	 * 
	 * @param string $region
	 * @param string $environment
	 * @param string $merchantId
	 * @param string $overrideUrl 
	 * 
	 * @return string widgetUrl
	 */
	public function getWidgetUrlFor($region, $environment, $merchantId, $overrideUrl)
	{
		return sprintf(self::WIDGET_FORMAT_STRING, 
					$this->_getWidgetHostFor($region, $overrideUrl), 
					$this->_getWidgetRegionFor($region), 
			    	$this->_getWidgetEnvironmentFor($environment, $region));
	}
	
	/**
	 * Return the mws service for this region
	 * 
	 * @param string $region       merchant region - us, na, uk, de
	 * @param string $environment  service - live, sandbox
	 * @param string $overrideUrl  override url
	 * 
	 * @return string mws service url
	 */
	public function getServiceUrlFor($region, $environment, $overrideUrl)
	{
		return sprintf(self::SERVICE_FORMAT_STRING,
					$this->_getServiceHostFor($region, $overrideUrl),
					$this->_getSectionNameFor($environment),
					OffAmazonPaymentsService_Client::SERVICE_VERSION);
	}
	
	/**
	 * Get the currency code for the given region
	 * 
	 * @param string $region us,uk,de,na
	 * 
	 * @return string currency code
	 */
	public function getCurrencyFor($region)
	{
		$this->_validateRegionIsDefined($region, $this->_currencyCodes);
		return $this->_currencyCodes[$region];
	}
	
	/**
	 * Return the correct host for the widget url based on the region
	 * 
	 * @param string $region 		us,uk,de,na
	 * @param string $overrideUrl	override string for widget host
	 * 
	 * @return string widget host
	 */
	private function _getWidgetHostFor($region, $overrideUrl) 
	{
		if (empty($overrideUrl)) {
			return $this->_getRegionPropertyFor($region, $this->_widgetUrls);
		}
		
		return $overrideUrl;
	}
	
	/**
	 * Return the correct host for the service url based on the region
	 * 
	 * @param string $region 		us,uk,de,na
	 * @param string $overrideUrl   override string for service host
	 * 
	 * @return string mws host
	 */
	private function _getServiceHostFor($region, $overrideUrl)
	{
		if (empty($overrideUrl)) {
			return $this->_getRegionPropertyFor($region, $this->_serviceUrls);
		}
		
		return $overrideUrl;
	}
	
	/**
	 * Return the correct value for this region from an
	 * associate array keyed by realms
	 * 
	 * @param string $region us,uk,de,na
	 * @param array $properties associate array of realms to property values
	 * 
	 * @return string property for region
	 */
	private function _getRegionPropertyFor($region, $properties)
	{
		$this->_validateRegionIsDefined($region, $this->_regionMappings);	
		return $properties[$this->_regionMappings[$region]];
	}
	
	/**
	 * Throw an exception if the region does not hold a valid mapping
	 * in an array
	 * 
	 * @param string $region defined region - us,na,uk,de
	 * @param array $array array to validate
	 * 
	 * @return void
	 */
	private function _validateRegionIsDefined($region, $array)
	{
		if (array_key_exists($region, $array) == FALSE) {
			throw new InvalidArgumentException("No region mapping defined for region " . $region);
		}
	}
	
	/**
	 * Return the correct region if it requires additional mapping
	 * 
	 * @param string $region configured region
	 * 
	 * @return string url region param
	 */
	private function _getWidgetRegionFor($region)
	{
		// Since na was a go live region, we maintain special handling so that compatablity is not broken
		if (strcasecmp($region, 'na') == 0) {
			return 'us';   
		} 
		
		return strtolower($region);
	}
	
	/**
	 * Generate the correct widget environment string, based on the environement
	 * 
	 * @param string $environment currently configured environment
	 * 
	 * @return string postfix for widget url string
	 */
	private function _getWidgetEnvironmentFor($environment, $region)
	{
		if(strcasecmp($region, 'us') != 0 && strcasecmp($region, 'na') != 0) {
			return ( $this->_isSandbox($environment) ? '/sandbox/lpa' : '/lpa');
		} else {
			return ( $this->_isSandbox($environment) ? '/sandbox' : '');
		}
	}
	
	/**
	 * Return the correct section name postfix for the
	 * given environment
	 *
	 * @param string environment name
	 *
	 * @return string section name postfix
	 */
	private function _getSectionNameFor($environment)
	{
		return ( $this->_isSandbox($environment) ? '_Sandbox' : '');
	}
	
	/**
	 * Check if the environment is sandbox
	 * 
	 * @param string environment name
	 * 
	 * @return bool true if environment is sandbox
	 */
	private function _isSandbox($environment)
	{
		return strcasecmp($environment,'sandbox') == 0;
	}
}

?>