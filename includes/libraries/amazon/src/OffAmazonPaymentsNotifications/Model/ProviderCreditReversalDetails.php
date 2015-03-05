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
 * @see OffAmazonPaymentsNotification_Model
 */
require_once 'OffAmazonPayments/Model.php';

/**
 * OffAmazonPaymentsNotification_Model_ProviderCreditReversalDetails
 *
 * Properties:
 * <ul>
 *
 * <li>AmazonProviderCreditReversalId: string</li>
 * <li>SellerId: string</li>
 * <li>ProviderSellerId: string</li>
 * <li>CreditReversalReferenceId: string</li>
 * <li>CreditReversalAmount: OffAmazonPaymentsNotification_Model_Price</li>
 * <li>CreationTimestamp: string</li>
 * <li>CreditReversalStatus: OffAmazonPaymentsNotification_Model_Status</li>
 * <li>CreditReversalNote: string</li>
 *
 * </ul>
 */
class OffAmazonPaymentsNotifications_Model_ProviderCreditReversalDetails extends OffAmazonPayments_Model {
	
	/**
	 * Construct new OffAmazonPaymentsNotification_Model_ProviderCreditReversalDetails
	 *
	 * @param mixed $data
	 *        	DOMElement or Associative Array to construct from.
	 *        	
	 *        	Valid properties:
	 *        	<ul>
	 *        	
	 *        	<li>AmazonProviderCreditReversalId: string</li>
	 *        	<li>SellerId: string</li>
	 *        	<li>ProviderSellerId: string</li>
	 *        	<li>CreditReversalReferenceId: string</li>
	 *        	<li>CreditReversalAmount: OffAmazonPaymentsNotifications_Model_Price</li>
	 *        	<li>CreationTimestamp: string</li>
	 *        	<li>CreditReversalStatus: OffAmazonPaymentsNotifications_Model_Status</li>
	 *        	<li>CreditReversalNote: string</li>
	 *        	</ul>
	 */
	public function __construct($data = null) {
		$this->fields = array (
				'AmazonProviderCreditReversalId' => array (
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
				'CreditReversalReferenceId' => array (
						'FieldValue' => null,
						'FieldType' => 'string' 
				),
				
				'CreditReversalAmount' => array (
						'FieldValue' => null,
						'FieldType' => 'OffAmazonPaymentsNotifications_Model_Price' 
				),
				
				'CreationTimestamp' => array (
						'FieldValue' => null,
						'FieldType' => 'string' 
				),
				
				'CreditReversalStatus' => array (
						'FieldValue' => null,
						'FieldType' => 'OffAmazonPaymentsNotifications_Model_Status' 
				),
				
				'CreditReversalNote' => array (
						'FieldValue' => null,
						'FieldType' => 'string' 
				) 
		);
		parent::__construct ( $data );
	}
	
	/**
	 * Gets the value of the AmazonProviderCreditReversalId property.
	 *
	 * @return string AmazonProviderCreditReversalId
	 */
	public function getAmazonProviderCreditReversalId() {
		return $this->fields ['AmazonProviderCreditReversalId'] ['FieldValue'];
	}
	
	/**
	 * Sets the value of the AmazonProviderCreditReversalId property.
	 *
	 * @param
	 *        	string AmazonProviderCreditReversalId
	 * @return this instance
	 */
	public function setAmazonProviderCreditReversalId($value) {
		$this->fields ['AmazonProviderCreditReversalId'] ['FieldValue'] = $value;
		return $this;
	}
	
	/**
	 * Sets the value of the AmazonProviderCreditReversalId and returns this instance
	 *
	 * @param string $value
	 *        	AmazonProviderCreditReversalId
	 * @return OffAmazonPaymentsNotification_Model_ProviderCreditReversalDetails instance
	 */
	public function withAmazonProviderCreditReversalId($value) {
		$this->setAmazonProviderCreditReversalId ( $value );
		return $this;
	}
	
	/**
	 * Checks if AmazonProviderCreditReversalId is set
	 *
	 * @return bool true if AmazonProviderCreditReversalId is set
	 */
	public function isSetAmazonProviderCreditReversalId() {
		return ! is_null ( $this->fields ['AmazonProviderCreditReversalId'] ['FieldValue'] );
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
	 * @return OffAmazonPaymentsNotification_Model_ProviderCreditReversalDetails instance
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
	 * @return OffAmazonPaymentsNotification_Model_ProviderCreditReversalDetails instance
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
	 * Gets the value of the CreditReversalReferenceId property.
	 *
	 * @return string CreditReversalReferenceId
	 */
	public function getCreditReversalReferenceId() {
		return $this->fields ['CreditReversalReferenceId'] ['FieldValue'];
	}
	
	/**
	 * Sets the value of the CreditReversalReferenceId property.
	 *
	 * @param
	 *        	string CreditReversalReferenceId
	 * @return this instance
	 */
	public function setCreditReversalReferenceId($value) {
		$this->fields ['CreditReversalReferenceId'] ['FieldValue'] = $value;
		return $this;
	}
	
	/**
	 * Sets the value of the CreditReversalReferenceId and returns this instance
	 *
	 * @param string $value
	 *        	CreditReversalReferenceId
	 * @return OffAmazonPaymentsNotification_Model_ProviderCreditReversalDetails instance
	 */
	public function withCreditReversalReferenceId($value) {
		$this->setCreditReversalReferenceId ( $value );
		return $this;
	}
	
	/**
	 * Checks if CreditReversalReferenceId is set
	 *
	 * @return bool true if CreditReversalReferenceId is set
	 */
	public function isSetCreditReversalReferenceId() {
		return ! is_null ( $this->fields ['CreditReversalReferenceId'] ['FieldValue'] );
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
	 * @return OffAmazonPaymentsNotification_Model_ProviderCreditReversalDetails instance
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
	 * @return OffAmazonPaymentsNotification_Model_ProviderCreditReversalDetails instance
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
	 * Gets the value of the CreditReversalStatus.
	 *
	 * @return Status CreditReversalStatus
	 */
	public function getCreditReversalStatus() {
		return $this->fields ['CreditReversalStatus'] ['FieldValue'];
	}
	
	/**
	 * Sets the value of the CreditReversalStatus.
	 *
	 * @param
	 *        	Status CreditReversalStatus
	 * @return void
	 */
	public function setCreditReversalStatus($value) {
		$this->fields ['CreditReversalStatus'] ['FieldValue'] = $value;
		return;
	}
	
	/**
	 * Sets the value of the CreditReversalStatus and returns this instance
	 *
	 * @param Status $value
	 *        	CreditReversalStatus
	 * @return OffAmazonPaymentsNotification_Model_ProviderCreditReversalDetails instance
	 */
	public function withCreditReversalStatus($value) {
		$this->setCreditReversalStatus ( $value );
		return $this;
	}
	
	/**
	 * Checks if CreditReversalStatus is set
	 *
	 * @return bool true if CreditReversalStatus property is set
	 */
	public function isSetCreditReversalStatus() {
		return ! is_null ( $this->fields ['CreditReversalStatus'] ['FieldValue'] );
	}
	
	/**
	 * Gets the value of the CreditReversalNote property.
	 *
	 * @return string CreditReversalNote
	 */
	public function getCreditReversalNote() {
		return $this->fields ['CreditReversalNote'] ['FieldValue'];
	}
	
	/**
	 * Sets the value of the CreditReversalNote property.
	 *
	 * @param
	 *        	string CreditReversalNote
	 * @return this instance
	 */
	public function setCreditReversalNote($value) {
		$this->fields ['CreditReversalNote'] ['FieldValue'] = $value;
		return $this;
	}
	
	/**
	 * Sets the value of the CreditReversalNote and returns this instance
	 *
	 * @param string $value
	 *        	CreditReversalNote
	 * @return OffAmazonPaymentsNotification_Model_ProviderCreditReversalDetails instance
	 */
	public function withCreditReversalNote($value) {
		$this->setCreditReversalNote ( $value );
		return $this;
	}
	
	/**
	 * Checks if CreditReversalNote is set
	 *
	 * @return bool true if CreditReversalNote is set
	 */
	public function isSetCreditReversalNote() {
		return ! is_null ( $this->fields ['CreditReversalNote'] ['FieldValue'] );
	}
}