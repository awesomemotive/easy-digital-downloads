<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ReceiptOptions;

/**
 * Builder for model ReceiptOptions
 *
 * @see ReceiptOptions
 */
class ReceiptOptionsBuilder
{
    /**
     * @var ReceiptOptions
     */
    private $instance;

    private function __construct(ReceiptOptions $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Receipt Options Builder object.
     *
     * @param string $paymentId
     */
    public static function init(string $paymentId): self
    {
        return new self(new ReceiptOptions($paymentId));
    }

    /**
     * Sets print only field.
     *
     * @param bool|null $value
     */
    public function printOnly(?bool $value): self
    {
        $this->instance->setPrintOnly($value);
        return $this;
    }

    /**
     * Unsets print only field.
     */
    public function unsetPrintOnly(): self
    {
        $this->instance->unsetPrintOnly();
        return $this;
    }

    /**
     * Sets is duplicate field.
     *
     * @param bool|null $value
     */
    public function isDuplicate(?bool $value): self
    {
        $this->instance->setIsDuplicate($value);
        return $this;
    }

    /**
     * Unsets is duplicate field.
     */
    public function unsetIsDuplicate(): self
    {
        $this->instance->unsetIsDuplicate();
        return $this;
    }

    /**
     * Initializes a new Receipt Options object.
     */
    public function build(): ReceiptOptions
    {
        return CoreHelper::clone($this->instance);
    }
}
