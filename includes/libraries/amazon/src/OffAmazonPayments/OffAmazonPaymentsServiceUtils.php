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

class OffAmazonPaymentsServiceUtils 
{
    const MWS_CLIENT_VERSION = '2013-01-01';
    const APPLICATION_LIBRARY_VERSION = '1.0.12';

    public function buildUserAgentString($applicationName, $applicationVersion, $attributes = null) 
    {   
        if (is_null($attributes)) {
            $attributes = array ();
        }

        if (!array_key_exists("ApplicationLibraryVersion", $attributes)) {
            array_push($attributes, "ApplicationLibraryVersion", self::APPLICATION_LIBRARY_VERSION);
        }

        return $this->constructUserAgentHeader($applicationName, $applicationVersion, $attributes);
    }
    
    private function constructUserAgentHeader($applicationName, $applicationVersion, $attributes) 
    {
        $userAgent
            = $this->quoteApplicationName($applicationName)
            . '/'
            . $this->quoteApplicationVersion($applicationVersion);
            
        $userAgent .= ' (';
        $userAgent .= 'Language=PHP/' . phpversion();
        $userAgent .= '; ';
        $userAgent .= 'Platform=' . php_uname('s') . '/' . php_uname('m') . '/' . php_uname('r');
        $userAgent .= '; ';
        $userAgent .= 'MWSClientVersion=' . self::MWS_CLIENT_VERSION;
        
        foreach ($attributes as $key => $value) {
            if (empty($value)) {
                throw new InvalidArgumentException("value for $key cannot be null or empty");
            }
            
            $userAgent .= '; '
                    . $this->quoteAttributeName($key)
                    . '='
                    . $this->quoteAttributeValue($value);
        }
    
        $userAgent .= ')';
        
        return $userAgent;
    }
    
    /**
     * Collapse multiple whitespace characters into a single ' ' and backslash escape '\',
     * and '/' characters from a string.
     * @param $s
     * @return string
     */
    private function quoteApplicationName($s) {
        $quotedString = $this->collapseWhitespace($s);
        $quotedString = preg_replace('/\\\\/', '\\\\\\\\', $quotedString);
        $quotedString = preg_replace('/\//', '\\/', $quotedString);
    
        return $quotedString;
    }

    /**
     * Collapse multiple whitespace characters into a single ' ' and backslash escape '\',
     * and '(' characters from a string.
     *
     * @param $s
     * @return string
     */
    private function quoteApplicationVersion($s) {
        $quotedString = $this->collapseWhitespace($s);
        $quotedString = preg_replace('/\\\\/', '\\\\\\\\', $quotedString);
        $quotedString = preg_replace('/\\(/', '\\(', $quotedString);
    
        return $quotedString;
    }

    /**
    * Collapse multiple whitespace characters into a single ' ' character.
    * @param $s
    * @return string
    */
    private function collapseWhitespace($s) {
       return preg_replace('/ {2,}|\s/', ' ', $s);
    }

    /**
     * Collapse multiple whitespace characters into a single ' ' and backslash escape '\',
     * and '=' characters from a string.
     *
     * @param $s
     * @return unknown_type
     */
    private function quoteAttributeName($s) {
        $quotedString = $this->collapseWhitespace($s);
        $quotedString = preg_replace('/\\\\/', '\\\\\\\\', $quotedString);
        $quotedString = preg_replace('/\\=/', '\\=', $quotedString);
    
        return $quotedString;
    }

    /**
     * Collapse multiple whitespace characters into a single ' ' and backslash escape ';', '\',
     * and ')' characters from a string.
     *
     * @param $s
     * @return unknown_type
     */
    private function quoteAttributeValue($s) {
        $quotedString = $this->collapseWhitespace($s);
        $quotedString = preg_replace('/\\\\/', '\\\\\\\\', $quotedString);
        $quotedString = preg_replace('/\\;/', '\\;', $quotedString);
        $quotedString = preg_replace('/\\)/', '\\)', $quotedString);
    
        return $quotedString;
    }
}


?>