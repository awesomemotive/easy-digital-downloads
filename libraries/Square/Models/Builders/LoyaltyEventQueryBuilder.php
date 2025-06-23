<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyEventFilter;
use EDD\Vendor\Square\Models\LoyaltyEventQuery;

/**
 * Builder for model LoyaltyEventQuery
 *
 * @see LoyaltyEventQuery
 */
class LoyaltyEventQueryBuilder
{
    /**
     * @var LoyaltyEventQuery
     */
    private $instance;

    private function __construct(LoyaltyEventQuery $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Event Query Builder object.
     */
    public static function init(): self
    {
        return new self(new LoyaltyEventQuery());
    }

    /**
     * Sets filter field.
     *
     * @param LoyaltyEventFilter|null $value
     */
    public function filter(?LoyaltyEventFilter $value): self
    {
        $this->instance->setFilter($value);
        return $this;
    }

    /**
     * Initializes a new Loyalty Event Query object.
     */
    public function build(): LoyaltyEventQuery
    {
        return CoreHelper::clone($this->instance);
    }
}
