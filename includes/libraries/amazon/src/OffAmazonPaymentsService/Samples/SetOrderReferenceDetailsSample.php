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
 * Set Order Reference Details  Sample
 */

require_once realpath(dirname(__FILE__)) . '/.config.inc.php'; 

                                                    
/**
  * Set Order Reference Details Action Sample
  
  * @param OffAmazonPaymentsService_Interface $service instance of OffAmazonPaymentsService_Interface
  * @param mixed $request OffAmazonPaymentsService_Model_SetOrderReferenceDetails or array of parameters
  */
function printSetOrderReferenceDetailsResponse($response) 
{

                print "Service Response" . PHP_EOL;
                print "=============================================================================" . PHP_EOL;

                print "        SetOrderReferenceDetailsResponse" . PHP_EOL;
                if ($response->isSetSetOrderReferenceDetailsResult()) { 
                    print "            SetOrderReferenceDetailsResult" . PHP_EOL;
                    $setOrderReferenceDetailsResult = $response->getSetOrderReferenceDetailsResult();
                    if ($setOrderReferenceDetailsResult->isSetOrderReferenceDetails()) { 
                        print "                OrderReferenceDetails" . PHP_EOL;
                        $orderReferenceDetails = $setOrderReferenceDetailsResult->getOrderReferenceDetails();
                        if ($orderReferenceDetails->isSetAmazonOrderReferenceId()) 
                        {
                            print "                    AmazonOrderReferenceId" . PHP_EOL;
                            print "                        " . $orderReferenceDetails->getAmazonOrderReferenceId() . PHP_EOL;
                        }
                        if ($orderReferenceDetails->isSetBuyer()) { 
                            print "                    Buyer" . PHP_EOL;
                            $buyer = $orderReferenceDetails->getBuyer();
                            if ($buyer->isSetName()) 
                            {
                                print "                        Name" . PHP_EOL;
                                print "                            " . $buyer->getName() . PHP_EOL;
                            }
                            if ($buyer->isSetEmail()) 
                            {
                                print "                        Email" . PHP_EOL;
                                print "                            " . $buyer->getEmail() . PHP_EOL;
                            }
                            if ($buyer->isSetPhone()) 
                            {
                                print "                        Phone" . PHP_EOL;
                                print "                            " . $buyer->getPhone() . PHP_EOL;
                            }
                        } 
                        if ($orderReferenceDetails->isSetOrderTotal()) { 
                            print "                    OrderTotal" . PHP_EOL;
                            $orderTotal = $orderReferenceDetails->getOrderTotal();
                            if ($orderTotal->isSetCurrencyCode()) 
                            {
                                print "                        CurrencyCode" . PHP_EOL;
                                print "                            " . $orderTotal->getCurrencyCode() . PHP_EOL;
                            }
                            if ($orderTotal->isSetAmount()) 
                            {
                                print "                        Amount" . PHP_EOL;
                                print "                            " . $orderTotal->getAmount() . PHP_EOL;
                            }
                        } 
                        if ($orderReferenceDetails->isSetSellerNote()) 
                        {
                            print "                    SellerNote" . PHP_EOL;
                            print "                        " . $orderReferenceDetails->getSellerNote() . PHP_EOL;
                        }
                        if ($orderReferenceDetails->isSetDestination()) { 
                            print "                    Destination" . PHP_EOL;
                            $destination = $orderReferenceDetails->getDestination();
                            if ($destination->isSetDestinationType()) 
                            {
                                print "                        DestinationType" . PHP_EOL;
                                print "                            " . $destination->getDestinationType() . PHP_EOL;
                            }
                            if ($destination->isSetPhysicalDestination()) { 
                                print "                        PhysicalDestination" . PHP_EOL;
                                $physicalDestination = $destination->getPhysicalDestination();
                                if ($physicalDestination->isSetName()) 
                                {
                                    print "                            Name" . PHP_EOL;
                                    print "                                " . $physicalDestination->getName() . PHP_EOL;
                                }
                                if ($physicalDestination->isSetAddressLine1()) 
                                {
                                    print "                            AddressLine1" . PHP_EOL;
                                    print "                                " . $physicalDestination->getAddressLine1() . PHP_EOL;
                                }
                                if ($physicalDestination->isSetAddressLine2()) 
                                {
                                    print "                            AddressLine2" . PHP_EOL;
                                    print "                                " . $physicalDestination->getAddressLine2() . PHP_EOL;
                                }
                                if ($physicalDestination->isSetAddressLine3()) 
                                {
                                    print "                            AddressLine3" . PHP_EOL;
                                    print "                                " . $physicalDestination->getAddressLine3() . PHP_EOL;
                                }
                                if ($physicalDestination->isSetCity()) 
                                {
                                    print "                            City" . PHP_EOL;
                                    print "                                " . $physicalDestination->getCity() . PHP_EOL;
                                }
                                if ($physicalDestination->isSetCounty()) 
                                {
                                    print "                            County" . PHP_EOL;
                                    print "                                " . $physicalDestination->getCounty() . PHP_EOL;
                                }
                                if ($physicalDestination->isSetDistrict()) 
                                {
                                    print "                            District" . PHP_EOL;
                                    print "                                " . $physicalDestination->getDistrict() . PHP_EOL;
                                }
                                if ($physicalDestination->isSetStateOrRegion()) 
                                {
                                    print "                            StateOrRegion" . PHP_EOL;
                                    print "                                " . $physicalDestination->getStateOrRegion() . PHP_EOL;
                                }
                                if ($physicalDestination->isSetPostalCode()) 
                                {
                                    print "                            PostalCode" . PHP_EOL;
                                    print "                                " . $physicalDestination->getPostalCode() . PHP_EOL;
                                }
                                if ($physicalDestination->isSetCountryCode()) 
                                {
                                    print "                            CountryCode" . PHP_EOL;
                                    print "                                " . $physicalDestination->getCountryCode() . PHP_EOL;
                                }
                                if ($physicalDestination->isSetPhone()) 
                                {
                                    print "                            Phone" . PHP_EOL;
                                    print "                                " . $physicalDestination->getPhone() . PHP_EOL;
                                }
                            } 
                        }
                        if ($orderReferenceDetails->isSetBillingAddress()) {
                        	echo ("                    BillingAddress\n");
                        	$billingAddress = $orderReferenceDetails->getBillingAddress();
                        	if ($billingAddress->isSetAddressType()) {
                        		echo ("                        AddressType\n");
                        		echo ("                            " . $billingAddress->getAddressType() . "\n");
                        	}
                        	if ($billingAddress->isSetPhysicalAddress()) {
                        		echo ("                        PhysicalAddress\n");
                        		$physicalAddress = $billingAddress->getPhysicalAddress();
                        		if ($physicalAddress->isSetName()) {
                        			echo ("                            Name\n");
                        			echo ("                                " . $physicalAddress->getName() .
                        					"\n");
                        		}
                        		if ($physicalAddress->isSetAddressLine1()) {
                        			echo ("                            AddressLine1\n");
                        			echo ("                                " .
                        					$physicalAddress->getAddressLine1() . "\n");
                        		}
                        		if ($physicalAddress->isSetAddressLine2()) {
                        			echo ("                            AddressLine2\n");
                        			echo ("                                " .
                        					$physicalAddress->getAddressLine2() . "\n");
                        		}
                        		if ($physicalAddress->isSetAddressLine3()) {
                        			echo ("                            AddressLine3\n");
                        			echo ("                                " .
                        					$physicalAddress->getAddressLine3() . "\n");
                        		}
                        		if ($physicalAddress->isSetCity()) {
                        			echo ("                            City\n");
                        			echo ("                                " . $physicalAddress->getCity() .
                        					"\n");
                        		}
                        		if ($physicalAddress->isSetCounty()) {
                        			echo ("                            County\n");
                        			echo ("                                " . $physicalAddress->getCounty() .
                        					"\n");
                        		}
                        		if ($physicalAddress->isSetDistrict()) {
                        			echo ("                            District\n");
                        			echo ("                                " .
                        					$physicalAddress->getDistrict() . "\n");
                        		}
                        		if ($physicalAddress->isSetStateOrRegion()) {
                        			echo ("                            StateOrRegion\n");
                        			echo ("                                " .
                        					$physicalAddress->getStateOrRegion() . "\n");
                        		}
                        		if ($physicalAddress->isSetPostalCode()) {
                        			echo ("                            PostalCode\n");
                        			echo ("                                " .
                        					$physicalAddress->getPostalCode() . "\n");
                        		}
                        		if ($physicalAddress->isSetCountryCode()) {
                        			echo ("                            CountryCode\n");
                        			echo ("                                " .
                        					$physicalAddress->getCountryCode() . "\n");
                        		}
                        		if ($physicalAddress->isSetPhone()) {
                        			echo ("                            Phone\n");
                        			echo ("                                " . $physicalAddress->getPhone() .
                        					"\n");
                        		}
                        	}
                        } 
                        if ($orderReferenceDetails->isSetReleaseEnvironment()) 
                        {
                            print "                    ReleaseEnvironment" . PHP_EOL;
                            print "                        " . $orderReferenceDetails->getReleaseEnvironment() . PHP_EOL;
                        }
                        if ($orderReferenceDetails->isSetIdList())
                        {
                        	print "                    IdList" . PHP_EOL;
                        	$idList = $orderReferenceDetails->getIdList();
                        	$memberList  =  $idList->getmember();
                        	foreach ($memberList as $member) {
                        		print "                        member" . PHP_EOL;
                        		print "                            " . $member . PHP_EOL;;
                        	}
                        }
                        if ($orderReferenceDetails->isSetSellerOrderAttributes()) { 
                            print "                    SellerOrderAttributes" . PHP_EOL;
                            $sellerOrderAttributes = $orderReferenceDetails->getSellerOrderAttributes();
                            if ($sellerOrderAttributes->isSetSellerOrderId()) 
                            {
                                print "                        SellerOrderId" . PHP_EOL;
                                print "                            " . $sellerOrderAttributes->getSellerOrderId() . PHP_EOL;
                            }
                            if ($sellerOrderAttributes->isSetStoreName()) 
                            {
                                print "                        StoreName" . PHP_EOL;
                                print "                            " . $sellerOrderAttributes->getStoreName() . PHP_EOL;
                            }
                            if ($sellerOrderAttributes->isSetOrderItemCategories()) { 
                                print "                        OrderItemCategories" . PHP_EOL;
                                $orderItemCategories = $sellerOrderAttributes->getOrderItemCategories();
                                $orderItemCategoryList  =  $orderItemCategories->getOrderItemCategory();
                                foreach ($orderItemCategoryList as $orderItemCategory) { 
                                    print "                            OrderItemCategory" . PHP_EOL;
                                    print "                                " . $orderItemCategory;
                                }	
                            } 
                            if ($sellerOrderAttributes->isSetCustomInformation()) 
                            {
                                print "                        CustomInformation" . PHP_EOL;
                                print "                            " . $sellerOrderAttributes->getCustomInformation() . PHP_EOL;
                            }
                        } 
                        if ($orderReferenceDetails->isSetOrderReferenceStatus()) { 
                            print "                    OrderReferenceStatus" . PHP_EOL;
                            $orderReferenceStatus = $orderReferenceDetails->getOrderReferenceStatus();
                            if ($orderReferenceStatus->isSetState()) 
                            {
                                print "                        State" . PHP_EOL;
                                print "                            " . $orderReferenceStatus->getState() . PHP_EOL;
                            }
                            if ($orderReferenceStatus->isSetLastUpdateTimestamp()) 
                            {
                                print "                        LastUpdateTimestamp" . PHP_EOL;
                                print "                            " . $orderReferenceStatus->getLastUpdateTimestamp() . PHP_EOL;
                            }
                            if ($orderReferenceStatus->isSetReasonCode()) 
                            {
                                print "                        ReasonCode" . PHP_EOL;
                                print "                            " . $orderReferenceStatus->getReasonCode() . PHP_EOL;
                            }
                            if ($orderReferenceStatus->isSetReasonDescription()) 
                            {
                                print "                        ReasonDescription" . PHP_EOL;
                                print "                            " . $orderReferenceStatus->getReasonDescription() . PHP_EOL;
                            }
                        } 
                        if ($orderReferenceDetails->isSetConstraints()) { 
                            print "                    Constraints" . PHP_EOL;
                            $constraints = $orderReferenceDetails->getConstraints();
                            $constraintList = $constraints->getConstraint();
                            foreach ($constraintList as $constraint) {
                                print "                        Constraint" . PHP_EOL;
                                if ($constraint->isSetConstraintID()) 
                                {
                                    print "                            ConstraintID" . PHP_EOL;
                                    print "                                " . $constraint->getConstraintID() . PHP_EOL;
                                }
                                if ($constraint->isSetDescription()) 
                                {
                                    print "                            Description" . PHP_EOL;
                                    print "                                " . $constraint->getDescription() . PHP_EOL;
                                }
                            }
                        } 
                        if ($orderReferenceDetails->isSetCreationTimestamp()) 
                        {
                            print "                    CreationTimestamp" . PHP_EOL;
                            print "                        " . $orderReferenceDetails->getCreationTimestamp() . PHP_EOL;
                        }
                        if ($orderReferenceDetails->isSetExpirationTimestamp()) 
                        {
                            print "                    ExpirationTimestamp" . PHP_EOL;
                            print "                        " . $orderReferenceDetails->getExpirationTimestamp() . PHP_EOL;
                        }
                        if ($orderReferenceDetails->isSetParentDetails()) {
                            print "                    ParentDetails" . PHP_EOL;
                            $parentDetails = $orderReferenceDetails->getParentDetails();
                            if ($parentDetails->isSetId()) {
                                print "                        Id" . PHP_EOL;
                                print "                            " . $parentDetails->getId() . PHP_EOL;
                            }
                            if ($parentDetails->isSetType()) {
                                print "                        Type" . PHP_EOL;
                                print "                            " . $parentDetails->getType() . PHP_EOL;
                            }
                        }
                    } 
                } 
                if ($response->isSetResponseMetadata()) { 
                    print "            ResponseMetadata" . PHP_EOL;
                    $responseMetadata = $response->getResponseMetadata();
                    if ($responseMetadata->isSetRequestId()) 
                    {
                        print "                RequestId" . PHP_EOL;
                        print "                    " . $responseMetadata->getRequestId() . PHP_EOL;
                    }
                } 

   	print "            ResponseHeaderMetadata: " . 
    $response->getResponseHeaderMetadata() . PHP_EOL;
        	
   	return $response;
}
?>       