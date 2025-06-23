<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomerPreferences;

/**
 * Builder for model CustomerPreferences
 *
 * @see CustomerPreferences
 */
class CustomerPreferencesBuilder
{
    /**
     * @var CustomerPreferences
     */
    private $instance;

    private function __construct(CustomerPreferences $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Customer Preferences Builder object.
     */
    public static function init(): self
    {
        return new self(new CustomerPreferences());
    }

    /**
     * Sets email unsubscribed field.
     *
     * @param bool|null $value
     */
    public function emailUnsubscribed(?bool $value): self
    {
        $this->instance->setEmailUnsubscribed($value);
        return $this;
    }

    /**
     * Unsets email unsubscribed field.
     */
    public function unsetEmailUnsubscribed(): self
    {
        $this->instance->unsetEmailUnsubscribed();
        return $this;
    }

    /**
     * Initializes a new Customer Preferences object.
     */
    public function build(): CustomerPreferences
    {
        return CoreHelper::clone($this->instance);
    }
}
