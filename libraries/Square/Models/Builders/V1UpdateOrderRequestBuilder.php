<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\V1UpdateOrderRequest;

/**
 * Builder for model V1UpdateOrderRequest
 *
 * @see V1UpdateOrderRequest
 */
class V1UpdateOrderRequestBuilder
{
    /**
     * @var V1UpdateOrderRequest
     */
    private $instance;

    private function __construct(V1UpdateOrderRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new V1 Update Order Request Builder object.
     *
     * @param string $action
     */
    public static function init(string $action): self
    {
        return new self(new V1UpdateOrderRequest($action));
    }

    /**
     * Sets shipped tracking number field.
     *
     * @param string|null $value
     */
    public function shippedTrackingNumber(?string $value): self
    {
        $this->instance->setShippedTrackingNumber($value);
        return $this;
    }

    /**
     * Unsets shipped tracking number field.
     */
    public function unsetShippedTrackingNumber(): self
    {
        $this->instance->unsetShippedTrackingNumber();
        return $this;
    }

    /**
     * Sets completed note field.
     *
     * @param string|null $value
     */
    public function completedNote(?string $value): self
    {
        $this->instance->setCompletedNote($value);
        return $this;
    }

    /**
     * Unsets completed note field.
     */
    public function unsetCompletedNote(): self
    {
        $this->instance->unsetCompletedNote();
        return $this;
    }

    /**
     * Sets refunded note field.
     *
     * @param string|null $value
     */
    public function refundedNote(?string $value): self
    {
        $this->instance->setRefundedNote($value);
        return $this;
    }

    /**
     * Unsets refunded note field.
     */
    public function unsetRefundedNote(): self
    {
        $this->instance->unsetRefundedNote();
        return $this;
    }

    /**
     * Sets canceled note field.
     *
     * @param string|null $value
     */
    public function canceledNote(?string $value): self
    {
        $this->instance->setCanceledNote($value);
        return $this;
    }

    /**
     * Unsets canceled note field.
     */
    public function unsetCanceledNote(): self
    {
        $this->instance->unsetCanceledNote();
        return $this;
    }

    /**
     * Initializes a new V1 Update Order Request object.
     */
    public function build(): V1UpdateOrderRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
