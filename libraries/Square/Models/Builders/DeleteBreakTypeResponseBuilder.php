<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeleteBreakTypeResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model DeleteBreakTypeResponse
 *
 * @see DeleteBreakTypeResponse
 */
class DeleteBreakTypeResponseBuilder
{
    /**
     * @var DeleteBreakTypeResponse
     */
    private $instance;

    private function __construct(DeleteBreakTypeResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Delete Break Type Response Builder object.
     */
    public static function init(): self
    {
        return new self(new DeleteBreakTypeResponse());
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
     * Initializes a new Delete Break Type Response object.
     */
    public function build(): DeleteBreakTypeResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
