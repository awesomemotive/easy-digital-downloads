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
require_once 'OffAmazonPaymentsNotifications/Samples/NotificationSample.php';
require_once 'OffAmazonPaymentsNotifications/Model/OrderReferenceNotification.php';

/**
 * Class for handling an order reference notification and print the
 * contents to the log file
 *
 */
class OffAmazonPaymentsNotifications_Samples_OrderReferenceSample 
    extends OffAmazonPaymentsNotifications_Samples_NotificationSample
{
    /**
     * Create a new instance of the Order reference notification sample
     *
     * @param OffAmazonPaymentsNotifications_Model_OrderReferenceNotification $notification notification
     *
     * @return void
     */
    public function __construct(
        OffAmazonPaymentsNotifications_Model_OrderReferenceNotification $notification
    ) {
        parent::__construct($notification);
    }
    
    /**
     * Extract the name of the log file based on the notification
     *
     * @throws InvalidArgumentException
     *
     * @return string
     */
    protected function getLogFileName() 
    {
        if (!$this->notification->getOrderReference()->isSetAmazonOrderReferenceId()) {
            throw new InvalidArgumentException("OrderReferenceId is NULL");
        }

        return $this->notification->getOrderReference()->getAmazonOrderReferenceId() 
            . "_OrderReference.txt";
    }
    
    /**
     * Log the notification contents
     *
     * @return void
     */
    protected function logNotificationContents()
    {
        $this->ipnLogFile->writeLine("OrderReference @ ".date("Y-m-d H:i:s"));
        $this->ipnLogFile->writeLine("=============================================================================");
        if ($this->notification->isSetOrderReference()) {
            $this->ipnLogFile->writeLine("  OrderReference");
            $orderReference = $this->notification->getOrderReference();
            if ($orderReference->isSetAmazonOrderReferenceId()) {
                $this->ipnLogFile->writeLine("      AmazonOrderReferenceId");
                $this->ipnLogFile->writeLine("          " . $orderReference->getAmazonOrderReferenceId());
            }
            if ($orderReference->isSetOrderTotal()) {
                $this->ipnLogFile->writeLine("  OrderTotal");
                $orderTotal = $orderReference->getOrderTotal();
                if ($orderTotal->isSetAmount()) {
                    $this->ipnLogFile->writeLine("      Amount");
                    $this->ipnLogFile->writeLine("          " . $orderTotal->getAmount());
                }
                if ($orderTotal->isSetCurrencyCode()) {
                    $this->ipnLogFile->writeLine("      CurrencyCode");
                    $this->ipnLogFile->writeLine("          " . $orderTotal->getCurrencyCode());
                }
            }
            if ($orderReference->isSetSellerOrderAttributes()) {
                $this->ipnLogFile->writeLine("  SellerOrderAttributes");
                $sellerOrderAttributes = $orderReference->getSellerOrderAttributes();
                if ($sellerOrderAttributes->isSetSellerId()) {
                    $this->ipnLogFile->writeLine("      SellerId");
                    $this->ipnLogFile->writeLine("          " . $sellerOrderAttributes->getSellerId());
                }
                if ($sellerOrderAttributes->isSetSellerOrderId()) {
                    $this->ipnLogFile->writeLine("      SellerOrderId");
                    $this->ipnLogFile->writeLine("          " . $sellerOrderAttributes->getSellerOrderId());
                }
                if ($sellerOrderAttributes->isSetOrderItemCategories()) {
                    $this->ipnLogFile->writeLine("       OrderItemCategories");
                    $orderItemCategories = $sellerOrderAttributes->getOrderItemCategories();
                    $orderItemCategoryList  =  $orderItemCategories->getOrderItemCategory();
                    foreach ($orderItemCategoryList as $orderItemCategory) {
                        $this->ipnLogFile->writeLine("          OrderItemCategory");
                        $this->ipnLogFile->writeLine("             " . $orderItemCategory);
                    }
                }
            }
            if ($orderReference->isSetOrderReferenceStatus()) {
                $this->ipnLogFile->writeLine("  OrderReferenceStatus");
                $orderReferenceStatus = $orderReference->getOrderReferenceStatus();
                if ($orderReferenceStatus->isSetState())
                {
                    $this->ipnLogFile->writeLine("          State");
                    $this->ipnLogFile->writeLine("          " . $orderReferenceStatus->getState());
                }
                if ($orderReferenceStatus->isSetLastUpdateTimestamp())
                {
                    $this->ipnLogFile->writeLine("          LastUpdateTimestamp");
                    $this->ipnLogFile->writeLine("          " . $orderReferenceStatus->getLastUpdateTimestamp());
                }
                if ($orderReferenceStatus->isSetReasonCode())
                {
                    $this->ipnLogFile->writeLine("          ReasonCode");
                    $this->ipnLogFile->writeLine("          " . $orderReferenceStatus->getReasonCode());
                }
                if ($orderReferenceStatus->isSetReasonDescription())
                {
                    $this->ipnLogFile->writeLine("          ReasonDescription");
                    $this->ipnLogFile->writeLine("          " . $orderReferenceStatus->getReasonDescription());
                }
            }
            if ($orderReference->isSetCreationTimestamp()) {
                $this->ipnLogFile->writeLine("  CreationTimestamp");
                $this->ipnLogFile->writeLine("      " . $orderReference->getCreationTimestamp());
            }
            if ($orderReference->isSetExpirationTimestamp()) {
                $this->ipnLogFile->writeLine("  ExpirationTimestamp");
                $this->ipnLogFile->writeLine("      " . $orderReference->getExpirationTimestamp());
            }
        }
    }
}
?>