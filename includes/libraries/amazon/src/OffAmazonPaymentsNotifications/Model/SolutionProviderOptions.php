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
 *  @see OffAmazonPaymentsNotifications_Model
 */
require_once 'OffAmazonPayments/Model.php';  

    

/**
 * OffAmazonPaymentsNotifications_Model_SolutionProviderOption
 * 
 * Properties:
 * <ul>
 * 
 * <li>SolutionProviderOption: OffAmazonPaymentsNotifications_Model_SolutionProviderOption</li>
 *
 * </ul>
 */ 
class OffAmazonPaymentsNotifications_Model_SolutionProviderOptions extends OffAmazonPayments_Model
{

    /**
     * Construct new OffAmazonPaymentsNotifications_Model_SolutionProviderOption
     * 
     * @param mixed $data DOMElement or Associative Array to construct from. 
     * 
     * Valid properties:
     * <ul>
     * 
     * <li>SolutionProviderOption: OffAmazonPaymentsNotifications_Model_SolutionProviderOption</li>
     *
     * </ul>
     */
    public function __construct($data = null)
    {
        $this->fields = array (
        'SolutionProviderOption' => array('FieldValue' => array(), 'FieldType' => array('OffAmazonPaymentsNotifications_Model_SolutionProviderOption')),
        );
        parent::__construct($data);
    }

        /**
     * Gets the value of the SolutionProviderOption.
     * 
     * @return array of SolutionProviderOption
     */
    public function getSolutionProviderOption() 
    {
        return $this->fields['SolutionProviderOption']['FieldValue'];
    }

    /**
     * Sets the value of the SolutionProviderOption.
     * 
     * @param mixed SolutionProviderOption or an array of SolutionProviderOption SolutionProviderOption
     * @return this instance
     */
    public function setSolutionProviderOption($solutionProviderOptionList) 
    {
        if (!$this->_isNumericArray($solutionProviderOptionList)) {
            $solutionProviderOptionList =  array ($solutionProviderOptionList);    
        }
        $this->fields['SolutionProviderOption']['FieldValue'] = $solutionProviderOptionList;
        return $this;
    }


    /**
     * Sets single or multiple values of SolutionProviderOption list via variable number of arguments. 
     * For example, to set the list with two elements, simply pass two values as arguments to this function
     * <code>withSolutionProviderOption($SolutionProviderOption1, $SolutionProviderOption2)</code>
     * 
     * @param SolutionProviderOption  $providerCreditSummaryArgs one or more SolutionProviderOption
     * @return OffAmazonPaymentsNotifications_Model_SolutionProviderOption  instance
     */
    public function withSolutionProviderOption($solutionProviderOptionList)
    {
        foreach (func_get_args() as $solutionProviderOptionList) {
            $this->fields['SolutionProviderOption']['FieldValue'][] = $solutionProviderOptionList;
        }
        return $this;
    }   



    /**
     * Checks if SolutionProviderOption list is non-empty
     * 
     * @return bool true if SolutionProviderOption list is non-empty
     */
    public function isSetSolutionProviderOption()
    {
        return count ($this->fields['SolutionProviderOption']['FieldValue']) > 0;
    }




}