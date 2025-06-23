<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\GiftCardActivityUnblock;

/**
 * Builder for model GiftCardActivityUnblock
 *
 * @see GiftCardActivityUnblock
 */
class GiftCardActivityUnblockBuilder
{
    /**
     * @var GiftCardActivityUnblock
     */
    private $instance;

    private function __construct(GiftCardActivityUnblock $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Gift Card Activity Unblock Builder object.
     */
    public static function init(): self
    {
        return new self(new GiftCardActivityUnblock());
    }

    /**
     * Initializes a new Gift Card Activity Unblock object.
     */
    public function build(): GiftCardActivityUnblock
    {
        return CoreHelper::clone($this->instance);
    }
}
