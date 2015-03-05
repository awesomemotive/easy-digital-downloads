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

require_once 'OffAmazonPayments/HttpRequest/IHttpRequestFactory.php';
require_once 'OffAmazonPayments/HttpRequest/Impl/HttpRequestCurlImpl.php';
require_once 'OffAmazonPayments/OffAmazonPaymentsServiceUtils.php';
require_once 'OffAmazonPaymentsService/MerchantValues.php';

/**
 * Wrapper to simplify curl functions for http get/set
 * 
 */
class HttpRequestFactoryCurlImpl implements IHttpRequestFactory
{

    /**
     * Merchant values configuration instance
     *
     */
    private $_merchantValues = null;

    
    /**
     * Create an instance of the client class
     *
     * @param instance of OffAmazonPayments_MerchantValues class
     * 
     * @return void
     */
    public function __construct($merchantValues) {

        if(!isset($merchantValues)) {
            throw new InvalidArgumentException("merchantValue object not injected");
        }

        $this->_merchantValues = $merchantValues;
    }

    /**
     * Create a http get request for the resource
     * at the given uri
     *
     * @param url uniform resource locator to get
     *
     * @return HttpRequest object
     */
    public function createGetRequest($url) 
    {
        return $this->createNewRequest($url);
    }

    /**
     * Create a http post request using to given
     * given uri & body content
     *
     * @param url uniform resource locator to post
     *
     * @return HttpRequest object
     */
    public function createPostRequest($url, $body) 
    {
        $httpRequest = $this->createNewRequest($url);
        $httpRequest->makePost($body);

        return $httpRequest;
    }

    /**
     * Create a new curl handle and set up with default
     * options for all requests
     *
     * @param url resource to request
     *
     * @return curl handle
     */
    private function createNewRequest($url)
    {
        $httpRequest = new HttpRequestCurlImpl();

        $parsedUrl = $this->_setupConnectionInfo($url);

        $httpRequest->setUrl($parsedUrl['url']);
        $httpRequest->setPort($parsedUrl['port']);
        $httpRequest->setUserAgent($this->_merchantValues->getUserAgentString());

        # if a ca bundle is configured, use it as opposed to the default ca 
        # configured for the server
        if ($this->_merchantValues->isCaBundleConfigured()) {
            $httpRequest->setCaBundleFile($this->_merchantValues->getCaBundleFile());
        }

        if ($this->_merchantValues->isProxyConfigured()) {
            $this->setupProxyForCurl($httpRequest);
        }

        return $httpRequest;
    }

    /**
     * Setup the connection parameters for the request
     * 
     * @param url resource to request
     *
     */
    private function _setupConnectionInfo($url)
    {
        $parsed_url = parse_url($url);

        $uri = array_key_exists('path', $parsed_url) ? $parsed_url['path'] : null;
        if (!isset($uri)) {
            $uri = "/";
        }

        $scheme = '';

        switch ($parsed_url['scheme']) {
            case 'https':
                $scheme = 'https://';
                $port = array_key_exists('port', $parsed_url) && (isset($parsed_url['port'])) ? $parsed_url['port'] : 443;
                break;
            default:
                $scheme = 'http://';
                $port = array_key_exists('port', $parsed_url) && (isset($parsed_url['port'])) ? $parsed_url['port'] : 80;
        }

        $retVal = array(
            'port' => $port,
            'url' => $scheme . $parsed_url['host'] . $uri
        );

        return $retVal;
    }

    /**
     * Setup proxy options for curl handle
     *
     * @param httpRequest httpRequestObject
     *
     */
    private function setupProxyForCurl($httpRequest)
    {
        $proxy = $this->_merchantValues->getProxyHost() . ':' . $this->_merchantValues->getProxyPort();
        $httpRequest->setupProxy($proxy);

        if ($this->_merchantValues->isProxyAuthenticationConfigured()) {
            $proxyUserPwd = $this->_merchantValues->getProxyUsername() . ':' . $this->_merchantValues->getProxyPassword();
            $httpRequest->setupProxyUsernameAndPassword($proxyUserPwd);
        }
    }
}

?>