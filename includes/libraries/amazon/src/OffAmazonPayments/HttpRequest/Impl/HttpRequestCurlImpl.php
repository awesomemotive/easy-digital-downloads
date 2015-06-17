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

require_once 'OffAmazonPayments/HttpRequest/HttpException.php';
require_once 'OffAmazonPayments/HttpRequest/IHttpRequest.php';

class HttpRequestCurlImpl implements IHttpRequest 
{

    /**
     * Reference to the underlying curl handle
     */
    private $_ch = null;

    /*
     * Default headers for curl requests
     */
    private $_headers = array(
        'Expect' => null // Don't expect 100 Continue
    );

    /**
     * Create a new instane of the class + underlying curl handle
     * 
     */
    public function __construct()
    {
        $this->_ch = curl_init();
        curl_setopt($this->_ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($this->_ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->_ch, CURLOPT_HEADER, true);
        curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
    }

    /**
     * Set the url for the curl handle
     *
     * @param url resource to request
     */
    public function setUrl($url) {
        curl_setopt($this->_ch, CURLOPT_URL, $url);
    }

    /**
     * Set the port for the curl handle
     *
     * @param port to use
     */
    public function setPort($port) {
        curl_setopt($this->_ch, CURLOPT_PORT, $port);
    }

    /**
     * Set the useragent for the curl handle
     *
     * @param userAgent for request
     */
    public function setUserAgent($userAgent) {
        curl_setopt($this->_ch, CURLOPT_USERAGENT, $userAgent);
    }

    /**
     * Make this request a post request with the given body
     *
     * @param body http POST request body
     */
    public function makePost($body) {
        curl_setopt($this->_ch, CURLOPT_POST, true);
        curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $body);

        array_push($this->_headers, 'Content-Type', "application/x-www-form-urlencoded; charset=utf-8");
    }

    /**
     * Setup the ca bundle file
     *
     * @param caBundleFile file containing trusted ca certs
     */
    public function setCaBundleFile($caBundleFile)
    {
        curl_setopt($this->_ch, CURLOPT_CAINFO, $caBundleFile);
    }

    /**
     * Setup the proxy hostname and port
     *
     * @param hostnameport username and password in <hostname>:<port> format
     *
     */
    public function setupProxy($hostnameport)
    {
        curl_setopt($this->_ch, CURLOPT_PROXY, $hostnameport);
    }

    /**
     * Setup the proxy username and password
     *
     * @param usernamepwd username and password in <username>:<password> format
     *
     */
    public function setupProxyUsernameAndPassword($usernamepwd)
    {
        curl_setopt($this->_ch, CURLOPT_PROXYUSERPWD, $usernamepwd);
    }

    /**
     * Create a http get request for the resource
     * at the given uri
     *
     * @param execute the underlying http request
     *
     * @return response header + body
     */
    public function execute() 
    {
        $this->setRequestHeaders();
        $response = '';
        if (!$response = curl_exec($this->_ch)) {
            $errorNo = curl_error($this->_ch);
            curl_close($this->_ch);
            throw new OffAmazonPayments_HttpException($errorNo);
        }

        curl_close($this->_ch);

        return $response;
    }

    /**
     * Setup request header information
     *
     */
    private function setRequestHeaders()
    {
        $allHeadersStr = array();
        foreach($this->_headers as $name => $val) {
            $str = $name . ": ";
            if(isset($val)) {
                $str = $str . $val;
            }
            $allHeadersStr[] = $str;
        }

        curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $allHeadersStr);
    }
};

?>