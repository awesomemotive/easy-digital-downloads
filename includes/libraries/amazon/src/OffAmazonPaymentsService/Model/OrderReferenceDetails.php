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


/**
 *  @see OffAmazonPaymentsService_Model
 */
require_once 'OffAmazonPaymentsService/Model.php';  

    

/**
 * OffAmazonPaymentsService_Model_OrderReferenceDetails
 * 
 * Properties:
 * <ul>
 * 
 * <li>AmazonOrderReferenceId: string</li>
 * <li>Buyer: OffAmazonPaymentsService_Model_Buyer</li>
 * <li>OrderTotal: OffAmazonPaymentsService_Model_OrderTotal</li>
 * <li>SellerNote: string</li>
 * <li>PlatformId: string</li>
 * <li>Destination: OffAmazonPaymentsService_Model_Destination</li>
 * <li>BillingAddress: OffAmazonPaymentsService_Model_BillingAddress</li>
 * <li>ReleaseEnvironment: string</li>
 * <li>SellerOrderAttributes: OffAmazonPaymentsService_Model_SellerOrderAttributes</li>
 * <li>OrderReferenceStatus: OffAmazonPaymentsService_Model_OrderReferenceStatus</li>
 * <li>Constraints: OffAmazonPaymentsService_Model_Constraints</li>
 * <li>CreationTimestamp: string</li>
 * <li>ExpirationTimestamp: string</li>
 * <li>IdList: OffAmazonPaymentsService_Model_IdList</li>
 * <li>ParentDetails: OffAmazonPaymentsService_Model_ParentDetails</li>
 * </ul>
 */ 
class OffAmazonPaymentsService_Model_OrderReferenceDetails extends OffAmazonPaymentsService_Model
{

    /**
     * Construct new OffAmazonPaymentsService_Model_OrderReferenceDetails
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>AmazonOrderReferenceId: string</li>
     * <li>Buyer: OffAmazonPaymentsService_Model_Buyer</li>
     * <li>OrderTotal: OffAmazonPaymentsService_Model_OrderTotal</li>
     * <li>SellerNote: string</li>
     * <li>PlatformId: string</li>
     * <li>Destination: OffAmazonPaymentsService_Model_Destination</li>
     * <li>BillingAddress: OffAmazonPaymentsService_Model_BillingAddress</li> 
     * <li>ReleaseEnvironment: string</li>
     * <li>SellerOrderAttributes: OffAmazonPaymentsService_Model_SellerOrderAttributes</li>
     * <li>OrderReferenceStatus: OffAmazonPaymentsService_Model_OrderReferenceStatus</li>
     * <li>Constraints: OffAmazonPaymentsService_Model_Constraints</li>
     * <li>CreationTimestamp: string</li>
     * <li>ExpirationTimestamp: string</li>
     * <li>IdList: OffAmazonPaymentsService_Model_IdList</li>
     * <li>ParentDetails: OffAmazonPaymentsService_Model_ParentDetails</li>
     * 
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->_fields = array (
        'AmazonOrderReferenceId' => array('FieldValue' => null, 'FieldType' => 'string'),

        'Buyer' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_Buyer'),


        'OrderTotal' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_OrderTotal'),

        'SellerNote' => array('FieldValue' => null, 'FieldType' => 'string'),
                
        'PlatformId' => array('FieldValue' => null, 'FieldType' => 'string'),

        'Destination' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_Destination'),
        
        'BillingAddress' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_BillingAddress'),
  
        'ReleaseEnvironment' => array('FieldValue' => null, 'FieldType' => 'string'),

        'SellerOrderAttributes' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_SellerOrderAttributes'),

        'OrderReferenceStatus' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_OrderReferenceStatus'),


        'Constraints' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_Constraints'),

        'CreationTimestamp' => array('FieldValue' => null, 'FieldType' => 'string'),
        'ExpirationTimestamp' => array('FieldValue' => null, 'FieldType' => 'string'),
        		
        'IdList' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_IdList'),
        
        'ParentDetails' => array('FieldValue' => null, 'FieldType' => 'OffAmazonPaymentsService_Model_ParentDetails'),
        		
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the AmazonOrderReferenceId property.
     * 
     * @return string AmazonOrderReferenceId
     */
    public function getAmazonOrderReferenceId() 
    {
        return $this->_fields['AmazonOrderReferenceId']['FieldValue'];
    }

    /**
     * Sets the value of the AmazonOrderReferenceId property.
     * 
     * @param string AmazonOrderReferenceId
     * @return this instance
     */
    public function setAmazonOrderReferenceId($value) 
    {
        $this->_fields['AmazonOrderReferenceId']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the AmazonOrderReferenceId and returns this instance
     * 
     * @param string $value AmazonOrderReferenceId
     * @return OffAmazonPaymentsService_Model_OrderReferenceDetails instance
     */
    public function withAmazonOrderReferenceId($value)
    {
        $this->setAmazonOrderReferenceId($value);
        return $this;
    }


    /**
     * Checks if AmazonOrderReferenceId is set
     * 
     * @return bool true if AmazonOrderReferenceId  is set
     */
    public function isSetAmazonOrderReferenceId()
    {
        return !is_null($this->_fields['AmazonOrderReferenceId']['FieldValue']);
    }

    /**
     * Gets the value of the Buyer.
     * 
     * @return Buyer Buyer
     */
    public function getBuyer() 
    {
        return $this->_fields['Buyer']['FieldValue'];
    }

    /**
     * Sets the value of the Buyer.
     * 
     * @param Buyer Buyer
     * @return void
     */
    public function setBuyer($value) 
    {
        $this->_fields['Buyer']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the Buyer  and returns this instance
     * 
     * @param Buyer $value Buyer
     * @return OffAmazonPaymentsService_Model_OrderReferenceDetails instance
     */
    public function withBuyer($value)
    {
        $this->setBuyer($value);
        return $this;
    }


    /**
     * Checks if Buyer  is set
     * 
     * @return bool true if Buyer property is set
     */
    public function isSetBuyer()
    {
        return !is_null($this->_fields['Buyer']['FieldValue']);

    }

    /**
     * Gets the value of the OrderTotal.
     * 
     * @return OrderTotal OrderTotal
     */
    public function getOrderTotal() 
    {
        return $this->_fields['OrderTotal']['FieldValue'];
    }

    /**
     * Sets the value of the OrderTotal.
     * 
     * @param OrderTotal OrderTotal
     * @return void
     */
    public function setOrderTotal($value) 
    {
        $this->_fields['OrderTotal']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the OrderTotal  and returns this instance
     * 
     * @param OrderTotal $value OrderTotal
     * @return OffAmazonPaymentsService_Model_OrderReferenceDetails instance
     */
    public function withOrderTotal($value)
    {
        $this->setOrderTotal($value);
        return $this;
    }


    /**
     * Checks if OrderTotal  is set
     * 
     * @return bool true if OrderTotal property is set
     */
    public function isSetOrderTotal()
    {
        return !is_null($this->_fields['OrderTotal']['FieldValue']);

    }

    /**
     * Gets the value of the SellerNote property.
     * 
     * @return string SellerNote
     */
    public function getSellerNote() 
    {
        return $this->_fields['SellerNote']['FieldValue'];
    }

    /**
     * Sets the value of the SellerNote property.
     * 
     * @param string SellerNote
     * @return this instance
     */
    public function setSellerNote($value) 
    {
        $this->_fields['SellerNote']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the SellerNote and returns this instance
     * 
     * @param string $value SellerNote
     * @return OffAmazonPaymentsService_Model_OrderReferenceDetails instance
     */
    public function withSellerNote($value)
    {
        $this->setSellerNote($value);
        return $this;
    }


    /**
     * Checks if SellerNote is set
     * 
     * @return bool true if SellerNote  is set
     */
    public function isSetSellerNote()
    {
        return !is_null($this->_fields['SellerNote']['FieldValue']);
    }
    
    /**
     * Gets the value of the PlatformId property.
     *
     * @return string PlatformId
     */
    public function getPlatformId()
    {
        return $this->_fields['PlatformId']['FieldValue'];
    }
    
    /**
     * Sets the value of the PlatformId property.
     *
     * @param string PlatformId
     * @return this instance
     */
    public function setPlatformId($value)
    {
        $this->_fields['PlatformId']['FieldValue'] = $value;
        return $this;
    }
    
    /**
     * Sets the value of the PlatformId and returns this instance
     *
     * @param string $value PlatformId
     * @return OffAmazonPaymentsService_Model_OrderReferenceDetails instance
     */
    public function withPlatformId($value)
    {
        $this->setPlatformId($value);
        return $this;
    }
    
    
    /**
     * Checks if PlatformId is set
     *
     * @return bool true if PlatformId  is set
     */
    public function isSetPlatformId()
    {
        return !is_null($this->_fields['PlatformId']['FieldValue']);
    }

    /**
     * Gets the value of the Destination.
     * 
     * @return Destination Destination
     */
    public function getDestination() 
    {
        return $this->_fields['Destination']['FieldValue'];
    }

    /**
     * Sets the value of the Destination.
     * 
     * @param Destination Destination
     * @return void
     */
    public function setDestination($value) 
    {
        $this->_fields['Destination']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the Destination  and returns this instance
     * 
     * @param Destination $value Destination
     * @return OffAmazonPaymentsService_Model_OrderReferenceDetails instance
     */
    public function withDestination($value)
    {
        $this->setDestination($value);
        return $this;
    }


    /**
     * Checks if Destination  is set
     * 
     * @return bool true if Destination property is set
     */
    public function isSetDestination()
    {
        return !is_null($this->_fields['Destination']['FieldValue']);

    }

    /**
     * Gets the value of the BillingAddress.
     *
     * @return BillingAddress BillingAddress
     */
    public function getBillingAddress()
    {
    	return $this->_fields['BillingAddress']['FieldValue'];
    }
    
    /**
     * Sets the value of the BillingAddress.
     *
     * @param BillingAddress BillingAddress
     * @return void
     */
    public function setBillingAddress($value)
    {
    	$this->_fields['BillingAddress']['FieldValue'] = $value;
    	return;
    }
    
    /**
     * Sets the value of the BillingAddress  and returns this instance
     *
     * @param BillingAddress $value BillingAddress
     * @return OffAmazonPaymentsService_Model_OrderReferenceDetails instance
     */
    public function withBillingAddress($value)
    {
    	$this->setBillingAddress($value);
    	return $this;
    }

    /**
     * Checks if BillingAddress  is set
     *
     * @return bool true if BillingAddress property is set
     */
    public function isSetBillingAddress()
    {
    	return !is_null($this->_fields['BillingAddress']['FieldValue']);
    
    }
    
    
    /**
     * Gets the value of the ReleaseEnvironment property.
     * 
     * @return string ReleaseEnvironment
     */
    public function getReleaseEnvironment() 
    {
        return $this->_fields['ReleaseEnvironment']['FieldValue'];
    }

    /**
     * Sets the value of the ReleaseEnvironment property.
     * 
     * @param string ReleaseEnvironment
     * @return this instance
     */
    public function setReleaseEnvironment($value) 
    {
        $this->_fields['ReleaseEnvironment']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the ReleaseEnvironment and returns this instance
     * 
     * @param string $value ReleaseEnvironment
     * @return OffAmazonPaymentsService_Model_OrderReferenceDetails instance
     */
    public function withReleaseEnvironment($value)
    {
        $this->setReleaseEnvironment($value);
        return $this;
    }


    /**
     * Checks if ReleaseEnvironment is set
     * 
     * @return bool true if ReleaseEnvironment  is set
     */
    public function isSetReleaseEnvironment()
    {
        return !is_null($this->_fields['ReleaseEnvironment']['FieldValue']);
    }

    /**
     * Gets the value of the SellerOrderAttributes.
     * 
     * @return SellerOrderAttributes SellerOrderAttributes
     */
    public function getSellerOrderAttributes() 
    {
        return $this->_fields['SellerOrderAttributes']['FieldValue'];
    }

    /**
     * Sets the value of the SellerOrderAttributes.
     * 
     * @param SellerOrderAttributes SellerOrderAttributes
     * @return void
     */
    public function setSellerOrderAttributes($value) 
    {
        $this->_fields['SellerOrderAttributes']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the SellerOrderAttributes  and returns this instance
     * 
     * @param SellerOrderAttributes $value SellerOrderAttributes
     * @return OffAmazonPaymentsService_Model_OrderReferenceDetails instance
     */
    public function withSellerOrderAttributes($value)
    {
        $this->setSellerOrderAttributes($value);
        return $this;
    }


    /**
     * Checks if SellerOrderAttributes  is set
     * 
     * @return bool true if SellerOrderAttributes property is set
     */
    public function isSetSellerOrderAttributes()
    {
        return !is_null($this->_fields['SellerOrderAttributes']['FieldValue']);

    }
    
    /**
     * Gets the value of the OrderReferenceStatus.
     * 
     * @return OrderReferenceStatus OrderReferenceStatus
     */
    public function getOrderReferenceStatus() 
    {
        return $this->_fields['OrderReferenceStatus']['FieldValue'];
    }

    /**
     * Sets the value of the OrderReferenceStatus.
     * 
     * @param OrderReferenceStatus OrderReferenceStatus
     * @return void
     */
    public function setOrderReferenceStatus($value) 
    {
        $this->_fields['OrderReferenceStatus']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the OrderReferenceStatus  and returns this instance
     * 
     * @param OrderReferenceStatus $value OrderReferenceStatus
     * @return OffAmazonPaymentsService_Model_OrderReferenceDetails instance
     */
    public function withOrderReferenceStatus($value)
    {
        $this->setOrderReferenceStatus($value);
        return $this;
    }


    /**
     * Checks if OrderReferenceStatus  is set
     * 
     * @return bool true if OrderReferenceStatus property is set
     */
    public function isSetOrderReferenceStatus()
    {
        return !is_null($this->_fields['OrderReferenceStatus']['FieldValue']);

    }

    /**
     * Gets the value of the Constraints.
     * 
     * @return Constraints Constraints
     */
    public function getConstraints() 
    {
        return $this->_fields['Constraints']['FieldValue'];
    }

    /**
     * Sets the value of the Constraints.
     * 
     * @param Constraints Constraints
     * @return void
     */
    public function setConstraints($value) 
    {
        $this->_fields['Constraints']['FieldValue'] = $value;
        return;
    }

    /**
     * Sets the value of the Constraints  and returns this instance
     * 
     * @param Constraints $value Constraints
     * @return OffAmazonPaymentsService_Model_OrderReferenceDetails instance
     */
    public function withConstraints($value)
    {
        $this->setConstraints($value);
        return $this;
    }


    /**
     * Checks if Constraints  is set
     * 
     * @return bool true if Constraints property is set
     */
    public function isSetConstraints()
    {
        return !is_null($this->_fields['Constraints']['FieldValue']);

    }

    /**
     * Gets the value of the CreationTimestamp property.
     * 
     * @return string CreationTimestamp
     */
    public function getCreationTimestamp() 
    {
        return $this->_fields['CreationTimestamp']['FieldValue'];
    }

    /**
     * Sets the value of the CreationTimestamp property.
     * 
     * @param string CreationTimestamp
     * @return this instance
     */
    public function setCreationTimestamp($value) 
    {
        $this->_fields['CreationTimestamp']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the CreationTimestamp and returns this instance
     * 
     * @param string $value CreationTimestamp
     * @return OffAmazonPaymentsService_Model_OrderReferenceDetails instance
     */
    public function withCreationTimestamp($value)
    {
        $this->setCreationTimestamp($value);
        return $this;
    }


    /**
     * Checks if CreationTimestamp is set
     * 
     * @return bool true if CreationTimestamp  is set
     */
    public function isSetCreationTimestamp()
    {
        return !is_null($this->_fields['CreationTimestamp']['FieldValue']);
    }

    /**
     * Gets the value of the ExpirationTimestamp property.
     * 
     * @return string ExpirationTimestamp
     */
    public function getExpirationTimestamp() 
    {
        return $this->_fields['ExpirationTimestamp']['FieldValue'];
    }

    /**
     * Sets the value of the ExpirationTimestamp property.
     * 
     * @param string ExpirationTimestamp
     * @return this instance
     */
    public function setExpirationTimestamp($value) 
    {
        $this->_fields['ExpirationTimestamp']['FieldValue'] = $value;
        return $this;
    }

    /**
     * Sets the value of the ExpirationTimestamp and returns this instance
     * 
     * @param string $value ExpirationTimestamp
     * @return OffAmazonPaymentsService_Model_OrderReferenceDetails instance
     */
    public function withExpirationTimestamp($value)
    {
        $this->setExpirationTimestamp($value);
        return $this;
    }


    /**
     * Checks if ExpirationTimestamp is set
     * 
     * @return bool true if ExpirationTimestamp  is set
     */
    public function isSetExpirationTimestamp()
    {
        return !is_null($this->_fields['ExpirationTimestamp']['FieldValue']);
    }


    /**
     * Gets the value of the IdList.
     *
     * @return IdList IdList
     */
    public function getIdList()
    {
    	return $this->_fields['IdList']['FieldValue'];
    }
    
    /**
     * Sets the value of the IdList.
     *
     * @param IdList IdList
     * @return void
     */
    public function setIdList($value)
    {
    	$this->_fields['IdList']['FieldValue'] = $value;
    	return;
    }
    
    /**
     * Sets the value of the IdList  and returns this instance
     *
     * @param IdList $value IdList
     * @return OffAmazonPaymentsService_Model_OrderReferenceDetails instance
     */
    public function withIdList($value)
    {
    	$this->setIdList($value);
    	return $this;
    }
    
    
    /**
     * Checks if IdList  is set
     *
     * @return bool true if IdList property is set
     */
    public function isSetIdList()
    {
    	return !is_null($this->_fields['IdList']['FieldValue']);
    
    }
    
    /**
     * Gets the value of the ParentDetails.
     *
     * @return OffAmazonPaymentsService_Model_ParentDetails ParentDetails
     */
    public function getParentDetails()
    {
        return $this->_fields['ParentDetails']['FieldValue'];
    }
    
    /**
     * Sets the value of the ParentDetails.
     *
     * @param OffAmazonPaymentsService_Model_ParentDetails ParentDetails
     * @return void
     */
    public function setParentDetails($value)
    {
        $this->_fields['ParentDetails']['FieldValue'] = $value;
        return;
    }
    
    /**
     * Sets the value of the ParentDetails  and returns this instance
     *
     * @param ParentDetails $value ParentDetails
     * @return OffAmazonPaymentsService_Model_OrderReferenceDetails instance
     */
    public function withParentDetails($value)
    {
        $this->setParentDetails($value);
        return $this;
    }
    
    
    /**
     * Checks if ParentDetails is set
     *
     * @return bool true if ParentDetails property is set
     */
    public function isSetParentDetails()
    {
        return !is_null($this->_fields['ParentDetails']['FieldValue']);
    
    }

}
?>