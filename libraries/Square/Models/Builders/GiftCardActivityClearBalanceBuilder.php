<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\GiftCardActivityClearBalance;

/**
 * Builder for model GiftCardActivityClearBalance
 *
 * @see GiftCardActivityClearBalance
 */
class GiftCardActivityClearBalanceBuilder
{
    /**
     * @var GiftCardActivityClearBalance
     */
    private $instance;

    private function __construct(GiftCardActivityClearBalance $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Gift Card Activity Clear Balance Builder object.
     *
     * @param string $reason
     */
    public static function init(string $reason): self
    {
        return new self(new GiftCardActivityClearBalance($reason));
    }

    /**
     * Initializes a new Gift Card Activity Clear Balance object.
     */
    public function build(): GiftCardActivityClearBalance
    {
        return CoreHelper::clone($this->instance);
    }
}
