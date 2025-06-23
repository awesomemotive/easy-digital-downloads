<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\BulkDeleteOrderCustomAttributesRequestDeleteCustomAttribute;

/**
 * Builder for model BulkDeleteOrderCustomAttributesRequestDeleteCustomAttribute
 *
 * @see BulkDeleteOrderCustomAttributesRequestDeleteCustomAttribute
 */
class BulkDeleteOrderCustomAttributesRequestDeleteCustomAttributeBuilder
{
    /**
     * @var BulkDeleteOrderCustomAttributesRequestDeleteCustomAttribute
     */
    private $instance;

    private function __construct(BulkDeleteOrderCustomAttributesRequestDeleteCustomAttribute $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Bulk Delete Order Custom Attributes Request Delete Custom Attribute Builder object.
     *
     * @param string $orderId
     */
    public static function init(string $orderId): self
    {
        return new self(new BulkDeleteOrderCustomAttributesRequestDeleteCustomAttribute($orderId));
    }

    /**
     * Sets key field.
     *
     * @param string|null $value
     */
    public function key(?string $value): self
    {
        $this->instance->setKey($value);
        return $this;
    }

    /**
     * Initializes a new Bulk Delete Order Custom Attributes Request Delete Custom Attribute object.
     */
    public function build(): BulkDeleteOrderCustomAttributesRequestDeleteCustomAttribute
    {
        return CoreHelper::clone($this->instance);
    }
}
