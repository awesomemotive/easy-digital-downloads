<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BankAccount;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\GetBankAccountResponse;

/**
 * Builder for model GetBankAccountResponse
 *
 * @see GetBankAccountResponse
 */
class GetBankAccountResponseBuilder
{
    /**
     * @var GetBankAccountResponse
     */
    private $instance;

    private function __construct(GetBankAccountResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Get Bank Account Response Builder object.
     */
    public static function init(): self
    {
        return new self(new GetBankAccountResponse());
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
     * Sets bank account field.
     *
     * @param BankAccount|null $value
     */
    public function bankAccount(?BankAccount $value): self
    {
        $this->instance->setBankAccount($value);
        return $this;
    }

    /**
     * Initializes a new Get Bank Account Response object.
     */
    public function build(): GetBankAccountResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
