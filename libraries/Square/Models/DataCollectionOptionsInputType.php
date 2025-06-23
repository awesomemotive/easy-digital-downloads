<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models;

/**
 * Describes the input type of the data.
 */
class DataCollectionOptionsInputType
{
    /**
     * This value is used to represent an input text that contains a email validation on the
     * client.
     */
    public const EMAIL = 'EMAIL';

    /**
     * This value is used to represent an input text that contains a phone number validation on
     * the client.
     */
    public const PHONE_NUMBER = 'PHONE_NUMBER';
}
