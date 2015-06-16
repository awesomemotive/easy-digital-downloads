<?php

/*******************************************************************************
 *  Copyright 2011 Amazon.com, Inc. or its affiliates. All Rights Reserved.
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

/**
 *
 * @see OffAmazonPaymentsNotifications_Model
 */
require_once 'OffAmazonPayments/Model.php';

/**
 * OffAmazonPaymentsNotifications_Model_MerchantRegistrationDetails
 *
 * Properties:
 * <ul>
 *
 * <li>SellerId: string</li>
 * <li>Type: string</li>
 * <li>Options: OffAmazonPaymentsNotifications_Model_SolutionProviderOptions</li>
 *
 * </ul>
 */
class OffAmazonPaymentsNotifications_Model_MerchantRegistrationDetails extends OffAmazonPayments_Model {
	
	/**
	 * Construct new OffAmazonPaymentsNotifications_Model_MerchantRegistrationDetails
	 *
	 * @param mixed $data
	 *        	DOMElement or Associative Array to construct from.
	 *        	
	 *        	Valid properties:
	 *        	<ul>
	 *        	
	 *        	<li>SellerId: string</li>
	 *        	<li>Type: string</li>
	 *        	<li>Options: OffAmazonPaymentsNotifications_Model_SolutionProviderOptions</li>
	 *        	
	 *        	</ul>
	 */
	public function __construct($data = null) {
		$this->fields = array (
				'SellerId' => array (
						'FieldValue' => null,
						'FieldType' => 'string' 
				),
				'Type' => array (
						'FieldValue' => null,
						'FieldType' => 'string' 
				),
				'Options' => array (
						'FieldValue' => null,
						'FieldType' => 'OffAmazonPaymentsNotifications_Model_SolutionProviderOptions' 
				)
		);
		parent::__construct ( $data );
	}

	/**
	 * Gets the value of the SellerId property.
	 *
	 * @return string SellerId
	 */
	public function getSellerId() {
		return $this->fields ['SellerId'] ['FieldValue'];
	}
	
	/**
	 * Sets the value of the SellerId property.
	 *
	 * @param
	 *        	string SellerId
	 * @return this instance
	 */
	public function setSellerId($value) {
		$this->fields ['SellerId'] ['FieldValue'] = $value;
		return $this;
	}
	
	/**
	 * Sets the value of the SellerId and returns this instance
	 *
	 * @param string $value
	 *        	SellerId
	 * @return OffAmazonPaymentsNotifications_Model_MerchantRegistrationDetails instance
	 */
	public function withSellerId($value) {
		$this->setSellerId ( $value );
		return $this;
	}
	
	/**
	 * Checks if SellerId is set
	 *
	 * @return bool true if SellerId is set
	 */
	public function isSetSellerId() {
		return ! is_null ( $this->fields ['SellerId'] ['FieldValue'] );
	}
	
	/**
	 * Gets the value of the Type property.
	 *
	 * @return string Type
	 */
	public function getType() {
		return $this->fields ['Type'] ['FieldValue'];
	}
	
	/**
	 * Sets the value of the Type property.
	 *
	 * @param
	 *        	string Type
	 * @return this instance
	 */
	public function setType($value) {
		$this->fields ['Type'] ['FieldValue'] = $value;
		return $this;
	}
	
	/**
	 * Sets the value of the Type and returns this instance
	 *
	 * @param string $value
	 *        	Type
	 * @return OffAmazonPaymentsNotifications_Model_MerchantRegistrationDetails instance
	 */
	public function withType($value) {
		$this->setType ( $value );
		return $this;
	}
	
	/**
	 * Checks if Type is set
	 *
	 * @return bool true if Type is set
	 */
	public function isSetType() {
		return ! is_null ( $this->fields ['Type'] ['FieldValue'] );
	}
	
	/**
	 * Gets the value of the Options.
	 *
	 * @return IdList Options
	 */
	public function getOptions() {
		return $this->fields ['Options'] ['FieldValue'];
	}
	
	/**
	 * Sets the value of the Options.
	 *
	 * @param
	 *        	IdList Options
	 * @return void
	 */
	public function setOptions($value) {
		$this->fields ['Options'] ['FieldValue'] = $value;
		return;
	}
	
	/**
	 * Sets the value of the Options and returns this instance
	 *
	 * @param IdList $value
	 *        	Options
	 * @return OffAmazonPaymentsNotifications_Model_MerchantRegistrationDetails instance
	 */
	public function withOptions($value) {
		$this->setOptions ( $value );
		return $this;
	}
	
	/**
	 * Checks if SolutionProviderOptions is set
	 *
	 * @return bool true if SolutionProviderOptions property is set
	 */
	public function isSetOptions() {
		return ! is_null ( $this->fields ['Options'] ['FieldValue'] );
	}

}