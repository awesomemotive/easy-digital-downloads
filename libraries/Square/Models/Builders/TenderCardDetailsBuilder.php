<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Card;
use EDD\Vendor\Square\Models\TenderCardDetails;

/**
 * Builder for model TenderCardDetails
 *
 * @see TenderCardDetails
 */
class TenderCardDetailsBuilder
{
    /**
     * @var TenderCardDetails
     */
    private $instance;

    private function __construct(TenderCardDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Tender Card Details Builder object.
     */
    public static function init(): self
    {
        return new self(new TenderCardDetails());
    }

    /**
     * Sets status field.
     *
     * @param string|null $value
     */
    public function status(?string $value): self
    {
        $this->instance->setStatus($value);
        return $this;
    }

    /**
     * Sets card field.
     *
     * @param Card|null $value
     */
    public function card(?Card $value): self
    {
        $this->instance->setCard($value);
        return $this;
    }

    /**
     * Sets entry method field.
     *
     * @param string|null $value
     */
    public function entryMethod(?string $value): self
    {
        $this->instance->setEntryMethod($value);
        return $this;
    }

    /**
     * Initializes a new Tender Card Details object.
     */
    public function build(): TenderCardDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
