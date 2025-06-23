<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SubscriptionEventInfo;

/**
 * Builder for model SubscriptionEventInfo
 *
 * @see SubscriptionEventInfo
 */
class SubscriptionEventInfoBuilder
{
    /**
     * @var SubscriptionEventInfo
     */
    private $instance;

    private function __construct(SubscriptionEventInfo $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Subscription Event Info Builder object.
     */
    public static function init(): self
    {
        return new self(new SubscriptionEventInfo());
    }

    /**
     * Sets detail field.
     *
     * @param string|null $value
     */
    public function detail(?string $value): self
    {
        $this->instance->setDetail($value);
        return $this;
    }

    /**
     * Unsets detail field.
     */
    public function unsetDetail(): self
    {
        $this->instance->unsetDetail();
        return $this;
    }

    /**
     * Sets code field.
     *
     * @param string|null $value
     */
    public function code(?string $value): self
    {
        $this->instance->setCode($value);
        return $this;
    }

    /**
     * Initializes a new Subscription Event Info object.
     */
    public function build(): SubscriptionEventInfo
    {
        return CoreHelper::clone($this->instance);
    }
}
