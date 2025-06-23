<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\V1PhoneNumber;

/**
 * Builder for model V1PhoneNumber
 *
 * @see V1PhoneNumber
 */
class V1PhoneNumberBuilder
{
    /**
     * @var V1PhoneNumber
     */
    private $instance;

    private function __construct(V1PhoneNumber $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new V1 Phone Number Builder object.
     *
     * @param string $callingCode
     * @param string $number
     */
    public static function init(string $callingCode, string $number): self
    {
        return new self(new V1PhoneNumber($callingCode, $number));
    }

    /**
     * Initializes a new V1 Phone Number object.
     */
    public function build(): V1PhoneNumber
    {
        return CoreHelper::clone($this->instance);
    }
}
