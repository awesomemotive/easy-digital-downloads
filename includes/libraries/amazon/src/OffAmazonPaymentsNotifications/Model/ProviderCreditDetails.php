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
 * OffAmazonPaymentsNotifications_Model_ProviderCreditDetails
 *
 * Properties:
 * <ul>
 *
 * <li>AmazonProviderCreditId: string</li>
 * <li>SellerId: string</li>
 * <li>ProviderSellerId: string</li>
 * <li>CreditAmount: OffAmazonPaymentsNotifications_Model_Price</li>
 * <li>CreditReversalAmount: OffAmazonPaymentsNotifications_Model_Price</li>
 * <li>CreditReversalIdList: OffAmazonPaymentsNotifications_Model_IdList</li>
 * <li>CreationTimestamp: string</li>
 * <li>CreditStatus: OffAmazonPaymentsNotifications_Model_Status</li>
 *
 * </ul>
 */
class OffAmazonPaymentsNotifications_Model_ProviderCreditDetails extends OffAmazonPayments_Model {
	
	/**
	 * Construct new OffAmazonPaymentsNotifications_Model_ProviderCreditDetails
	 *
	 * @param mixed $data
	 *        	DOMElement or Associative Array to construct from.
	 *        	
	 *        	Valid properties:
	 *        	<ul>
	 *        	
	 *        	<li>AmazonProviderCreditId: string</li>
	 *        	<li>SellerId: string</li>
	 *        	<li>ProviderSellerId: string</li>
	 *        	<li>CreditAmount: OffAmazonPaymentsNotifications_Model_Price</li>
	 *        	<li>CreditReversalAmount: OffAmazonPaymentsNotifications_Model_Price</li>
	 *        	<li>CreditReversalIdList: OffAmazonPaymentsNotifications_Model_IdList</li>
	 *        	<li>CreationTimestamp: string</li>
	 *        	<li>CreditStatus: OffAmazonPaymentsNotifications_Model_Status</li>
	 *        	
	 *        	</ul>
	 */
	public function __construct($data = null) {
		$this->fields = array (
				'AmazonProviderCreditId' => array (
						'FieldValue' => null,
						'FieldType' => 'string' 
				),
				'SellerId' => array (
						'FieldValue' => null,
						'FieldType' => 'string' 
				),
				'ProviderSellerId' => array (
						'FieldValue' => null,
						'FieldType' => 'string' 
				),
				
				'CreditAmount' => array (
						'FieldValue' => null,
						'FieldType' => 'OffAmazonPaymentsNotifications_Model_Price' 
				),
				
				'CreditReversalAmount' => array (
						'FieldValue' => null,
						'FieldType' => 'OffAmazonPaymentsNotifications_Model_Price' 
				),
				
				'CreditReversalIdList' => array (
						'FieldValue' => null,
						'FieldType' => 'OffAmazonPaymentsNotifications_Model_IdList' 
				),
				
				'CreationTimestamp' => array (
						'FieldValue' => null,
						'FieldType' => 'string' 
				),
				
				'CreditStatus' => array (
						'FieldValue' => null,
						'FieldType' => 'OffAmazonPaymentsNotifications_Model_Status' 
				) 
		);
		parent::__construct ( $data );
	}
	
	/**
	 * Gets the value of the AmazonProviderCreditId property.
	 *
	 * @return string AmazonProviderCreditId
	 */
	public function getAmazonProviderCreditId() {
		return $this->fields ['AmazonProviderCreditId'] ['FieldValue'];
	}
	
	/**
	 * Sets the value of the AmazonProviderCreditId property.
	 *
	 * @param
	 *        	string AmazonProviderCreditId
	 * @return this instance
	 */
	public function setAmazonProviderCreditId($value) {
		$this->fields ['AmazonProviderCreditId'] ['FieldValue'] = $value;
		return $this;
	}
	
	/**
	 * Sets the value of the AmazonProviderCreditId and returns this instance
	 *
	 * @param string $value
	 *        	AmazonProviderCreditId
	 * @return OffAmazonPaymentsNotifications_Model_ProviderCreditDetails instance
	 */
	public function withAmazonProviderCreditId($value) {
		$this->setAmazonProviderCreditId ( $value );
		return $this;
	}
	
	/**
	 * Checks if AmazonProviderCreditId is set
	 *
	 * @return bool true if AmazonProviderCreditId is set
	 */
	public function isSetAmazonProviderCreditId() {
		return ! is_null ( $this->fields ['AmazonProviderCreditId'] ['FieldValue'] );
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
	 * @return OffAmazonPaymentsNotifications_Model_ProviderCreditDetails instance
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
	 * Gets the value of the ProviderSellerId property.
	 *
	 * @return string ProviderSellerId
	 */
	public function getProviderSellerId() {
		return $this->fields ['ProviderSellerId'] ['FieldValue'];
	}
	
	/**
	 * Sets the value of the ProviderSellerId property.
	 *
	 * @param
	 *        	string ProviderSellerId
	 * @return this instance
	 */
	public function setProviderSellerId($value) {
		$this->fields ['ProviderSellerId'] ['FieldValue'] = $value;
		return $this;
	}
	
	/**
	 * Sets the value of the ProviderSellerId and returns this instance
	 *
	 * @param string $value
	 *        	ProviderSellerId
	 * @return OffAmazonPaymentsNotifications_Model_ProviderCreditDetails instance
	 */
	public function withProviderSellerId($value) {
		$this->setProviderSellerId ( $value );
		return $this;
	}
	
	/**
	 * Checks if ProviderSellerId is set
	 *
	 * @return bool true if ProviderSellerId is set
	 */
	public function isSetProviderSellerId() {
		return ! is_null ( $this->fields ['ProviderSellerId'] ['FieldValue'] );
	}
	
	/**
	 * Gets the value of the CreditAmount.
	 *
	 * @return Price CreditAmount
	 */
	public function getCreditAmount() {
		return $this->fields ['CreditAmount'] ['FieldValue'];
	}
	
	/**
	 * Sets the value of the CreditAmount.
	 *
	 * @param
	 *        	Price CreditAmount
	 * @return void
	 */
	public function setCreditAmount($value) {
		$this->fields ['CreditAmount'] ['FieldValue'] = $value;
		return;
	}
	
	/**
	 * Sets the value of the CreditAmount and returns this instance
	 *
	 * @param Price $value
	 *        	CreditAmount
	 * @return OffAmazonPaymentsNotifications_Model_ProviderCreditDetails instance
	 */
	public function withCreditAmount($value) {
		$this->setCreditAmount ( $value );
		return $this;
	}
	
	/**
	 * Checks if CreditAmount is set
	 *
	 * @return bool true if CreditAmount property is set
	 */
	public function isSetCreditAmount() {
		return ! is_null ( $this->fields ['CreditAmount'] ['FieldValue'] );
	}
	
	/**
	 * Gets the value of the CreditReversalAmount.
	 *
	 * @return Price CreditReversalAmount
	 */
	public function getCreditReversalAmount() {
		return $this->fields ['CreditReversalAmount'] ['FieldValue'];
	}
	
	/**
	 * Sets the value of the CreditReversalAmount.
	 *
	 * @param
	 *        	Price CreditReversalAmount
	 * @return void
	 */
	public function setCreditReversalAmount($value) {
		$this->fields ['CreditReversalAmount'] ['FieldValue'] = $value;
		return;
	}
	
	/**
	 * Sets the value of the CreditReversalAmount and returns this instance
	 *
	 * @param Price $value
	 *        	CreditReversalAmount
	 * @return OffAmazonPaymentsNotifications_Model_ProviderCreditDetails instance
	 */
	public function withCreditReversalAmount($value) {
		$this->setCreditReversalAmount ( $value );
		return $this;
	}
	
	/**
	 * Checks if CreditReversalAmount is set
	 *
	 * @return bool true if CreditReversalAmount property is set
	 */
	public function isSetCreditReversalAmount() {
		return ! is_null ( $this->fields ['CreditReversalAmount'] ['FieldValue'] );
	}
	
	/**
	 * Gets the value of the CreditReversalIdList.
	 *
	 * @return IdList CreditReversalIdList
	 */
	public function getCreditReversalIdList() {
		return $this->fields ['CreditReversalIdList'] ['FieldValue'];
	}
	
	/**
	 * Sets the value of the CreditReversalIdList.
	 *
	 * @param
	 *        	IdList CreditReversalIdList
	 * @return void
	 */
	public function setCreditReversalIdList($value) {
		$this->fields ['CreditReversalIdList'] ['FieldValue'] = $value;
		return;
	}
	
	/**
	 * Sets the value of the CreditReversalIdList and returns this instance
	 *
	 * @param IdList $value
	 *        	CreditReversalIdList
	 * @return OffAmazonPaymentsNotifications_Model_ProviderCreditDetails instance
	 */
	public function withCreditReversalIdList($value) {
		$this->setCreditReversalIdList ( $value );
		return $this;
	}
	
	/**
	 * Checks if CreditReversalIdList is set
	 *
	 * @return bool true if CreditReversalIdList property is set
	 */
	public function isSetCreditReversalIdList() {
		return ! is_null ( $this->fields ['CreditReversalIdList'] ['FieldValue'] );
	}
	
	/**
	 * Gets the value of the CreationTimestamp property.
	 *
	 * @return string CreationTimestamp
	 */
	public function getCreationTimestamp() {
		return $this->fields ['CreationTimestamp'] ['FieldValue'];
	}
	
	/**
	 * Sets the value of the CreationTimestamp property.
	 *
	 * @param
	 *        	string CreationTimestamp
	 * @return this instance
	 */
	public function setCreationTimestamp($value) {
		$this->fields ['CreationTimestamp'] ['FieldValue'] = $value;
		return $this;
	}
	
	/**
	 * Sets the value of the CreationTimestamp and returns this instance
	 *
	 * @param string $value
	 *        	CreationTimestamp
	 * @return OffAmazonPaymentsNotifications_Model_ProviderCreditDetails instance
	 */
	public function withCreationTimestamp($value) {
		$this->setCreationTimestamp ( $value );
		return $this;
	}
	
	/**
	 * Checks if CreationTimestamp is set
	 *
	 * @return bool true if CreationTimestamp is set
	 */
	public function isSetCreationTimestamp() {
		return ! is_null ( $this->fields ['CreationTimestamp'] ['FieldValue'] );
	}
	
	/**
	 * Gets the value of the CreditStatus.
	 *
	 * @return Status CreditStatus
	 */
	public function getCreditStatus() {
		return $this->fields ['CreditStatus'] ['FieldValue'];
	}
	
	/**
	 * Sets the value of the CreditStatus.
	 *
	 * @param
	 *        	Status CreditStatus
	 * @return void
	 */
	public function setCreditStatus($value) {
		$this->fields ['CreditStatus'] ['FieldValue'] = $value;
		return;
	}
	
	/**
	 * Sets the value of the CreditStatus and returns this instance
	 *
	 * @param Status $value
	 *        	CreditStatus
	 * @return OffAmazonPaymentsNotifications_Model_ProviderCreditDetails instance
	 */
	public function withCreditStatus($value) {
		$this->setCreditStatus ( $value );
		return $this;
	}
	
	/**
	 * Checks if CreditStatus is set
	 *
	 * @return bool true if CreditStatus property is set
	 */
	public function isSetCreditStatus() {
		return ! is_null ( $this->fields ['CreditStatus'] ['FieldValue'] );
	}
}