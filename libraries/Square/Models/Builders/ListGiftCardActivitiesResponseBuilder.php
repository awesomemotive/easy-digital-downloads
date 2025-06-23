<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\GiftCardActivity;
use EDD\Vendor\Square\Models\ListGiftCardActivitiesResponse;

/**
 * Builder for model ListGiftCardActivitiesResponse
 *
 * @see ListGiftCardActivitiesResponse
 */
class ListGiftCardActivitiesResponseBuilder
{
    /**
     * @var ListGiftCardActivitiesResponse
     */
    private $instance;

    private function __construct(ListGiftCardActivitiesResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new List Gift Card Activities Response Builder object.
     */
    public static function init(): self
    {
        return new self(new ListGiftCardActivitiesResponse());
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
     * Sets gift card activities field.
     *
     * @param GiftCardActivity[]|null $value
     */
    public function giftCardActivities(?array $value): self
    {
        $this->instance->setGiftCardActivities($value);
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
     * Initializes a new List Gift Card Activities Response object.
     */
    public function build(): ListGiftCardActivitiesResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
