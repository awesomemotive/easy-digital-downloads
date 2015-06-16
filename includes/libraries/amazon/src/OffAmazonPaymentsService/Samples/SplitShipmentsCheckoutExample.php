<?php
/*
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
 */

/*
 * Split shipments checkout example
 * 
 * This demonstrates a merchant use case where the order needs to be
 * furfilled in multiple shipments, or where a single item is shipped
 * in multiple shipments over time.
 * 
 * Note that the API does not require merchants to provide item level
 * information in order to capture funds -this example uses items in
 * order to show how to aggregate item level costs to a single value
 * for an order
 *
 * This is done using multiple calls to authorize and capture for
 * the full order amount
 *
 * This example will show the following service calls:
 *    - SetOrderReferenceDetails
 *    - ConfirmOrderReference
 *    - Authorize
 *    - GetAuthorizeDetails
 *    - Capture
 *    - GetCaptureDetails
 *    - CloseOrderReference
 */

require_once realpath(dirname(__FILE__)) . '/.config.inc.php';

class SplitShipmentsCheckoutExample
{
    private $_sellerId;
    private $_service;
    private $_amazonOrderReferenceId;
    private $_authorizationReferenceIdBase;
    private $_captureReferenceIdBase;
    private $_orderShipments;
    private $_itemsInStock;
    
    /**
     * Create a new instance of the split shipments
     * checkout example
     * 
     * @param OffAmazonPaymentsService_Client $service                instance of the service client
     * 
     * @param string                          $amazonOrderReferenceId an order reference object in
     *                                                                draft state to use in
     *                                                                the example
     *                                                                
     * @return new SplitShipmentsCheckoutExample
     */
    public function __construct($service, $amazonOrderReferenceId)
    {
        $this->_service = $service;
        $this->_amazonOrderReferenceId = $amazonOrderReferenceId;
        
        /*
         * Setup shared identifiers for the series of transaction requests
         */
        $this->_authorizationReferenceIdBase
            = str_replace("-", "", $this->_amazonOrderReferenceId) . "a01";
        $this->_captureReferenceIdBase = $this->_authorizationReferenceIdBase . "c01";
        
        $this->_sellerId
            = $this->_service->getMerchantValues()->getMerchantId();
        
        /*
         * Initialize the variable holding the shipments that are part
         * of the order
         */
        $this->_orderShipments = array();
        $this->_itemsInStock = array(
            new Item("Apple", 3.20), 
            new Item("Pinapple", 1.8), 
            new Item("Banana", 0.9), 
            new Item("Orange", 1.2),
            new Item("Pear", 2.1)
        );
    }
    
    /**
     * Step 1: calcaulte the total value of the order and set the amount on the order
     * reference
     * 
     */
    public function addOrderAmountToOrderReference()
    {
        $setOrderReferenceDetailsRequest = new OffAmazonPaymentsService_Model_SetOrderReferenceDetailsRequest();
        $setOrderReferenceDetailsRequest->setSellerId($this->_sellerId);
        $setOrderReferenceDetailsRequest->setAmazonOrderReferenceId($this->_amazonOrderReferenceId);
        $setOrderReferenceDetailsRequest->setOrderReferenceAttributes(new OffAmazonPaymentsService_Model_OrderReferenceAttributes());
        $setOrderReferenceDetailsRequest->getOrderReferenceAttributes()->setOrderTotal(new OffAmazonPaymentsService_Model_OrderTotal());
        $setOrderReferenceDetailsRequest->getOrderReferenceAttributes()->getOrderTotal()->setCurrencyCode($this->_service->getMerchantValues()->getCurrency());
        $setOrderReferenceDetailsRequest->getOrderReferenceAttributes()->getOrderTotal()->setAmount($this->_getOrderTotal());
        
        return $this->_service->setOrderReferenceDetails($setOrderReferenceDetailsRequest);
    }
    
    /**
     * Confirm the order reference information, allowing for
     * authorizations and captures to be created
     *
     * @return OffAmazonPaymentsService_Model_ConfirmOrderReferenceResponse service response
     */
    public function confirmOrderReference()
    {
        $confirmOrderReferenceRequest
            = new OffAmazonPaymentsService_Model_ConfirmOrderReferenceRequest();
        $confirmOrderReferenceRequest
            ->setAmazonOrderReferenceId($this->_amazonOrderReferenceId);
        $confirmOrderReferenceRequest->setSellerId($this->_sellerId);
    
        return $this->_service->confirmOrderReference($confirmOrderReferenceRequest);
    }
    
    /**
     * Perform the authorization for the shipment at the given index
     * 
     * @param int    $shipmentNumber order item index to authorize
     * 
     * @return OffAmazonPaymentsService_Model_AuthorizeResponse service response
     */
    public function performAuthorizationForShipment($shipmentNumber)
    {
        $item = $this->_orderShipments[$shipmentNumber];
        
        $authorizeRequest = new OffAmazonPaymentsService_Model_AuthorizeRequest();
        $authorizeRequest->setAmazonOrderReferenceId($this->_amazonOrderReferenceId);
        $authorizeRequest->setSellerId($this->_sellerId);
        $authorizeRequest
            ->setAuthorizationReferenceId($this->_authorizationReferenceIdBase . $shipmentNumber);
        $authorizeRequest->setAuthorizationAmount(new OffAmazonPaymentsService_Model_Price());
        $authorizeRequest->getAuthorizationAmount()->setAmount($item->price);
        $authorizeRequest->getAuthorizationAmount()->setCurrencyCode($this->_service->getMerchantValues()->getCurrency());
        
        // Set the application timeout so that the polling request will
        // get a definitive response within this period of time
        $authorizeRequest->setTransactionTimeout(5);
        
        return $this->_service->authorize($authorizeRequest);
    }
    
    /**
     * Poll the API for the status of the Authorization Request, and continue
     * once the status has been updated
     * WARNING: This is not the way to integrate for production systems,
     * instead merchants should use IPN to receive a callback once the
     * processing has been completed.
     * Note that Amazon reserves the right to throttle requests that
     * ignore this advice and poll for a response
     *
     * @param string $amazonAuthorizationReferenceId authorization transaction to query
     *
     * @return OffAmazonPaymentsService_Model_GetAuthorizationDetailsResponse service response
     */
    public function waitUntilAuthorizationProcessingIsCompleted($amazonAuthorizationId)
    {         
        $getAuthorizationDetailsResponse = null;
        do {
            sleep(5);
            $getAuthorizationDetailsResponse
                = $this->getAuthorizationDetails($amazonAuthorizationId);
        } while ((isset($getAuthorizationDetailsResponse)) and
                (strcasecmp(
                    $getAuthorizationDetailsResponse
                        ->getGetAuthorizationDetailsResult()->getAuthorizationDetails()
                        ->getAuthorizationStatus()->getState(),
                    "Pending"
                ) == 0));
         
        if (is_null($getAuthorizationDetailsResponse)) {
            throw new ErrorException("No valid response from getAuthorizationDetails request");
        }
         
        return $getAuthorizationDetailsResponse;
    }
    
    /**
     * Perform the getAuthroizationDetails request for the order
     *
     * @param string $amazonAuthorizationReferenceId authorization transaction
     *                                               to query
     *
     * @return OffAmazonPaymentsService_Model_GetAuthorizationDetailsResponse service response
     */
    public function getAuthorizationDetails($amazonAuthorizationId)
    {
        $getAuthorizationDetailsRequest = new OffAmazonPaymentsService_Model_GetAuthorizationDetailsRequest();
        $getAuthorizationDetailsRequest->setSellerId($this->_sellerId);
        $getAuthorizationDetailsRequest->setAmazonAuthorizationId($amazonAuthorizationId);
    
        return $this->_service->getAuthorizationDetails($getAuthorizationDetailsRequest);
    }
    
    /**
     * Perform the capture for a single shipment
     * 
     * @param int    $shipmentNumber        order item index to capture
     * @param string $amazonAuthorizationId authorization to capture
     * 
     * @return OffAmazonPayments_Model_CaptureResponse service response
     */
    public function performCaptureForShipment($shipmentNumber, $amazonAuthorizationId)
    {
        $item = $this->_orderShipments[$shipmentNumber];
        
        $captureRequest = new OffAmazonPaymentsService_Model_CaptureRequest();
        $captureRequest->setSellerId($this->_sellerId);
        $captureRequest->setAmazonAuthorizationId($amazonAuthorizationId);
        $captureRequest
            ->setCaptureReferenceId($this->_captureReferenceIdBase . $shipmentNumber);
        $captureRequest->setCaptureAmount(new OffAmazonPaymentsService_Model_Price());
        $captureRequest->getCaptureAmount()->setAmount($item->price);
        $captureRequest->getCaptureAmount()->setCurrencyCode($this->_service->getMerchantValues()->getCurrency());
        
        return $this->_service->capture($captureRequest);
    }
    
    /**
     * Perform the get capture details call for the order
     *
     * @param string $amazonCaptureId capture it to get details for
     *
     * @return OffAmazonPaymentsService_Model_CaptureResponse service response
     */
    public function getCaptureDetails($amazonCaptureId)
    {
        $captureDetailsRequest
            = new OffAmazonPaymentsService_Model_GetCaptureDetailsRequest();
        $captureDetailsRequest->setSellerId($this->_sellerId);
        $captureDetailsRequest->setAmazonCaptureId($amazonCaptureId);
    
        return $this->_service->getCaptureDetails($captureDetailsRequest);
    }
    
    /**
     * Close this order reference to indicate that the order is complete, and
     * no further authorizations and captures will be performed on this order
     *
     * @return OffAmazonPaymentsService_Model_CloseOrderReferenceResponse service response
     */
    public function closeOrderReference()
    {
        $closeOrderReferenceRequest = new OffAmazonPaymentsService_Model_CloseOrderReferenceRequest();
        $closeOrderReferenceRequest->setSellerId($this->_sellerId);
        $closeOrderReferenceRequest->setAmazonOrderReferenceId($this->_amazonOrderReferenceId);
        $closeOrderReferenceRequest->setClosureReason("Order complete");
    
        return $this->_service->closeOrderReference($closeOrderReferenceRequest);
    }
   
    /**
     * Add a new shipment of an item to the order
     * 
     * @param int $itemIndex index of the item to place in this order
     * 
     */
    public function addShipmentToOrder($itemIndex)
    {
        array_push($this->_orderShipments, $this->_itemsInStock[$itemIndex]); 
    }
    
    /**
     * Return the names of the items available to place into the order
     * 
     * @return array names of available items
     */
    public function getItemNames()
    {
        return array_map(
            function($item) {
                return $item->name;
            }, $this->_itemsInStock
        );
    }
    
    /**
     * Return the total amount for the order
     * 
     * @return int order total
     */
    private function _getOrderTotal()
    {
        return array_reduce(
            $this->_orderShipments, 
            function($runningTotal, $item) {
                $runningTotal += $item->price;
                return $runningTotal;
            }
        );
    }
    
}
 
/*
 * Helper class to store item information for the
 * split shipment scenario
 */
class Item
{
    public $price;
    public $name;
    
    /**
     * Create a new item
     * 
     * @param float $price
     * @param string $name
     * 
     * @return new Item instance
     */
    public function __construct($name, $price)
    {
        $this->price = $price;
        $this->name = $name;
    }
}
?>
