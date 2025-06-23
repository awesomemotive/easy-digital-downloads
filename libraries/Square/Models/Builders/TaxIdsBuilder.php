<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\TaxIds;

/**
 * Builder for model TaxIds
 *
 * @see TaxIds
 */
class TaxIdsBuilder
{
    /**
     * @var TaxIds
     */
    private $instance;

    private function __construct(TaxIds $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Tax Ids Builder object.
     */
    public static function init(): self
    {
        return new self(new TaxIds());
    }

    /**
     * Sets eu vat field.
     *
     * @param string|null $value
     */
    public function euVat(?string $value): self
    {
        $this->instance->setEuVat($value);
        return $this;
    }

    /**
     * Sets fr siret field.
     *
     * @param string|null $value
     */
    public function frSiret(?string $value): self
    {
        $this->instance->setFrSiret($value);
        return $this;
    }

    /**
     * Sets fr naf field.
     *
     * @param string|null $value
     */
    public function frNaf(?string $value): self
    {
        $this->instance->setFrNaf($value);
        return $this;
    }

    /**
     * Sets es nif field.
     *
     * @param string|null $value
     */
    public function esNif(?string $value): self
    {
        $this->instance->setEsNif($value);
        return $this;
    }

    /**
     * Sets jp qii field.
     *
     * @param string|null $value
     */
    public function jpQii(?string $value): self
    {
        $this->instance->setJpQii($value);
        return $this;
    }

    /**
     * Initializes a new Tax Ids object.
     */
    public function build(): TaxIds
    {
        return CoreHelper::clone($this->instance);
    }
}
