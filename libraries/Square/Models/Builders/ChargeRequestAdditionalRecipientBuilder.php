<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ChargeRequestAdditionalRecipient;
use EDD\Vendor\Square\Models\Money;

/**
 * Builder for model ChargeRequestAdditionalRecipient
 *
 * @see ChargeRequestAdditionalRecipient
 */
class ChargeRequestAdditionalRecipientBuilder
{
    /**
     * @var ChargeRequestAdditionalRecipient
     */
    private $instance;

    private function __construct(ChargeRequestAdditionalRecipient $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Charge Request Additional Recipient Builder object.
     *
     * @param string $locationId
     * @param string $description
     * @param Money $amountMoney
     */
    public static function init(string $locationId, string $description, Money $amountMoney): self
    {
        return new self(new ChargeRequestAdditionalRecipient($locationId, $description, $amountMoney));
    }

    /**
     * Initializes a new Charge Request Additional Recipient object.
     */
    public function build(): ChargeRequestAdditionalRecipient
    {
        return CoreHelper::clone($this->instance);
    }
}
