<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\ListSitesResponse;
use EDD\Vendor\Square\Models\Site;

/**
 * Builder for model ListSitesResponse
 *
 * @see ListSitesResponse
 */
class ListSitesResponseBuilder
{
    /**
     * @var ListSitesResponse
     */
    private $instance;

    private function __construct(ListSitesResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Sites Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListSitesResponse());
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
     * Sets sites field.
     *
     * @param Site[]|null $value
     */
    public function sites(?array $value): self
    {
        $this->instance->setSites($value);
        return $this;
    }

    /**
     * Initializes a new List Sites Response object.
     */
    public function build(): ListSitesResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
