<?php

declare(strict_types=1);

namespace EDD\Vendor\Core\Exceptions;

use InvalidArgumentException;

/**
 * Authentication Validation Exception.
 */
class AuthValidationException extends InvalidArgumentException
{
    private const ERROR_MESSAGE_PREFIX = "Following authentication credentials are required:\n-> ";

    /**
     * Initialize a new instance of AuthValidationException
     *
     * @param string[] $errors An array of errors in authentication validation
     */
    public static function init(array $errors): AuthValidationException
    {
        return new self(self::ERROR_MESSAGE_PREFIX . join("\n-> ", $errors));
    }
}
