<?php

namespace EDD\Vendor\Core\Utils;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Rs\Json\Pointer;

class JsonPointerValue
{
    /**
     * Get the value from a JSON pointer.
     *
     * @param string $jsonObj The JSON string
     * @param string $pointer The pointer string
     * @return mixed The value at the pointer, serialized if object, or empty string on error
     */
    public static function getJsonPointerValue(string $jsonObj, string $pointer)
    {
        if (trim($jsonObj) === '' || trim($pointer) === '') {
            return "";
        }

        try {
            $jsonPointer = new Pointer($jsonObj);
            $pointerValue = $jsonPointer->get($pointer);

            if (is_object($pointerValue)) {
                $pointerValue = CoreHelper::serialize($pointerValue);
            }

            return $pointerValue;
        } catch (\Exception $ex) {
            return "";
        }
    }
}
