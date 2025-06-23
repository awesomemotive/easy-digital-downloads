<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeleteCustomerRequest;

/**
 * Builder for model DeleteCustomerRequest
 *
 * @see DeleteCustomerRequest
 */
class DeleteCustomerRequestBuilder
{
    /**
     * @var DeleteCustomerRequest
     */
    private $instance;

    private function __construct(DeleteCustomerRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Delete Customer Request Builder object.
     */
    public static function init(): self
    {
        return new self(new DeleteCustomerRequest());
    }

    /**
     * Sets version field.
     *
     * @param int|null $value
     */
    public function version(?int $value): self
    {
        $this->instance->setVersion($value);
        return $this;
    }

    /**
     * Initializes a new Delete Customer Request object.
     */
    public function build(): DeleteCustomerRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
