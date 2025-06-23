<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\TenderCashDetails;

/**
 * Builder for model TenderCashDetails
 *
 * @see TenderCashDetails
 */
class TenderCashDetailsBuilder
{
    /**
     * @var TenderCashDetails
     */
    private $instance;

    private function __construct(TenderCashDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Tender Cash Details Builder object.
     */
    public static function init(): self
    {
        return new self(new TenderCashDetails());
    }

    /**
     * Sets buyer tendered money field.
     *
     * @param Money|null $value
     */
    public function buyerTenderedMoney(?Money $value): self
    {
        $this->instance->setBuyerTenderedMoney($value);
        return $this;
    }

    /**
     * Sets change back money field.
     *
     * @param Money|null $value
     */
    public function changeBackMoney(?Money $value): self
    {
        $this->instance->setChangeBackMoney($value);
        return $this;
    }

    /**
     * Initializes a new Tender Cash Details object.
     */
    public function build(): TenderCashDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
