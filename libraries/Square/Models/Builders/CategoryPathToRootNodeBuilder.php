<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CategoryPathToRootNode;

/**
 * Builder for model CategoryPathToRootNode
 *
 * @see CategoryPathToRootNode
 */
class CategoryPathToRootNodeBuilder
{
    /**
     * @var CategoryPathToRootNode
     */
    private $instance;

    private function __construct(CategoryPathToRootNode $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Category Path To Root Node Builder object.
     */
    public static function init(): self
    {
        return new self(new CategoryPathToRootNode());
    }

    /**
     * Sets category id field.
     *
     * @param string|null $value
     */
    public function categoryId(?string $value): self
    {
        $this->instance->setCategoryId($value);
        return $this;
    }

    /**
     * Unsets category id field.
     */
    public function unsetCategoryId(): self
    {
        $this->instance->unsetCategoryId();
        return $this;
    }

    /**
     * Sets category name field.
     *
     * @param string|null $value
     */
    public function categoryName(?string $value): self
    {
        $this->instance->setCategoryName($value);
        return $this;
    }

    /**
     * Unsets category name field.
     */
    public function unsetCategoryName(): self
    {
        $this->instance->unsetCategoryName();
        return $this;
    }

    /**
     * Initializes a new Category Path To Root Node object.
     */
    public function build(): CategoryPathToRootNode
    {
        return CoreHelper::clone($this->instance);
    }
}
