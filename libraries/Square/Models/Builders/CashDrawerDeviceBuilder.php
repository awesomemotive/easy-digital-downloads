<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CashDrawerDevice;

/**
 * Builder for model CashDrawerDevice
 *
 * @see CashDrawerDevice
 */
class CashDrawerDeviceBuilder
{
    /**
     * @var CashDrawerDevice
     */
    private $instance;

    private function __construct(CashDrawerDevice $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Cash Drawer Device Builder object.
     */
    public static function init(): self
    {
        return new self(new CashDrawerDevice());
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
     * Initializes a new Cash Drawer Device object.
     */
    public function build(): CashDrawerDevice
    {
        return CoreHelper::clone($this->instance);
    }
}
