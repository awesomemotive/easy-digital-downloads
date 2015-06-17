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
require_once realpath(dirname(__FILE__)) . '/.config.inc.php';

/**
 * Print the CreateOrderReferenceForId response to the console
 *
 * @param OffAmazonPaymentsService_Model_CreateOrderReferenceForIdResponse $response
 *            response object
 *            
 * @return void
 */
function printCreateOrderReferenceForIdResponse ($response)
{
    echo ("Service Response\n");
    echo ("=============================================================================\n");
    
    echo ("        CreateOrderReferenceForIdResponse\n");
    if ($response->isSetCreateOrderReferenceForIdResult()) {
        echo ("            CreateOrderReferenceForIdResult\n");
        $createOrderReferenceForIdResult = $response->getCreateOrderReferenceForIdResult();
        if ($createOrderReferenceForIdResult->isSetOrderReferenceDetails()) {
            echo ("                OrderReferenceDetails\n");
            $orderReferenceDetails = $createOrderReferenceForIdResult->getOrderReferenceDetails();
            if ($orderReferenceDetails->isSetAmazonOrderReferenceId()) {
                echo ("                    AmazonOrderReferenceId\n");
                echo ("                        " .
                         $orderReferenceDetails->getAmazonOrderReferenceId() . "\n");
            }
            if ($orderReferenceDetails->isSetBuyer()) {
                echo ("                    Buyer\n");
                $buyer = $orderReferenceDetails->getBuyer();
                if ($buyer->isSetName()) {
                    echo ("                        Name\n");
                    echo ("                            " . $buyer->getName() . "\n");
                }
                if ($buyer->isSetEmail()) {
                    echo ("                        Email\n");
                    echo ("                            " . $buyer->getEmail() . "\n");
                }
                if ($buyer->isSetPhone()) {
                    echo ("                        Phone\n");
                    echo ("                            " . $buyer->getPhone() . "\n");
                }
            }
            if ($orderReferenceDetails->isSetOrderTotal()) {
                echo ("                    OrderTotal\n");
                $orderTotal = $orderReferenceDetails->getOrderTotal();
                if ($orderTotal->isSetCurrencyCode()) {
                    echo ("                        CurrencyCode\n");
                    echo ("                            " . $orderTotal->getCurrencyCode() . "\n");
                }
                if ($orderTotal->isSetAmount()) {
                    echo ("                        Amount\n");
                    echo ("                            " . $orderTotal->getAmount() . "\n");
                }
            }
            if ($orderReferenceDetails->isSetSellerNote()) {
                echo ("                    SellerNote\n");
                echo ("                        " . $orderReferenceDetails->getSellerNote() . "\n");
            }
            if ($orderReferenceDetails->isSetPlatformId()) {
                echo ("                    PlatformId\n");
                echo ("                        " . $orderReferenceDetails->getPlatformId() . "\n");
            }
            if ($orderReferenceDetails->isSetDestination()) {
                echo ("                    Destination\n");
                $destination = $orderReferenceDetails->getDestination();
                if ($destination->isSetDestinationType()) {
                    echo ("                        DestinationType\n");
                    echo ("                            " . $destination->getDestinationType() . "\n");
                }
                if ($destination->isSetPhysicalDestination()) {
                    echo ("                        PhysicalDestination\n");
                    $physicalDestination = $destination->getPhysicalDestination();
                    if ($physicalDestination->isSetName()) {
                        echo ("                            Name\n");
                        echo ("                                " . $physicalDestination->getName() .
                                 "\n");
                    }
                    if ($physicalDestination->isSetAddressLine1()) {
                        echo ("                            AddressLine1\n");
                        echo ("                                " .
                                 $physicalDestination->getAddressLine1() . "\n");
                    }
                    if ($physicalDestination->isSetAddressLine2()) {
                        echo ("                            AddressLine2\n");
                        echo ("                                " .
                                 $physicalDestination->getAddressLine2() . "\n");
                    }
                    if ($physicalDestination->isSetAddressLine3()) {
                        echo ("                            AddressLine3\n");
                        echo ("                                " .
                                 $physicalDestination->getAddressLine3() . "\n");
                    }
                    if ($physicalDestination->isSetCity()) {
                        echo ("                            City\n");
                        echo ("                                " . $physicalDestination->getCity() .
                                 "\n");
                    }
                    if ($physicalDestination->isSetCounty()) {
                        echo ("                            County\n");
                        echo ("                                " . $physicalDestination->getCounty() .
                                 "\n");
                    }
                    if ($physicalDestination->isSetDistrict()) {
                        echo ("                            District\n");
                        echo ("                                " .
                                 $physicalDestination->getDistrict() . "\n");
                    }
                    if ($physicalDestination->isSetStateOrRegion()) {
                        echo ("                            StateOrRegion\n");
                        echo ("                                " .
                                 $physicalDestination->getStateOrRegion() . "\n");
                    }
                    if ($physicalDestination->isSetPostalCode()) {
                        echo ("                            PostalCode\n");
                        echo ("                                " .
                                 $physicalDestination->getPostalCode() . "\n");
                    }
                    if ($physicalDestination->isSetCountryCode()) {
                        echo ("                            CountryCode\n");
                        echo ("                                " .
                                 $physicalDestination->getCountryCode() . "\n");
                    }
                    if ($physicalDestination->isSetPhone()) {
                        echo ("                            Phone\n");
                        echo ("                                " . $physicalDestination->getPhone() .
                                 "\n");
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
            if ($orderReferenceDetails->isSetReleaseEnvironment()) {
                echo ("                    ReleaseEnvironment\n");
                echo ("                        " . $orderReferenceDetails->getReleaseEnvironment() .
                         "\n");
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
                echo ("                    SellerOrderAttributes\n");
                $sellerOrderAttributes = $orderReferenceDetails->getSellerOrderAttributes();
                if ($sellerOrderAttributes->isSetSellerOrderId()) {
                    echo ("                        SellerOrderId\n");
                    echo ("                            " . $sellerOrderAttributes->getSellerOrderId() .
                             "\n");
                }
                if ($sellerOrderAttributes->isSetStoreName()) {
                    echo ("                        StoreName\n");
                    echo ("                            " . $sellerOrderAttributes->getStoreName() .
                             "\n");
                }
                if ($sellerOrderAttributes->isSetOrderItemCategories()) {
                    echo ("                        OrderItemCategories\n");
                    $orderItemCategories = $sellerOrderAttributes->getOrderItemCategories();
                    $orderItemCategoryList = $orderItemCategories->getOrderItemCategory();
                    foreach ($orderItemCategoryList as $orderItemCategory) {
                        echo ("                            OrderItemCategory\n");
                        echo ("                                " . $orderItemCategory);
                    }
                }
                if ($sellerOrderAttributes->isSetCustomInformation()) {
                    echo ("                        CustomInformation\n");
                    echo ("                            " .
                             $sellerOrderAttributes->getCustomInformation() . "\n");
                }
            }
            if ($orderReferenceDetails->isSetOrderReferenceStatus()) {
                echo ("                    OrderReferenceStatus\n");
                $orderReferenceStatus = $orderReferenceDetails->getOrderReferenceStatus();
                if ($orderReferenceStatus->isSetState()) {
                    echo ("                        State\n");
                    echo ("                            " . $orderReferenceStatus->getState() . "\n");
                }
                if ($orderReferenceStatus->isSetLastUpdateTimestamp()) {
                    echo ("                        LastUpdateTimestamp\n");
                    echo ("                            " .
                             $orderReferenceStatus->getLastUpdateTimestamp() . "\n");
                }
                if ($orderReferenceStatus->isSetReasonCode()) {
                    echo ("                        ReasonCode\n");
                    echo ("                            " . $orderReferenceStatus->getReasonCode() .
                             "\n");
                }
                if ($orderReferenceStatus->isSetReasonDescription()) {
                    echo ("                        ReasonDescription\n");
                    echo ("                            " .
                             $orderReferenceStatus->getReasonDescription() . "\n");
                }
            }
            if ($orderReferenceDetails->isSetConstraints()) {
                echo ("                    Constraints\n");
                $constraints = $orderReferenceDetails->getConstraints();
                $constraintList = $constraints->getConstraint();
                foreach ($constraintList as $constraint) {
                    echo ("                        Constraint\n");
                    if ($constraint->isSetConstraintID()) {
                        echo ("                            ConstraintID\n");
                        echo ("                                " . $constraint->getConstraintID() .
                                 "\n");
                    }
                    if ($constraint->isSetDescription()) {
                        echo ("                            Description\n");
                        echo ("                                " . $constraint->getDescription() .
                                 "\n");
                    }
                }
            }
            if ($orderReferenceDetails->isSetCreationTimestamp()) {
                echo ("                    CreationTimestamp\n");
                echo ("                        " . $orderReferenceDetails->getCreationTimestamp() .
                         "\n");
            }
            if ($orderReferenceDetails->isSetExpirationTimestamp()) {
                echo ("                    ExpirationTimestamp\n");
                echo ("                        " . $orderReferenceDetails->getExpirationTimestamp() .
                         "\n");
            }
            if ($orderReferenceDetails->isSetParentDetails()) {
                echo ("                    ParentDetails\n");
                $parentDetails = $orderReferenceDetails->getParentDetails();
                if ($parentDetails->isSetId()) {
                    echo ("                        Id\n");
                    echo ("                            " . $parentDetails->getId() . "\n");
                }
                if ($parentDetails->isSetType()) {
                    echo ("                        Type\n");
                    echo ("                            " . $parentDetails->getType() . "\n");
                }
            }
        }
    }
    if ($response->isSetResponseMetadata()) {
        echo ("            ResponseMetadata\n");
        $responseMetadata = $response->getResponseMetadata();
        if ($responseMetadata->isSetRequestId()) {
            echo ("                RequestId\n");
            echo ("                    " . $responseMetadata->getRequestId() . "\n");
        }
    }
    
    echo ("            ResponseHeaderMetadata: " . $response->getResponseHeaderMetadata() . "\n");
}
?>                                      