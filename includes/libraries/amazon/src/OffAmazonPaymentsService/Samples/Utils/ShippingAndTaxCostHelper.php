<?php

/* *****************************************************************************
 * Copyright 2013 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 * Licensed under the Apache License, Version 2.0 (the "License");
 *
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at:
 * http://aws.amazon.com/apache2.0
 * This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
 * CONDITIONS OF ANY KIND, either express or implied. See the License for the
 * specific language governing permissions and limitations under the License.
 * *****************************************************************************
 */
require_once 'CountryRate.php';

class ShippingAndTaxCostHelper
{

    private $_countryCostsMatrix;

    public function __construct ()
    {
        /*
         * Define overnight, 2 day and 5 day shipping rates for the countries in
         * the example
         */
        $baseOvernightShipping = 50.00;
        $baseTwoDayShipping = 40.00;
        $baseFiveDayShipping = 30.00;
        
        $usShippingRates = array($baseOvernightShipping,$baseTwoDayShipping,$baseFiveDayShipping
        );
        $caShippingRates = array($baseOvernightShipping + 10.00,$baseTwoDayShipping + 10.00,
                $baseFiveDayShipping + 10.00
        );
        $auShippingRates = array($baseOvernightShipping * 2,$baseTwoDayShipping * 2,
                $baseFiveDayShipping * 2
        );
        $otherShippingRates = array($baseOvernightShipping + 30.00,$baseTwoDayShipping + 30.00,
                $baseFiveDayShipping + 30.00
        );
        
        /*
         * Define tax rates for different juristrictions in our example.
         */
        $usTaxRates = array('WA' => 1.2,'IL' => 1.42,'NY' => 1.2
        );
        $caTaxRates = array('BC' => 1.1,'ON' => 1.11,'QC' => 1.5
        );
        $auTaxRates = array('NSW' => 1.1,'VIC' => 1.1,'QLD' => 1.1
        );
        $otherTaxRates = array();
        
        /*
         * Setup sample shipping and tax rates for our scenario
         */
        $this->_countryCostsMatrix = array('US' => new CountryRates($usShippingRates, $usTaxRates),
                'CA' => new CountryRates($caShippingRates, $caTaxRates),
                'AU' => new CountryRates($auShippingRates, $auTaxRates),
                'Unknown' => new CountryRates($otherShippingRates, $otherTaxRates)
        );
    }

    public function calculateTotalAmount ($BillingAgreementDetails, $paymentAmountPreTaxAndShipping, 
            $shippingType)
    {
        $paymentTotal = $paymentAmountPreTaxAndShipping;
        if ($BillingAgreementDetails->getDestination() == null) {
        	throw Exception(
        			"Error - expected to find destination in billing agreement details response" .
        			", check that correct versions of the widgets have been" .
        			" used to create the Amazon billing agreement Id");
        }
        $physicalAddress = $BillingAgreementDetails->getDestination()->getPhysicalDestination();
	
        /* *********************************************************************
         * Add shipping costs to the total order, based on the country of the
         * destination address
         * ******************************************************************
         */
        if (array_key_exists($physicalAddress->getCountryCode(), $this->_countryCostsMatrix)) {
            $paymentTotal = $paymentTotal +
                     $this->_countryCostsMatrix[$physicalAddress->getCountryCode()]->shippingRates[$shippingType];
            
            /* *********************************************************************
             * Add tax to the order if the the state or region exists in our
             * tax rate map
             * ********************************************************************
             */
            if (array_key_exists($physicalAddress->getStateOrRegion(), 
                    $this->_countryCostsMatrix[$physicalAddress->getCountryCode()]->taxRates)) {
                $paymentTotal = $paymentTotal *
                         $this->_countryCostsMatrix[$physicalAddress->getCountryCode()]->taxRates[$physicalAddress->getStateOrRegion()];
            }
        } else {
            $paymentTotal = $paymentTotal +
                     $_this->countryCostsMatrix["Unknown"]->shippingRates[$shippingType];
        }
        
        return $paymentTotal;
    }
}
?>
