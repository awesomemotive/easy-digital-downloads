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
require_once 'OffAmazonPayments/Model.php';

/**
 * OffAmazonPaymentsNotifications_Model_BillingAgreementLimits
 * 
 * Properties:
 * <ul>
 * 
 * <li>AmountLimitPerTimePeriod: OffAmazonPaymentsNotifications_Model_Price</li>
 * <li>TimePeriodStartDate: string</li>
 * <li>TimePeriodEndDate: string</li>
 * <li>CurrentRemainingBalance: OffAmazonPaymentsNotifications_Model_Price</li>
 *
 * </ul>
 */
class OffAmazonPaymentsNotifications_Model_BillingAgreementLimits extends OffAmazonPayments_Model
{

    /**
     * Construct new OffAmazonPaymentsNotifications_Model_BillingAgreementLimits
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>AmountLimitPerTimePeriod: OffAmazonPaymentsNotifications_Model_Price</li>
     * <li>TimePeriodStartDate: string</li>
     * <li>TimePeriodEndDate: string</li>
     * <li>CurrentRemainingBalance: OffAmazonPaymentsNotifications_Model_Price</li>
     *
     * </ul>
     */
    public function __construct ($data = null)
    {
        $this->fields = array(
            
            'AmountLimitPerTimePeriod' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsNotifications_Model_Price'
            ),
            
            'TimePeriodStartDate' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            
            'TimePeriodEndDate' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            
            'CurrentRemainingBalance' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsNotifications_Model_Price'
            )
        )
        ;
        parent::__construct($data);
    }

    /**
     * Gets the value of the AmountLimitPerTimePeriod.
     * 
     * @return OffAmazonPaymentsNotifications_Model_Price AmountLimitPerTimePeriod
     */
    public function getAmountLimitPerTimePeriod ()
    {
        return $this->fields['AmountLimitPerTimePeriod']['FieldValue'];
    }

    /**
     * Sets the value of the AmountLimitPerTimePeriod.
     * 
     * @param OffAmazonPaymentsNotifications_Model_Price AmountLimitPerTimePeriod
     * @return void
     */
    public function setAmountLimitPerTimePeriod ($value)
    {
        $this->fields['AmountLimitPerTimePeriod']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the AmountLimitPerTimePeriod  and returns this instance
     * 
     * @param OffAmazonPaymentsNotifications_Model_Price $value AmountLimitPerTimePeriod
     * @return OffAmazonPaymentsNotifications_Model_BillingAgreementLimits instance
     */
    public function withAmountLimitPerTimePeriod ($value)
    {
        $this->setAmountLimitPerTimePeriod($value);
        return $this;
    }

    /**
     * Checks if AmountLimitPerTimePeriod  is set
     * 
     * @return bool true if AmountLimitPerTimePeriod property is set
     */
    public function isSetAmountLimitPerTimePeriod ()
    {
        return ! is_null($this->fields['AmountLimitPerTimePeriod']['FieldValue']);
    }

    /**
     * Gets the value of the TimePeriodStartDate property.
     * 
     * @return string TimePeriodStartDate
     */
    public function getTimePeriodStartDate ()
    {
        return $this->fields['TimePeriodStartDate']['FieldValue'];
    }

    /**
     * Sets the value of the TimePeriodStartDate property.
     * 
     * @param string TimePeriodStartDate
     * @return this instance
     */
    public function setTimePeriodStartDate ($value)
    {
        $this->fields['TimePeriodStartDate']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the TimePeriodStartDate and returns this instance
     * 
     * @param string $value TimePeriodStartDate
     * @return OffAmazonPaymentsNotifications_Model_BillingAgreementLimits instance
     */
    public function withTimePeriodStartDate ($value)
    {
        $this->setTimePeriodStartDate($value);
        return $this;
    }

    /**
     * Checks if TimePeriodStartDate is set
     * 
     * @return bool true if TimePeriodStartDate  is set
     */
    public function isSetTimePeriodStartDate ()
    {
        return ! is_null($this->fields['TimePeriodStartDate']['FieldValue']);
    }

    /**
     * Gets the value of the TimePeriodEndDate property.
     * 
     * @return string TimePeriodEndDate
     */
    public function getTimePeriodEndDate ()
    {
        return $this->fields['TimePeriodEndDate']['FieldValue'];
    }

    /**
     * Sets the value of the TimePeriodEndDate property.
     * 
     * @param string TimePeriodEndDate
     * @return this instance
     */
    public function setTimePeriodEndDate ($value)
    {
        $this->fields['TimePeriodEndDate']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the TimePeriodEndDate and returns this instance
     * 
     * @param string $value TimePeriodEndDate
     * @return OffAmazonPaymentsNotifications_Model_BillingAgreementLimits instance
     */
    public function withTimePeriodEndDate ($value)
    {
        $this->setTimePeriodEndDate($value);
        return $this;
    }

    /**
     * Checks if TimePeriodEndDate is set
     * 
     * @return bool true if TimePeriodEndDate  is set
     */
    public function isSetTimePeriodEndDate ()
    {
        return ! is_null($this->fields['TimePeriodEndDate']['FieldValue']);
    }

    /**
     * Gets the value of the CurrentRemainingBalance.
     * 
     * @return OffAmazonPaymentsNotifications_Model_Price CurrentRemainingBalance
     */
    public function getCurrentRemainingBalance ()
    {
        return $this->fields['CurrentRemainingBalance']['FieldValue'];
    }

    /**
     * Sets the value of the CurrentRemainingBalance.
     * 
     * @param OffAmazonPaymentsNotifications_Model_Price CurrentRemainingBalance
     * @return void
     */
    public function setCurrentRemainingBalance ($value)
    {
        $this->fields['CurrentRemainingBalance']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the CurrentRemainingBalance  and returns this instance
     * 
     * @param OffAmazonPaymentsNotifications_Model_Price $value CurrentRemainingBalance
     * @return OffAmazonPaymentsNotifications_Model_BillingAgreementLimits instance
     */
    public function withCurrentRemainingBalance ($value)
    {
        $this->setCurrentRemainingBalance($value);
        return $this;
    }

    /**
     * Checks if CurrentRemainingBalance  is set
     * 
     * @return bool true if CurrentRemainingBalance property is set
     */
    public function isSetCurrentRemainingBalance ()
    {
        return ! is_null($this->fields['CurrentRemainingBalance']['FieldValue']);
    }
}
?>