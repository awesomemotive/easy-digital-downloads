<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\PaginationCursor;

/**
 * Builder for model PaginationCursor
 *
 * @see PaginationCursor
 */
class PaginationCursorBuilder
{
    /**
     * @var PaginationCursor
     */
    private $instance;

    private function __construct(PaginationCursor $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Pagination Cursor Builder object.
     */
    public static function init(): self
    {
        return new self(new PaginationCursor());
    }

    /**
     * Sets order value field.
     *
     * @param string|null $value
     */
    public function orderValue(?string $value): self
    {
        $this->instance->setOrderValue($value);
        return $this;
    }

    /**
     * Unsets order value field.
     */
    public function unsetOrderValue(): self
    {
        $this->instance->unsetOrderValue();
        return $this;
    }

    /**
     * Initializes a new Pagination Cursor object.
     */
    public function build(): PaginationCursor
    {
        return CoreHelper::clone($this->instance);
    }
}
