<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ListLoyaltyPromotionsRequest;

/**
 * Builder for model ListLoyaltyPromotionsRequest
 *
 * @see ListLoyaltyPromotionsRequest
 */
class ListLoyaltyPromotionsRequestBuilder
{
    /**
     * @var ListLoyaltyPromotionsRequest
     */
    private $instance;

    private function __construct(ListLoyaltyPromotionsRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Loyalty Promotions Request Builder object.
     */
    public static function init(): self
    {
        return new self(new ListLoyaltyPromotionsRequest());
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
     * Unsets cursor field.
     */
    public function unsetCursor(): self
    {
        $this->instance->unsetCursor();
        return $this;
    }

    /**
     * Sets limit field.
     *
     * @param int|null $value
     */
    public function limit(?int $value): self
    {
        $this->instance->setLimit($value);
        return $this;
    }

    /**
     * Unsets limit field.
     */
    public function unsetLimit(): self
    {
        $this->instance->unsetLimit();
        return $this;
    }

    /**
     * Initializes a new List Loyalty Promotions Request object.
     */
    public function build(): ListLoyaltyPromotionsRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
