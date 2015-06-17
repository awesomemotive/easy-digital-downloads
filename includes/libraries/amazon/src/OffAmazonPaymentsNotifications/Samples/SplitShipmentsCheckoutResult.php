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

require_once realpath(dirname(__FILE__) . "/.config.inc.php");
require_once 'OffAmazonPaymentsNotifications/Samples/WebServerExample.php';
require_once 'OffAmazonPaymentsService/Client.php';
require_once 'OffAmazonPaymentsService/Samples/SplitShipmentsCheckoutExample.php';

/**
 * This script simulates a split shipments checkout example and generates
 * html for the page
 *
 */
class SplitShipmentsCheckoutResult extends WebServerExample
{
    /**
     * Create a new instance of the Split Shipments Checkout Example
     *
     * @param string $queryString url query string
     *
     * @return void
     */
    public function __construct($queryString)
    {
        parent::__construct($queryString);
   
        $this->exampleClass = new SplitShipmentsCheckoutExample(
            new OffAmazonPaymentsService_Client(),
            $this->queryStringParams['orderReferenceId']
        );
    }
 
    /**
     * Execute the example
     *
     * @return void
     */
    public function run()
    {
        $this->_addShipmentsToOrder();
        $this->_addOrderAmountToOrderReference($this->currencyCode);
        $this->_confirmOrderReference();
        $this->_performAuthAndCaptureForOrderShipments();
        $this->_closeOrderReference();
        print HTML_LB.HTML_LB."Split Shipment Checkout Sample is Complete";
    }
 
    /**
     * Fill in the details for a single shipment in the order
     *
     * @return void
     */
    private function _addShipmentsToOrder()
    {
        for ($i=1; $i<=$this->queryStringParams["totalShipments"];$i++) {
            $this->exampleClass->addShipmentToOrder(
                $this->queryStringParams["item".$i]
            );
        }
    }
 
    /**
     * Add the total price of all items in the order and 
     * update the payment contract
     *
     * @param string $currencyCode curreny that the order is placed in
     * 
     * @return void
     */
    private function _addOrderAmountToOrderReference($currencyCode)
    {
        $response = $this->callStepAndCheckForException(
            'addOrderAmountToOrderReference', array($currencyCode)
        );
        echo $this->printResponseToWebpage(
            "printSetOrderReferenceDetailsResponse", array($response)
        );
    }
 
    /**
     * Confirm the order reference information, allowing for
     * authorizations and captures to be created
     *
     * @return OffAmazonPaymentsService_Model_ConfirmOrderReferenceResponse service response
     */
    private function _confirmOrderReference()
    {
        $response = $this->callStepAndCheckForException('confirmOrderReference');
        echo $this->printResponseToWebpage(
            "printConfirmOrderReferenceResponse", array($response)
        );
    }
    
    /**
     * Perform the authorize and captures for all shipments in this
     * order
     *
     * @return void
     */
    private function _performAuthAndCaptureForOrderShipments()
    {
        for ($i = 0; $i < $this->queryStringParams["totalShipments"]; $i++) {
            $this->_performAuthAndCaptureForOrderShipment($i);
        }
    }
 
    /**
     * Perform the authorize and capture for a single shipment in the order
     *
     * @param int $shipmentNumber the shipment to perform transactions on
     * 
     * @return void
     */
    private function _performAuthAndCaptureForOrderShipment($shipmentNumber)
    {
        $amazonAuthorizationId 
            = $this->_performAuthorizationForShipment($shipmentNumber);
        $this->_waitUntilAuthorizationProcessingIsCompleted($amazonAuthorizationId);
        $this->_performCaptureForShipment($shipmentNumber, $amazonAuthorizationId);
    }
 
    /**
     * Perform the authorization for the shipment at the given index
     *
     * @param int $shipmentNumber order item index to authorize
     *
     * @return string amazonAuthorizationId for the single auth call
     */
    private function _performAuthorizationForShipment($shipmentNumber)
    {
        $response = $this->callStepAndCheckForException(
            'performAuthorizationForShipment', 
            array($shipmentNumber, $this->currencyCode)
        );
        echo $this->printResponseToWebpage(
            "printAuthorizeResponse", 
            array($response)
        );
        
        return $response->getAuthorizeResult()
            ->getAuthorizationDetails()->getAmazonAuthorizationId();
    }
 
    /**
     * Check that we have received an IPN notification for the authorization
     * 
     * For PHP, there is an IPN handler that will write the contents of the IPN to
     * a file in the format of 
     * <amazonAuthorizationId>_Authorization.txt
     * This method will check for the presence of this file 
     * and will loop/timeout until the notification has been handled.
     * 
     * Merchants can use alternative approaches such as memory caches, 
     * shared memory or database storage so that scripts serving user 
     * pages are able to check on the status of a notification
     *
     * @param string $amazonAuthorizationId authorization transaction to query
     * 
     * @return void
     */
    private function _waitUntilAuthorizationProcessingIsCompleted(
        $amazonAuthorizationId
    ) {
        // Check for the presence of the ipn notification
        $this->waitForNotificationToBeProcessedBeforeContinuing(
            $amazonAuthorizationId,
            "AuthorizationNotification"
        );
        
        // Notification is present, go and get the full 
        // information for this notification
        $response 
            = $this->callStepAndCheckForException(
                'getAuthorizationDetails',
                array($amazonAuthorizationId) 
            );
        
        echo $this->printResponseToWebpage(
            "printGetAuthorizationDetailsResponse", array($response)
        );
        
        validateThatAuthorizationIsOpen($response);
    }
 
    /**
     * Perform the capture for a single shipment
     *
     * @param int    $shipmentNumber        order item index to capture
     * @param string $amazonAuthorizationId authorization to capture
     *
     * @return void
     */
    private function _performCaptureForShipment(
        $shipmentNumber,
        $amazonAuthorizationId
    ) {
        $captureResponse = $this->callStepAndCheckForException(
            'performCaptureForShipment',
            array(
                $shipmentNumber,
                $amazonAuthorizationId,
                $this->currencyCode
            )
        );
        
        echo $this->printResponseToWebpage(
            "printCaptureResponse", array($captureResponse)
        );
        
        $captureId = $captureResponse->getCaptureResult()
            ->getCaptureDetails()->getAmazonCaptureId();
  
        // Check for the presence of the ipn notification
        $this->waitForNotificationToBeProcessedBeforeContinuing(
            $captureId,
            "CaptureNotification"
        );
  
        $getCaptureDetailsresponse = $this->callStepAndCheckForException(
            'getCaptureDetails',
            array($captureId)
        );
  
        validateThatCaptureIsCompleted($getCaptureDetailsresponse->getGetCaptureDetailsResult());
  
        echo $this->printResponseToWebpage(
            "printGetCaptureDetailsResponse", array($getCaptureDetailsresponse)
        );
    }
 
    /**
     * Close this order reference to indicate that the order is complete, and
     * no further authorizations and captures will be performed on this order
     *
     * @return void
     */
    private function _closeOrderReference()
    {
        $response = $this->callStepAndCheckForException('closeOrderReference');
    }
}

$splitShipmentsCheckoutResult = new SplitShipmentsCheckoutResult($_SERVER['QUERY_STRING']);
$splitShipmentsCheckoutResult->run();
?>