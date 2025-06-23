<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\SearchVendorsResponse;
use EDD\Vendor\Square\Models\Vendor;

/**
 * Builder for model SearchVendorsResponse
 *
 * @see SearchVendorsResponse
 */
class SearchVendorsResponseBuilder
{
    /**
     * @var SearchVendorsResponse
     */
    private $instance;

    private function __construct(SearchVendorsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Vendors Response Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchVendorsResponse());
    }

    /**
     * Sets errors field.
     *
     * @param Error[]|null $value
     */
    public function errors(?array $value): self
    {
        $this->instance->setErrors($value);
        return $this;
    }

    /**
     * Sets vendors field.
     *
     * @param Vendor[]|null $value
     */
    public function vendors(?array $value): self
    {
        $this->instance->setVendors($value);
        return $this;
    }

    /**
     * Sets cursor field.
     *
     * @param string|null $value
     */
    public function cursor(?string $value): self
    {
        $this->instance->setCursor($value);
        return $this;
    }

    /**
     * Initializes a new Search Vendors Response object.
     */
    public function build(): SearchVendorsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
