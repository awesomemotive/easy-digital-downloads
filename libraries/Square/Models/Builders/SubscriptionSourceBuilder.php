<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SubscriptionSource;

/**
 * Builder for model SubscriptionSource
 *
 * @see SubscriptionSource
 */
class SubscriptionSourceBuilder
{
    /**
     * @var SubscriptionSource
     */
    private $instance;

    private function __construct(SubscriptionSource $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Subscription Source Builder object.
     */
    public static function init(): self
    {
        return new self(new SubscriptionSource());
    }

    /**
     * Sets name field.
     *
     * @param string|null $value
     */
    public function name(?string $value): self
    {
        $this->instance->setName($value);
        return $this;
    }

    /**
     * Unsets name field.
     */
    public function unsetName(): self
    {
        $this->instance->unsetName();
        return $this;
    }

    /**
     * Initializes a new Subscription Source object.
     */
    public function build(): SubscriptionSource
    {
        return CoreHelper::clone($this->instance);
    }
}
