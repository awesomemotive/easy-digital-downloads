<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\LoyaltyEventAdjustPoints;

/**
 * Builder for model LoyaltyEventAdjustPoints
 *
 * @see LoyaltyEventAdjustPoints
 */
class LoyaltyEventAdjustPointsBuilder
{
    /**
     * @var LoyaltyEventAdjustPoints
     */
    private $instance;

    private function __construct(LoyaltyEventAdjustPoints $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Loyalty Event Adjust Points Builder object.
     *
     * @param int $points
     */
    public static function init(int $points): self
    {
        return new self(new LoyaltyEventAdjustPoints($points));
    }

    /**
     * Sets loyalty program id field.
     *
     * @param string|null $value
     */
    public function loyaltyProgramId(?string $value): self
    {
        $this->instance->setLoyaltyProgramId($value);
        return $this;
    }

    /**
     * Sets reason field.
     *
     * @param string|null $value
     */
    public function reason(?string $value): self
    {
        $this->instance->setReason($value);
        return $this;
    }

    /**
     * Unsets reason field.
     */
    public function unsetReason(): self
    {
        $this->instance->unsetReason();
        return $this;
    }

    /**
     * Initializes a new Loyalty Event Adjust Points object.
     */
    public function build(): LoyaltyEventAdjustPoints
    {
        return CoreHelper::clone($this->instance);
    }
}
