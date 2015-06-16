<?php

/*******************************************************************************
 *  Copyright 2013 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *
 *  You may not use this file except in compliance with the License.
 *   You may obtain a copy of the License at:
 *  http://aws.amazon.com/apache2.0
 *  This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
 *  CONDITIONS OF ANY KIND, either express or implied. See the License
 *  for the
 *  specific language governing permissions and limitations under the 
 *  License .
 * *****************************************************************************
 */

/**
 * Interface to abstract an implementation of the
 * signature verification algorithm
 * 
 */
interface VerifySignature
{
    /**
     * Verify that the signature is correct for the given data and
     * public key
     * 
     * @param string $data            data to validate
     * @param string $signature       decoded signature to compare against
     * @param string $certificatePath path to certificate, can be file or url
     * 
     * @throws OffAmazonPaymentsNotifications_InvalidMessageException if there 
     *                                                                is an error 
     *                                                                with the call
     * 
     * @return bool true if valid
     */
    function verifySignatureIsCorrect($data, $signature, $certificatePath);
}
?>