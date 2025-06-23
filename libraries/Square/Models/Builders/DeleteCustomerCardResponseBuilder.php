<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeleteCustomerCardResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model DeleteCustomerCardResponse
 *
 * @see DeleteCustomerCardResponse
 */
class DeleteCustomerCardResponseBuilder
{
    /**
     * @var DeleteCustomerCardResponse
     */
    private $instance;

    private function __construct(DeleteCustomerCardResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Delete Customer Card Response Builder object.
     */
    public static function init(): self
    {
        return new self(new DeleteCustomerCardResponse());
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
     * Initializes a new Delete Customer Card Response object.
     */
    public function build(): DeleteCustomerCardResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
