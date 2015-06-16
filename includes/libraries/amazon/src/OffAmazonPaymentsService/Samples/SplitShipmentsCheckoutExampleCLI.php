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
 * See SplitShipmentsCheckoutExample.php for more information
 */

require_once realpath(dirname(__FILE__)) . '/.config.inc.php';
require_once realpath(dirname(__FILE__)) . '/SetOrderReferenceDetailsSample.php';
require_once realpath(dirname(__FILE__)) . '/ConfirmOrderReferenceSample.php';
require_once realpath(dirname(__FILE__)) . '/AuthorizeSample.php';
require_once realpath(dirname(__FILE__)) . '/GetAuthorizationDetailsSample.php';
require_once realpath(dirname(__FILE__)) . '/CaptureSample.php';
require_once realpath(dirname(__FILE__)) . '/CloseOrderReferenceSample.php';
require_once realpath(dirname(__FILE__)) . '/CLIExample.php';
require_once realpath(dirname(__FILE__)) . '/SplitShipmentsCheckoutExample.php';
require_once realpath(dirname(__FILE__)) . '/GetCaptureDetailsSample.php';

/*
 * SplitShipmentsCheckoutExampleCLI class captures input from stdin and prints to stdout,
 * and drives the split shipment checkout example
 * 
 */
class SplitShipmentsCheckoutExampleCLI extends CLIExample
{
    /**
     * Create a new instance of the cli example and
     * validate command line arguments
     * 
     * @param array $argv arguments to the application passed to the command line
     */
    public function __construct($argv)
    {
        if (count($argv) < 1) {
            print "Missing mandatory argument: " .
               "please provide an amazonOrderReferenceId";
            exit(0);
        }
        
        /*
         * Instantiate Implementation of OffAmazonPaymentsService client
         *
         * Merchant ids and access keys are defined in the constants
         * are defined in the OffAmazonPaymentsService.config.inc.php file in
         * the parent directory of this server
         */
        $this->exampleClass 
            = new SplitShipmentsCheckoutExample(
                new OffAmazonPaymentsService_Client(), 
                $argv[1]
            );
    }

    /**
     * Run all the steps for the sample in sequence
     *
     */
    public function runSample()
    {
        $shipments = $this->_getNumberOfShipmentsInOrder();
        $this->_addShipmentsToOrder($shipments);
        $this->_addOrderAmountToOrderReference();
        $this->_confirmOrderReference();
        $this->_performAuthAndCaptureForOrderShipments($shipments);
        $this->_closeOrderReference();
        print "Split shipments checkout example completed" . PHP_EOL;
    }
    
    /**
     * Capture the number of shipments for this run of the scenario
     * 
     * @return int number of shipments in this order
     */
    private function _getNumberOfShipmentsInOrder()
    {
        print "Enter the number of shipments that you want to perform for this order: ";
        
        do {
            $shipments = trim(fgets(STDIN));
        } while (!is_numeric($shipments));
        
        print PHP_EOL;
        return $shipments;
    }
   
    /**
     * Fill in the details for each shipment in this order
     * 
     * @param int $shipments number of shipments to fill
     * 
     */
    private function _addShipmentsToOrder($shipments)
    {
        for ($i = 0; $i < $shipments; $i++) {
            $this->_addShipmentToOrder($i);
        }
    }
    
    /**
     * Fill in the details for a single shipment in the order
     * 
     * @param int $shipmentNumber nth shipment for the order
     */
    private function _addShipmentToOrder($shipmentNumber)
    {
        print "Select an item to add to shipment #" . ($shipmentNumber + 1) . ":" . PHP_EOL;
        $items = $this->exampleClass->getItemNames();
        for ($i = 0; $i < count($items); $i++) {
            print " " . $i . " - " . $items[$i] . PHP_EOL;
        }

        do {
            print "Select the number of the item to add to this shipment:";
            $itemToAdd = trim(fgets(STDIN));
        } while (!is_numeric($itemToAdd));
        
        print PHP_EOL;
        
        $this->exampleClass->addShipmentToOrder($itemToAdd);
    }
   
    /**
     * Add the total price of all items in the order and update the payment contract
     *
     */
    private function _addOrderAmountToOrderReference()
    {
        $response = $this->callStepAndCheckForException('addOrderAmountToOrderReference');
        printSetOrderReferenceDetailsResponse($response);
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
        printConfirmOrderReferenceResponse($response);
    }
    
    /**
     * Perform the authorize and captures for all shipments in this
     * order
     * 
     * @param int $shipments total number of shipments in this order
     */
    private function _performAuthAndCaptureForOrderShipments($shipments)
    {
        for ($i = 0; $i < $shipments; $i++) {
             $this->_performAuthAndCaptureForOrderShipment($i);
        }
    }
    
    /**
     * Perform the authorize and capture for a single shipment in the order
     * 
     * @param int $shipmentNumber the shipment to perform transactions on
     */
    private function _performAuthAndCaptureForOrderShipment($shipmentNumber)
    {
        $response = $amazonAuthorizationId = $this->_performAuthorizationForShipment($shipmentNumber);
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
        $response = $this->callStepAndCheckForException('performAuthorizationForShipment', array($shipmentNumber));
        printAuthorizeResponse($response);
        return $response->getAuthorizeResult()->getAuthorizationDetails()->getAmazonAuthorizationId();
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
     */
    private function _waitUntilAuthorizationProcessingIsCompleted($amazonAuthorizationId)
    {
        $response = $this->callStepAndCheckForException(
            'waitUntilAuthorizationProcessingIsCompleted', 
            array($amazonAuthorizationId)
        );
        printGetAuthorizationDetailsResponse($response);
        validateThatAuthorizationIsOpen($response);
    }

    /**
     * Perform the capture for a single shipment
     *
     * @param int    $shipmentNumber        order item index to capture
     * @param string $amazonAuthorizationId authorization to capture
     *
     */
    private function _performCaptureForShipment($shipmentNumber, $amazonAuthorizationId)
    {
        $response = $this->callStepAndCheckForException(
            'performCaptureForShipment',
            array($shipmentNumber, $amazonAuthorizationId)
        );
        validateThatCaptureIsCompleted($response->getCaptureResult());
        printCaptureResponse($response);
    }
    
    /**
     * Close this order reference to indicate that the order is complete, and
     * no further authorizations and captures will be performed on this order
     *
     */
    private function _closeOrderReference()
    {
        $response = $this->callStepAndCheckForException('closeOrderReference');
    }
}

$splitShipmentsCheckoutExample = new SplitShipmentsCheckoutExampleCLI($argv);
$splitShipmentsCheckoutExample->runSample();
?>