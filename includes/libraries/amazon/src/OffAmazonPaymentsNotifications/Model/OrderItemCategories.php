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
 * OffAmazonPaymentsNotifications_Model_OrderItemCategories
 * 
 * Properties:
 * <ul>
 * 
 * <li>OrderItemCategory: string</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsNotifications_Model_OrderItemCategories extends OffAmazonPayments_Model
{

    /**
     * Construct new OffAmazonPaymentsNotifications_Model_OrderItemCategories
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>OrderItemCategory: string</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->fields = array (
        'OrderItemCategory' => 
                array('FieldValue' => array(), 'FieldType' => array('string')),
        );
        parent::__construct($data);
    }

    /**
     * Gets the value of the OrderItemCategory .
     * 
     * @return array of string OrderItemCategory
     */
    public function getOrderItemCategory() 
    {
        return $this->fields['OrderItemCategory']['FieldValue'];
    }

    /**
     * Sets the value of the OrderItemCategory.
     * 
     * @param string|array $orderItemCategory string or an array
     *                                        of string OrderItemCategory
     * 
     * @return this instance
     */
    public function setOrderItemCategory($orderItemCategory) 
    {
        if (!$this->_isNumericArray($orderItemCategory)) {
            $orderItemCategory =  array ($orderItemCategory);    
        }
        $this->fields['OrderItemCategory']['FieldValue'] = $orderItemCategory;
        return $this;
    }
  

    /**
     * Sets single or multiple values of OrderItemCategory 
     * list via variable number of arguments. 
     * For example, to set the list with two elements, 
     * simply pass two values as arguments to this function
     * <code>withOrderItemCategory($orderItemCategory1, $orderItemCategory2)</code>
     * 
     * @param string $stringArgs one or more OrderItemCategory
     * 
     * @return OffAmazonPaymentsNotifications_Model_OrderItemCategories  instance
     */
    public function withOrderItemCategory($stringArgs)
    {
        foreach (func_get_args() as $orderItemCategory) {
            $this->fields['OrderItemCategory']['FieldValue'][] = $orderItemCategory;
        }
        return $this;
    }  
      

    /**
     * Checks if OrderItemCategory list is non-empty
     * 
     * @return bool true if OrderItemCategory list is non-empty
     */
    public function isSetOrderItemCategory()
    {
        return count($this->fields['OrderItemCategory']['FieldValue']) > 0;
    }
}
?>