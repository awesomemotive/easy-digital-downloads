<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Merchant;

/**
 * Builder for model Merchant
 *
 * @see Merchant
 */
class MerchantBuilder
{
    /**
     * @var Merchant
     */
    private $instance;

    private function __construct(Merchant $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Merchant Builder object.
     *
     * @param string $country
     */
    public static function init(string $country): self
    {
        return new self(new Merchant($country));
    }

    /**
     * Sets id field.
     *
     * @param string|null $value
     */
    public function id(?string $value): self
    {
        $this->instance->setId($value);
        return $this;
    }

    /**
     * Sets business name field.
     *
     * @param string|null $value
     */
    public function businessName(?string $value): self
    {
        $this->instance->setBusinessName($value);
        return $this;
    }

    /**
     * Unsets business name field.
     */
    public function unsetBusinessName(): self
    {
        $this->instance->unsetBusinessName();
        return $this;
    }

    /**
     * Sets language code field.
     *
     * @param string|null $value
     */
    public function languageCode(?string $value): self
    {
        $this->instance->setLanguageCode($value);
        return $this;
    }

    /**
     * Unsets language code field.
     */
    public function unsetLanguageCode(): self
    {
        $this->instance->unsetLanguageCode();
        return $this;
    }

    /**
     * Sets currency field.
     *
     * @param string|null $value
     */
    public function currency(?string $value): self
    {
        $this->instance->setCurrency($value);
        return $this;
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
     * Sets main location id field.
     *
     * @param string|null $value
     */
    public function mainLocationId(?string $value): self
    {
        $this->instance->setMainLocationId($value);
        return $this;
    }

    /**
     * Unsets main location id field.
     */
    public function unsetMainLocationId(): self
    {
        $this->instance->unsetMainLocationId();
        return $this;
    }

    /**
     * Sets created at field.
     *
     * @param string|null $value
     */
    public function createdAt(?string $value): self
    {
        $this->instance->setCreatedAt($value);
        return $this;
    }

    /**
     * Initializes a new Merchant object.
     */
    public function build(): Merchant
    {
        return CoreHelper::clone($this->instance);
    }
}
