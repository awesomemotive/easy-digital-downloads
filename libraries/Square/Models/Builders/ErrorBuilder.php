<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model Error
 *
 * @see Error
 */
class ErrorBuilder
{
    /**
     * @var Error
     */
    private $instance;

    private function __construct(Error $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Error Builder object.
     *
     * @param string $category
     * @param string $code
     */
    public static function init(string $category, string $code): self
    {
        return new self(new Error($category, $code));
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
     * Sets field field.
     *
     * @param string|null $value
     */
    public function field(?string $value): self
    {
        $this->instance->setField($value);
        return $this;
    }

    /**
     * Initializes a new Error object.
     */
    public function build(): Error
    {
        return CoreHelper::clone($this->instance);
    }
}
