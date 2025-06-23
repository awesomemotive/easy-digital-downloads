<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\GiftCard;
use EDD\Vendor\Square\Models\ListGiftCardsResponse;

/**
 * Builder for model ListGiftCardsResponse
 *
 * @see ListGiftCardsResponse
 */
class ListGiftCardsResponseBuilder
{
    /**
     * @var ListGiftCardsResponse
     */
    private $instance;

    private function __construct(ListGiftCardsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Gift Cards Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListGiftCardsResponse());
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
     * Sets gift cards field.
     *
     * @param GiftCard[]|null $value
     */
    public function giftCards(?array $value): self
    {
        $this->instance->setGiftCards($value);
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
     * Initializes a new List Gift Cards Response object.
     */
    public function build(): ListGiftCardsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
