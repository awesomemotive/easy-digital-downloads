<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DestinationDetailsExternalRefundDetails;

/**
 * Builder for model DestinationDetailsExternalRefundDetails
 *
 * @see DestinationDetailsExternalRefundDetails
 */
class DestinationDetailsExternalRefundDetailsBuilder
{
    /**
     * @var DestinationDetailsExternalRefundDetails
     */
    private $instance;

    private function __construct(DestinationDetailsExternalRefundDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Destination Details External Refund Details Builder object.
     *
     * @param string $type
     * @param string $source
     */
    public static function init(string $type, string $source): self
    {
        return new self(new DestinationDetailsExternalRefundDetails($type, $source));
    }

    /**
     * Sets source id field.
     *
     * @param string|null $value
     */
    public function sourceId(?string $value): self
    {
        $this->instance->setSourceId($value);
        return $this;
    }

    /**
     * Unsets source id field.
     */
    public function unsetSourceId(): self
    {
        $this->instance->unsetSourceId();
        return $this;
    }

    /**
     * Initializes a new Destination Details External Refund Details object.
     */
    public function build(): DestinationDetailsExternalRefundDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
