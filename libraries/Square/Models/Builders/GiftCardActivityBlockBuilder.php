<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\GiftCardActivityBlock;

/**
 * Builder for model GiftCardActivityBlock
 *
 * @see GiftCardActivityBlock
 */
class GiftCardActivityBlockBuilder
{
    /**
     * @var GiftCardActivityBlock
     */
    private $instance;

    private function __construct(GiftCardActivityBlock $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Gift Card Activity Block Builder object.
     */
    public static function init(): self
    {
        return new self(new GiftCardActivityBlock());
    }

    /**
     * Initializes a new Gift Card Activity Block object.
     */
    public function build(): GiftCardActivityBlock
    {
        return CoreHelper::clone($this->instance);
    }
}
