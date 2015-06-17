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

interface IHttpRequestFactory {

    /**
     * Create a http get request for the resource
     * at the given uri
     *
     * @param url uniform resource locator to get
     *
     * @return IHttpRequest object
     */
    public function createGetRequest($url);

    /**
     * Create a http post request using to given
     * given uri & body content
     *
     * @param url uniform resource locator to post
     *
     * @return IHttpRequest object
     */
    public function createPostRequest($url, $body);
};

?>