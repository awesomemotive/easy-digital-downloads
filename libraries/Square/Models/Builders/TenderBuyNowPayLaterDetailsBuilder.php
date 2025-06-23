<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\TenderBuyNowPayLaterDetails;

/**
 * Builder for model TenderBuyNowPayLaterDetails
 *
 * @see TenderBuyNowPayLaterDetails
 */
class TenderBuyNowPayLaterDetailsBuilder
{
    /**
     * @var TenderBuyNowPayLaterDetails
     */
    private $instance;

    private function __construct(TenderBuyNowPayLaterDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Tender Buy Now Pay Later Details Builder object.
     */
    public static function init(): self
    {
        return new self(new TenderBuyNowPayLaterDetails());
    }

    /**
     * Sets buy now pay later brand field.
     *
     * @param string|null $value
     */
    public function buyNowPayLaterBrand(?string $value): self
    {
        $this->instance->setBuyNowPayLaterBrand($value);
        return $this;
    }

    /**
     * Sets status field.
     *
     * @param string|null $value
     */
    public function status(?string $value): self
    {
        $this->instance->setStatus($value);
        return $this;
    }

    /**
     * Initializes a new Tender Buy Now Pay Later Details object.
     */
    public function build(): TenderBuyNowPayLaterDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
