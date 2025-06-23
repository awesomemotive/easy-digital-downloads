<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\QuickPay;

/**
 * Builder for model QuickPay
 *
 * @see QuickPay
 */
class QuickPayBuilder
{
    /**
     * @var QuickPay
     */
    private $instance;

    private function __construct(QuickPay $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Quick Pay Builder object.
     *
     * @param string $name
     * @param Money $priceMoney
     * @param string $locationId
     */
    public static function init(string $name, Money $priceMoney, string $locationId): self
    {
        return new self(new QuickPay($name, $priceMoney, $locationId));
    }

    /**
     * Initializes a new Quick Pay object.
     */
    public function build(): QuickPay
    {
        return CoreHelper::clone($this->instance);
    }
}
