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

require_once 'OffAmazonPaymentsNotifications/InvalidCertificateException.php';

/**
 * Class to wrap a Certificate
 * 
 */
class Certificate
{
    /**
     * Certificate as string (read from file/URL)
     * 
     * @var string
     */
    private $_certificate;
    
    /**
     * Create a new instance of the certificate and
     * wraps the contents in a class
     * 
     * Throws an exception if the message is not valid
     * as defined by the implementation of this class
     * 
     * @param string $certificate a string pasred from file/URL
     * 
     * @return new instance of concreate class
     */
    public function __construct($certificate)
    {
        $this->_certificate = $certificate;
    }

    /**
     * Return the certificate string
     * 
     * @return string of contents
     */
    public function getCertificate()
    {
        return $this->_certificate;
    }
    
    /**
     * Extract the subject field from the certificate and return the contents
     * 
     * @throws OffAmazonPaymentsNotifications_InvalidCertificateException if not found
     * 
     * @return array of contents of the subject if found
     */
    public function getSubject()
    {
        $certInfo = openssl_x509_parse($this->_certificate, true);
        $certSubject = $certInfo["subject"];

        if (is_null($certSubject)) {
            throw new OffAmazonPaymentsNotifications_InvalidCertificateException(
                "Error with certificate - subject cannot be found"
            );
        }
        return $certSubject;
    }
}
?>
