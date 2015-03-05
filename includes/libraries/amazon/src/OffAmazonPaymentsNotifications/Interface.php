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
 * Interface for interacting with the OffAmazonPaymentsNotifications
 * library
 *
 */
interface OffAmazonPaymentsNotifications_Interface
{
    /**
     * Converts a http POST body and headers into
     * a notification object
     *
     * @param array  $headers post request headers
     * @param string $body    post request body, should be json
     *
     * @throws OffAmazonPaymentsNotifications_InvalidMessage
     *
     * @return OffAmazonPaymentNotifications_Notification
     */
    public function parseRawMessage($headers, $body);
}

?>