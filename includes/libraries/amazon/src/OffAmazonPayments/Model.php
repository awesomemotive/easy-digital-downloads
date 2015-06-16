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
 * OffAmazonPayments_Model - base class for all model classes
 */ 
abstract class OffAmazonPayments_Model
{
    
    /** 
     * Defined fields for the model
     * object
     * 
     * @var array 
     */
    protected $fields = array ();
          
    /**
     * Construct new model class
     * 
     * @param mixed $data - DOMElement or Associative Array to construct from. 
     */
    public function __construct($data = null)
    {
        if (!is_null($data)) {
            if ($this->_isAssociativeArray($data)) {
                $this->_fromAssociativeArray($data);
            } elseif ($this->_isDOMElement($data)) {
                $this->_fromDOMElement($data);
            } else {
                throw new Exception(
                    "Unable to construct from provided data." . 
                    "Please be sure to pass associative array or DOMElement"
                );
            }
            
        }
    }

    /**
     * Support for virtual properties getters. 
     * 
     * Virtual property call example:
     *  
     *   $action->Property
     *   
     * Direct getter(preferred): 
     * 
     *   $action->getProperty()      
     * 
     * @param string $propertyName name of the property
     * 
     * @return value of the property
     */
    public function __get($propertyName)
    {
        $getter = "get$propertyName"; 
        return $this->$getter();
    }

    /**
     * Support for virtual properties setters. 
     * 
     * Virtual property call example:
     *  
     *   $action->Property  = 'ABC'
     *   
     * Direct setter (preferred):
     * 
     *   $action->setProperty('ABC')     
     * 
     * @param string $propertyName  name of the property
     * @param mixed  $propertyValue value of the property
     * 
     * @return instance of the object
     */
    public function __set($propertyName, $propertyValue)
    {
        $setter = "set$propertyName";
        $this->$setter($propertyValue);
        return $this;
    }

         
    /**
     * XML fragment representation of this object
     * Note, name of the root determined by caller 
     * This fragment returns inner fields representation only
     * 
     * @return string XML fragment for this object
     */
    protected function toXMLFragment() 
    {
        $xml = "";
        foreach ($this->fields as $fieldName => $field) {
            $fieldValue = $field['FieldValue'];
            if (!is_null($fieldValue)) {
                $fieldType = $field['FieldType'];
                if (is_array($fieldType)) {
                    if ($this->_isComplexType($fieldType[0])) {
                        foreach ($fieldValue as $item) {
                            $xml .= "<$fieldName>";
                            $xml .= $item->_toXMLFragment();
                            $xml .= "</$fieldName>";
                        }
                    } else {
                        foreach ($fieldValue as $item) {
                            $xml .= "<$fieldName>";
                            $xml .= $this->_escapeXML($item);
                            $xml .= "</$fieldName>";
                        }
                    }
                } else {
                    if ($this->_isComplexType($fieldType)) {
                        $xml .= "<$fieldName>";
                        $xml .= $fieldValue->_toXMLFragment();
                        $xml .= "</$fieldName>";
                    } else {
                        $xml .= "<$fieldName>";
                        $xml .= $this->_escapeXML($fieldValue);
                        $xml .= "</$fieldName>";
                    }
                }
            }
        }
        return $xml;
    }


    /**
     * Escape special XML characters
     * 
     * @param string $str unescaped xml string
     * 
     * @return string with escaped XML characters
     */
    private function _escapeXML($str) 
    {
        $from = array( "&", "<", ">", "'", "\""); 
        $to = array( "&amp;", "&lt;", "&gt;", "&#039;", "&quot;");
        return str_replace($from, $to, $str); 
    }


    
    /**
     * Construct from DOMElement 
     * 
     * This function iterates over object fields and queries XML 
     * for corresponding tag value. If query succeeds, value extracted 
     * from xml, and field value properly constructed based on field type. 
     *
     * Field types defined as arrays always constructed as arrays,
     * even if XML contains a single element - to make sure that
     * data structure is predictable, and no is_array checks are
     * required.
     * 
     * @param DOMElement $dom XML element to construct from
     * 
     * @return void
     */
    private function _fromDOMElement(DOMElement $dom)
    {
        $xpath = new DOMXPath($dom->ownerDocument);
        $xpath->registerNamespace(
            'a', 
            self::getNamespace()
        );
        
        foreach ($this->fields as $fieldName => $field) {
            $fieldType = $field['FieldType'];   
            if (is_array($fieldType)) {
                if ($this->_isComplexType($fieldType[0])) {
                    $elements = $xpath->query("//*[local-name()='$fieldName']", $dom);
                    if ($elements->length >= 1) {
                        include_once str_replace(
                            '_',
                            DIRECTORY_SEPARATOR,
                            $fieldType[0]
                        ) . ".php";
                        foreach ($elements as $element) {
                            $this->fields[$fieldName]['FieldValue'][] 
                                = new $fieldType[0]($element);
                        }
                    } 
                } else {
                    $elements = $xpath->query("//*[local-name()='$fieldName']", $dom);
                    if ($elements->length >= 1) {
                        foreach ($elements as $element) {
                            $text = $xpath->query('./text()', $element);
                            $this->fields[$fieldName]['FieldValue'][] 
                                = $text->item(0)->data;
                        }
                    }  
                }
            } else {
                if ($this->_isComplexType($fieldType)) {
                    $elements = $xpath->query("//*[local-name()='$fieldName']", $dom);
                    if ($elements->length == 1) {
                        include_once str_replace(
                            '_',
                            DIRECTORY_SEPARATOR,
                            $fieldType
                        ) . ".php";
                        $this->fields[$fieldName]['FieldValue'] 
                            = new $fieldType($elements->item(0));
                    }   
                } else {
                    $element = $xpath->query("./*[local-name()='$fieldName']/text()", $dom);
                    if ($element->length >= 1) {
                        $this->fields[$fieldName]['FieldValue'] 
                            = $element->item(0)->data;
                    }
                    $attribute = $xpath->query("./@$fieldName", $dom);
                    if ($attribute->length == 1) {
                        $this->fields[$fieldName]['FieldValue'] 
                            = $attribute->item(0)->nodeValue;
                        if (isset ($this->fields['Value'])) {
                            $parentNode = $attribute->item(0)->parentNode;
                            $this->fields['Value']['FieldValue'] 
                                = $parentNode->nodeValue;
                        }
                    }

                }
            }
        }
    }


    /**
     * Construct from Associative Array
     * 
     * @param array $array associative array to construct from
     * 
     * @return void
     */
    private function _fromAssociativeArray(array $array)
    {
        foreach ($this->fields as $fieldName => $field) {
            $fieldType = $field['FieldType'];   
            if (is_array($fieldType)) {
                if ($this->_isComplexType($fieldType[0])) {
                    if (array_key_exists($fieldName, $array)) { 
                        $elements = $array[$fieldName];
                        if (!$this->_isNumericArray($elements)) {
                            $elements =  array($elements);    
                        }
                        if (count($elements) >= 1) {
                            include_once str_replace(
                                '_',
                                DIRECTORY_SEPARATOR,
                                $fieldType[0]
                            ) . ".php";
                            foreach ($elements as $element) {
                                $this->fields[$fieldName]['FieldValue'][] 
                                    = new $fieldType[0]($element);
                            }
                        }
                    } 
                } else {
                    if (array_key_exists($fieldName, $array)) {
                        $elements = $array[$fieldName];
                        if (!$this->_isNumericArray($elements)) {
                            $elements =  array($elements);    
                        }
                        if (count($elements) >= 1) {
                            foreach ($elements as $element) {
                                $this->fields[$fieldName]['FieldValue'][]
                                    = $element;
                            }
                        }  
                    }
                }
            } else {
                if ($this->_isComplexType($fieldType)) {
                    if (array_key_exists($fieldName, $array)) {
                        include_once str_replace(
                            '_',
                            DIRECTORY_SEPARATOR,
                            $fieldType
                        ) . ".php";
                        $this->fields[$fieldName]['FieldValue'] 
                            = new $fieldType($array[$fieldName]);
                    }   
                } else {
                    if (array_key_exists($fieldName, $array)) {
                        $this->fields[$fieldName]['FieldValue'] 
                            = $array[$fieldName];
                    }
                }
            }
        }
    }



    /**
     * Determines if field is complex type
     * 
     * @param string $fieldType field type name
     * 
     * @return void
     */
    private function _isComplexType ($fieldType) 
    {
        return preg_match('/^OffAmazonPayments.*_Model_/', $fieldType);
    }

    /**
     * Checks  whether passed variable is an associative array
     *
     * @param mixed $var value to check
     * 
     * @return TRUE if passed variable is an associative array
     */
    private function _isAssociativeArray($var) 
    {
        return is_array($var) && array_keys($var) !== range(0, sizeof($var) - 1);
    }

    /**
     * Checks  whether passed variable is DOMElement
     *
     * @param mixed $var value to check
     * 
     * @return TRUE if passed variable is DOMElement
     */
    private function _isDOMElement($var) 
    {
        return $var instanceof DOMElement;
    }

    /**
     * Checks  whether passed variable is numeric array
     *
     * @param mixed $var value to check
     * 
     * @return TRUE if passed variable is an numeric array
     */
    protected function isNumericArray($var) 
    {
        return is_array($var) && array_keys($var) === range(0, sizeof($var) - 1);
    }
    
    /**
     * Returns the namespace for the xml
     * 
     * @return string xml namespace
     */
    protected static function getNamespace()
    {
        return "https://mws.amazonservices.com/ipn/OffAmazonPayments/2013-01-01";
    }
}
?>