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
require_once 'OffAmazonPaymentsService/Model.php';

/**
 * OffAmazonPaymentsService_Model_BillingAgreementDetails
 * 
 * Properties:
 * <ul>
 * 
 * <li>AmazonBillingAgreementId: string</li>
 * <li>BillingAgreementLimits: OffAmazonPaymentsService_Model_BillingAgreementLimits</li>
 * <li>Buyer: OffAmazonPaymentsService_Model_Buyer</li>
 * <li>SellerNote: string</li>
 * <li>PlatformId: string</li>
 * <li>Destination: OffAmazonPaymentsService_Model_Destination</li>
 * <li>BillingAddress: OffAmazonPaymentsService_Model_BillingAddress</li>
 * <li>ReleaseEnvironment: string</li>
 * <li>SellerBillingAgreementAttributes: OffAmazonPaymentsService_Model_SellerBillingAgreementAttributes</li>
 * <li>BillingAgreementStatus: OffAmazonPaymentsService_Model_BillingAgreementStatus</li>
 * <li>Constraints: OffAmazonPaymentsService_Model_Constraints</li>
 * <li>CreationTimestamp: string</li>
 * <li>ExpirationTimestamp: string</li>
 * <li>BillingAgreementConsent: bool</li>
 *
 * </ul>
 */
class OffAmazonPaymentsService_Model_BillingAgreementDetails extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_BillingAgreementDetails
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>AmazonBillingAgreementId: string</li>
     * <li>BillingAgreementLimits: OffAmazonPaymentsService_Model_BillingAgreementLimits</li>
     * <li>Buyer: OffAmazonPaymentsService_Model_Buyer</li>
     * <li>SellerNote: string</li>
     * <li>PlatformId: string</li>
     * <li>Destination: OffAmazonPaymentsService_Model_Destination</li>
     * <li>BillingAddress: OffAmazonPaymentsService_Model_BillingAddress</li>
     * <li>ReleaseEnvironment: string</li>
     * <li>SellerBillingAgreementAttributes: OffAmazonPaymentsService_Model_SellerBillingAgreementAttributes</li>
     * <li>BillingAgreementStatus: OffAmazonPaymentsService_Model_BillingAgreementStatus</li>
     * <li>Constraints: OffAmazonPaymentsService_Model_Constraints</li>
     * <li>CreationTimestamp: string</li>
     * <li>ExpirationTimestamp: string</li>
     * <li>BillingAgreementConsent: bool</li>
     *
     * </ul>
     */
    public function __construct ($data = null)
    {
        $this->_fields = array(
            'AmazonBillingAgreementId' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            
            'BillingAgreementLimits' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsService_Model_BillingAgreementLimits'
            ),
            
            'Buyer' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsService_Model_Buyer'
            ),
            
            'SellerNote' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'PlatformId' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            
            'Destination' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsService_Model_Destination'
            ),
           
            'BillingAddress' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsService_Model_BillingAddress'
            ),
 
            'ReleaseEnvironment' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            
            'SellerBillingAgreementAttributes' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsService_Model_SellerBillingAgreementAttributes'
            ),
            
            'BillingAgreementStatus' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsService_Model_BillingAgreementStatus'
            ),
            
            'Constraints' => array(
                'FieldValue' => null,
                'FieldType' => 'OffAmazonPaymentsService_Model_Constraints'
            ),
            
            'CreationTimestamp' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'ExpirationTimestamp' => array(
                'FieldValue' => null,
                'FieldType' => 'string'
            ),
            'BillingAgreementConsent' => array(
                'FieldValue' => null,
                'FieldType' => 'bool'
            )
        );
        parent::__construct($data);
    }

    /**
     * Gets the value of the AmazonBillingAgreementId property.
     * 
     * @return string AmazonBillingAgreementId
     */
    public function getAmazonBillingAgreementId ()
    {
        return $this->_fields['AmazonBillingAgreementId']['FieldValue'];
    }

    /**
     * Sets the value of the AmazonBillingAgreementId property.
     * 
     * @param string AmazonBillingAgreementId
     * @return this instance
     */
    public function setAmazonBillingAgreementId ($value)
    {
        $this->_fields['AmazonBillingAgreementId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the AmazonBillingAgreementId and returns this instance
     * 
     * @param string $value AmazonBillingAgreementId
     * @return OffAmazonPaymentsService_Model_BillingAgreementDetails instance
     */
    public function withAmazonBillingAgreementId ($value)
    {
        $this->setAmazonBillingAgreementId($value);
        return $this;
    }

    /**
     * Checks if AmazonBillingAgreementId is set
     * 
     * @return bool true if AmazonBillingAgreementId  is set
     */
    public function isSetAmazonBillingAgreementId ()
    {
        return ! is_null($this->_fields['AmazonBillingAgreementId']['FieldValue']);
    }

    /**
     * Gets the value of the BillingAgreementLimits.
     * 
     * @return BillingAgreementLimits BillingAgreementLimits
     */
    public function getBillingAgreementLimits ()
    {
        return $this->_fields['BillingAgreementLimits']['FieldValue'];
    }

    /**
     * Sets the value of the BillingAgreementLimits.
     * 
     * @param BillingAgreementLimits BillingAgreementLimits
     * @return void
     */
    public function setBillingAgreementLimits ($value)
    {
        $this->_fields['BillingAgreementLimits']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the BillingAgreementLimits  and returns this instance
     * 
     * @param BillingAgreementLimits $value BillingAgreementLimits
     * @return OffAmazonPaymentsService_Model_BillingAgreementDetails instance
     */
    public function withBillingAgreementLimits ($value)
    {
        $this->setBillingAgreementLimits($value);
        return $this;
    }

    /**
     * Checks if BillingAgreementLimits  is set
     * 
     * @return bool true if BillingAgreementLimits property is set
     */
    public function isSetBillingAgreementLimits ()
    {
        return ! is_null($this->_fields['BillingAgreementLimits']['FieldValue']);
    }

    /**
     * Gets the value of the Buyer.
     * 
     * @return Buyer Buyer
     */
    public function getBuyer ()
    {
        return $this->_fields['Buyer']['FieldValue'];
    }

    /**
     * Sets the value of the Buyer.
     * 
     * @param Buyer Buyer
     * @return void
     */
    public function setBuyer ($value)
    {
        $this->_fields['Buyer']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the Buyer  and returns this instance
     * 
     * @param Buyer $value Buyer
     * @return OffAmazonPaymentsService_Model_BillingAgreementDetails instance
     */
    public function withBuyer ($value)
    {
        $this->setBuyer($value);
        return $this;
    }

    /**
     * Checks if Buyer  is set
     * 
     * @return bool true if Buyer property is set
     */
    public function isSetBuyer ()
    {
        return ! is_null($this->_fields['Buyer']['FieldValue']);
    }

    /**
     * Gets the value of the SellerNote property.
     * 
     * @return string SellerNote
     */
    public function getSellerNote ()
    {
        return $this->_fields['SellerNote']['FieldValue'];
    }

    /**
     * Sets the value of the SellerNote property.
     * 
     * @param string SellerNote
     * @return this instance
     */
    public function setSellerNote ($value)
    {
        $this->_fields['SellerNote']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the SellerNote and returns this instance
     * 
     * @param string $value SellerNote
     * @return OffAmazonPaymentsService_Model_BillingAgreementDetails instance
     */
    public function withSellerNote ($value)
    {
        $this->setSellerNote($value);
        return $this;
    }

    /**
     * Checks if SellerNote is set
     * 
     * @return bool true if SellerNote  is set
     */
    public function isSetSellerNote ()
    {
        return ! is_null($this->_fields['SellerNote']['FieldValue']);
    }

    /**
     * Gets the value of the PlatformId property.
     * 
     * @return string PlatformId
     */
    public function getPlatformId ()
    {
        return $this->_fields['PlatformId']['FieldValue'];
    }

    /**
     * Sets the value of the PlatformId property.
     * 
     * @param string PlatformId
     * @return this instance
     */
    public function setPlatformId ($value)
    {
        $this->_fields['PlatformId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the PlatformId and returns this instance
     * 
     * @param string $value PlatformId
     * @return OffAmazonPaymentsService_Model_BillingAgreementDetails instance
     */
    public function withPlatformId ($value)
    {
        $this->setPlatformId($value);
        return $this;
    }

    /**
     * Checks if PlatformId is set
     * 
     * @return bool true if PlatformId  is set
     */
    public function isSetPlatformId ()
    {
        return ! is_null($this->_fields['PlatformId']['FieldValue']);
    }

    /**
     * Gets the value of the Destination.
     * 
     * @return Destination Destination
     */
    public function getDestination ()
    {
        return $this->_fields['Destination']['FieldValue'];
    }

    /**
     * Sets the value of the Destination.
     * 
     * @param Destination Destination
     * @return void
     */
    public function setDestination ($value)
    {
        $this->_fields['Destination']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the Destination  and returns this instance
     * 
     * @param Destination $value Destination
     * @return OffAmazonPaymentsService_Model_BillingAgreementDetails instance
     */
    public function withDestination ($value)
    {
        $this->setDestination($value);
        return $this;
    }

    /**
     * Checks if Destination  is set
     * 
     * @return bool true if Destination property is set
     */
    public function isSetDestination ()
    {
        return ! is_null($this->_fields['Destination']['FieldValue']);
    }

    /**
     * Gets the value of the BillingAddress.
     * 
     * @return BillingAddress BillingAddress
     */
    public function getBillingAddress ()
    {
        return $this->_fields['BillingAddress']['FieldValue'];
    }

    /**
     * Sets the value of the BillingAddress.
     * 
     * @param BillingAddress BillingAddress
     * @return void
     */
    public function setBillingAddress ($value)
    {
        $this->_fields['BillingAddress']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the BillingAddress  and returns this instance
     * 
     * @param BillingAddress $value BillingAddress
     * @return OffAmazonPaymentsService_Model_BillingAgreementDetails instance
     */
    public function withBillingAddress ($value)
    {
        $this->setBillingAddress($value);
        return $this;
    }

    /**
     * Checks if BillingAddress  is set
     * 
     * @return bool true if BillingAddress property is set
     */
    public function isSetBillingAddress ()
    {
        return ! is_null($this->_fields['BillingAddress']['FieldValue']);
    }

    /**
     * Gets the value of the ReleaseEnvironment property.
     * 
     * @return string ReleaseEnvironment
     */
    public function getReleaseEnvironment ()
    {
        return $this->_fields['ReleaseEnvironment']['FieldValue'];
    }

    /**
     * Sets the value of the ReleaseEnvironment property.
     * 
     * @param string ReleaseEnvironment
     * @return this instance
     */
    public function setReleaseEnvironment ($value)
    {
        $this->_fields['ReleaseEnvironment']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the ReleaseEnvironment and returns this instance
     * 
     * @param string $value ReleaseEnvironment
     * @return OffAmazonPaymentsService_Model_BillingAgreementDetails instance
     */
    public function withReleaseEnvironment ($value)
    {
        $this->setReleaseEnvironment($value);
        return $this;
    }

    /**
     * Checks if ReleaseEnvironment is set
     * 
     * @return bool true if ReleaseEnvironment  is set
     */
    public function isSetReleaseEnvironment ()
    {
        return ! is_null($this->_fields['ReleaseEnvironment']['FieldValue']);
    }

    /**
     * Gets the value of the SellerBillingAgreementAttributes.
     * 
     * @return SellerBillingAgreementAttributes SellerBillingAgreementAttributes
     */
    public function getSellerBillingAgreementAttributes ()
    {
        return $this->_fields['SellerBillingAgreementAttributes']['FieldValue'];
    }

    /**
     * Sets the value of the SellerBillingAgreementAttributes.
     * 
     * @param SellerBillingAgreementAttributes SellerBillingAgreementAttributes
     * @return void
     */
    public function setSellerBillingAgreementAttributes ($value)
    {
        $this->_fields['SellerBillingAgreementAttributes']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the SellerBillingAgreementAttributes  and returns this instance
     * 
     * @param SellerBillingAgreementAttributes $value SellerBillingAgreementAttributes
     * @return OffAmazonPaymentsService_Model_BillingAgreementDetails instance
     */
    public function withSellerBillingAgreementAttributes ($value)
    {
        $this->setSellerBillingAgreementAttributes($value);
        return $this;
    }

    /**
     * Checks if SellerBillingAgreementAttributes  is set
     * 
     * @return bool true if SellerBillingAgreementAttributes property is set
     */
    public function isSetSellerBillingAgreementAttributes ()
    {
        return ! is_null($this->_fields['SellerBillingAgreementAttributes']['FieldValue']);
    }

    /**
     * Gets the value of the BillingAgreementStatus.
     * 
     * @return BillingAgreementStatus BillingAgreementStatus
     */
    public function getBillingAgreementStatus ()
    {
        return $this->_fields['BillingAgreementStatus']['FieldValue'];
    }

    /**
     * Sets the value of the BillingAgreementStatus.
     * 
     * @param BillingAgreementStatus BillingAgreementStatus
     * @return void
     */
    public function setBillingAgreementStatus ($value)
    {
        $this->_fields['BillingAgreementStatus']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the BillingAgreementStatus  and returns this instance
     * 
     * @param BillingAgreementStatus $value BillingAgreementStatus
     * @return OffAmazonPaymentsService_Model_BillingAgreementDetails instance
     */
    public function withBillingAgreementStatus ($value)
    {
        $this->setBillingAgreementStatus($value);
        return $this;
    }

    /**
     * Checks if BillingAgreementStatus  is set
     * 
     * @return bool true if BillingAgreementStatus property is set
     */
    public function isSetBillingAgreementStatus ()
    {
        return ! is_null($this->_fields['BillingAgreementStatus']['FieldValue']);
    }

    /**
     * Gets the value of the Constraints.
     * 
     * @return Constraints Constraints
     */
    public function getConstraints ()
    {
        return $this->_fields['Constraints']['FieldValue'];
    }

    /**
     * Sets the value of the Constraints.
     * 
     * @param Constraints Constraints
     * @return void
     */
    public function setConstraints ($value)
    {
        $this->_fields['Constraints']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the Constraints  and returns this instance
     * 
     * @param Constraints $value Constraints
     * @return OffAmazonPaymentsService_Model_BillingAgreementDetails instance
     */
    public function withConstraints ($value)
    {
        $this->setConstraints($value);
        return $this;
    }

    /**
     * Checks if Constraints  is set
     * 
     * @return bool true if Constraints property is set
     */
    public function isSetConstraints ()
    {
        return ! is_null($this->_fields['Constraints']['FieldValue']);
    }

    /**
     * Gets the value of the CreationTimestamp property.
     * 
     * @return string CreationTimestamp
     */
    public function getCreationTimestamp ()
    {
        return $this->_fields['CreationTimestamp']['FieldValue'];
    }

    /**
     * Sets the value of the CreationTimestamp property.
     * 
     * @param string CreationTimestamp
     * @return this instance
     */
    public function setCreationTimestamp ($value)
    {
        $this->_fields['CreationTimestamp']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the CreationTimestamp and returns this instance
     * 
     * @param string $value CreationTimestamp
     * @return OffAmazonPaymentsService_Model_BillingAgreementDetails instance
     */
    public function withCreationTimestamp ($value)
    {
        $this->setCreationTimestamp($value);
        return $this;
    }

    /**
     * Checks if CreationTimestamp is set
     * 
     * @return bool true if CreationTimestamp  is set
     */
    public function isSetCreationTimestamp ()
    {
        return ! is_null($this->_fields['CreationTimestamp']['FieldValue']);
    }

    /**
     * Gets the value of the ExpirationTimestamp property.
     * 
     * @return string ExpirationTimestamp
     */
    public function getExpirationTimestamp ()
    {
        return $this->_fields['ExpirationTimestamp']['FieldValue'];
    }

    /**
     * Sets the value of the ExpirationTimestamp property.
     * 
     * @param string ExpirationTimestamp
     * @return this instance
     */
    public function setExpirationTimestamp ($value)
    {
        $this->_fields['ExpirationTimestamp']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the ExpirationTimestamp and returns this instance
     * 
     * @param string $value ExpirationTimestamp
     * @return OffAmazonPaymentsService_Model_BillingAgreementDetails instance
     */
    public function withExpirationTimestamp ($value)
    {
        $this->setExpirationTimestamp($value);
        return $this;
    }

    /**
     * Checks if ExpirationTimestamp is set
     * 
     * @return bool true if ExpirationTimestamp  is set
     */
    public function isSetExpirationTimestamp ()
    {
        return ! is_null($this->_fields['ExpirationTimestamp']['FieldValue']);
    }

    /**
     * Gets the value of the BillingAgreementConsent property.
     * 
     * @return bool BillingAgreementConsent
     */
    public function getBillingAgreementConsent ()
    {
        return $this->_fields['BillingAgreementConsent']['FieldValue'];
    }

    /**
     * Sets the value of the BillingAgreementConsent property.
     * 
     * @param bool BillingAgreementConsent
     * @return this instance
     */
    public function setBillingAgreementConsent ($value)
    {
        $this->_fields['BillingAgreementConsent']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the BillingAgreementConsent and returns this instance
     * 
     * @param bool $value BillingAgreementConsent
     * @return OffAmazonPaymentsService_Model_BillingAgreementDetails instance
     */
    public function withBillingAgreementConsent ($value)
    {
        $this->setBillingAgreementConsent($value);
        return $this;
    }

    /**
     * Checks if BillingAgreementConsent is set
     * 
     * @return bool true if BillingAgreementConsent  is set
     */
    public function isSetBillingAgreementConsent ()
    {
        return ! is_null($this->_fields['BillingAgreementConsent']['FieldValue']);
    }
}
?>
