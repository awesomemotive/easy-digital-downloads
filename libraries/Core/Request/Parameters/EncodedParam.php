<?php

declare(strict_types=1);

namespace EDD\Vendor\Core\Request\Parameters;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestArraySerialization;

abstract class EncodedParam extends Parameter
{
    protected $format = RequestArraySerialization::INDEXED;
    protected function __construct(string $key, $value, string $typeName)
    {
        parent::__construct($key, $value, $typeName);
    }

    /**
     * Generate URL-encoded query string from the giving list of parameters.
     *
     * @param  array  $data   Input data to be encoded
     * @param  string $parent Parent name accessor
     *
     * @return string Url encoded query string
     */
    protected function httpBuildQuery(array $data, string $format, string $parent = ''): string
    {
        if ($format == RequestArraySerialization::INDEXED) {
            return http_build_query($data);
        }
        $separatorFormat = in_array($format, [
            RequestArraySerialization::TSV,
            RequestArraySerialization::PSV,
            RequestArraySerialization::CSV
        ], true);
        $innerAssociativeArray = !empty($parent) && CoreHelper::isAssociative($data);
        $first = true;
        $separator = substr($format, strpos($format, ':') + 1);
        $result = [];
        array_walk($data, function (
            $value,
            $key
        ) use (
            &$result,
            &$first,
            $parent,
            $format,
            $separatorFormat,
            $separator,
            $innerAssociativeArray
        ): void {
            if (is_null($value)) {
                return;
            }
            $key = $this->generateKeyWithParent($format, $key, $parent, is_scalar($value));
            if (is_array($value)) {
                $result[] = $this->httpBuildQuery($value, $format, $key);
                return;
            }
            if (!$separatorFormat) {
                $result[] = http_build_query([$key => $value]);
                return;
            }
            $associativePartParam = "&" . http_build_query([$key => $value]);
            if ($first) {
                $result[] = $associativePartParam;
                $first = false;
                return;
            }
            if ($innerAssociativeArray) {
                $result[] = $associativePartParam;
                return;
            }
            $result[] = urlencode($separator) . urlencode(strval($value));
        });
        return implode($separatorFormat ? '' : '&', $result);
    }

    private function generateKeyWithParent(string $format, $key, string $parent, bool $isScalarValue): string
    {
        if (empty($parent)) {
            return $key;
        }
        $keyForCurrentNonScalarNonAssociativeArray = "{$parent}[$key]";
        if (!is_numeric($key)) {
            return $keyForCurrentNonScalarNonAssociativeArray;
        }
        if (!$isScalarValue) {
            return $keyForCurrentNonScalarNonAssociativeArray;
        }
        return $parent . $this->getKeyPostFix($format);
    }

    private function getKeyPostFix(string $format): string
    {
        if ($format == RequestArraySerialization::UN_INDEXED) {
            return '[]';
        }
        return '';
    }
}
