<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ACHDetails;

/**
 * Builder for model ACHDetails
 *
 * @see ACHDetails
 */
class ACHDetailsBuilder
{
    /**
     * @var ACHDetails
     */
    private $instance;

    private function __construct(ACHDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new ACH Details Builder object.
     */
    public static function init(): self
    {
        return new self(new ACHDetails());
    }

    /**
     * Sets routing number field.
     *
     * @param string|null $value
     */
    public function routingNumber(?string $value): self
    {
        $this->instance->setRoutingNumber($value);
        return $this;
    }

    /**
     * Unsets routing number field.
     */
    public function unsetRoutingNumber(): self
    {
        $this->instance->unsetRoutingNumber();
        return $this;
    }

    /**
     * Sets account number suffix field.
     *
     * @param string|null $value
     */
    public function accountNumberSuffix(?string $value): self
    {
        $this->instance->setAccountNumberSuffix($value);
        return $this;
    }

    /**
     * Unsets account number suffix field.
     */
    public function unsetAccountNumberSuffix(): self
    {
        $this->instance->unsetAccountNumberSuffix();
        return $this;
    }

    /**
     * Sets account type field.
     *
     * @param string|null $value
     */
    public function accountType(?string $value): self
    {
        $this->instance->setAccountType($value);
        return $this;
    }

    /**
     * Unsets account type field.
     */
    public function unsetAccountType(): self
    {
        $this->instance->unsetAccountType();
        return $this;
    }

    /**
     * Initializes a new ACH Details object.
     */
    public function build(): ACHDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
