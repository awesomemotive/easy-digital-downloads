<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CatalogProductSet;

/**
 * Builder for model CatalogProductSet
 *
 * @see CatalogProductSet
 */
class CatalogProductSetBuilder
{
    /**
     * @var CatalogProductSet
     */
    private $instance;

    private function __construct(CatalogProductSet $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Catalog Product Set Builder object.
     */
    public static function init(): self
    {
        return new self(new CatalogProductSet());
    }

    /**
     * Sets name field.
     *
     * @param string|null $value
     */
    public function name(?string $value): self
    {
        $this->instance->setName($value);
        return $this;
    }

    /**
     * Unsets name field.
     */
    public function unsetName(): self
    {
        $this->instance->unsetName();
        return $this;
    }

    /**
     * Sets product ids any field.
     *
     * @param string[]|null $value
     */
    public function productIdsAny(?array $value): self
    {
        $this->instance->setProductIdsAny($value);
        return $this;
    }

    /**
     * Unsets product ids any field.
     */
    public function unsetProductIdsAny(): self
    {
        $this->instance->unsetProductIdsAny();
        return $this;
    }

    /**
     * Sets product ids all field.
     *
     * @param string[]|null $value
     */
    public function productIdsAll(?array $value): self
    {
        $this->instance->setProductIdsAll($value);
        return $this;
    }

    /**
     * Unsets product ids all field.
     */
    public function unsetProductIdsAll(): self
    {
        $this->instance->unsetProductIdsAll();
        return $this;
    }

    /**
     * Sets quantity exact field.
     *
     * @param int|null $value
     */
    public function quantityExact(?int $value): self
    {
        $this->instance->setQuantityExact($value);
        return $this;
    }

    /**
     * Unsets quantity exact field.
     */
    public function unsetQuantityExact(): self
    {
        $this->instance->unsetQuantityExact();
        return $this;
    }

    /**
     * Sets quantity min field.
     *
     * @param int|null $value
     */
    public function quantityMin(?int $value): self
    {
        $this->instance->setQuantityMin($value);
        return $this;
    }

    /**
     * Unsets quantity min field.
     */
    public function unsetQuantityMin(): self
    {
        $this->instance->unsetQuantityMin();
        return $this;
    }

    /**
     * Sets quantity max field.
     *
     * @param int|null $value
     */
    public function quantityMax(?int $value): self
    {
        $this->instance->setQuantityMax($value);
        return $this;
    }

    /**
     * Unsets quantity max field.
     */
    public function unsetQuantityMax(): self
    {
        $this->instance->unsetQuantityMax();
        return $this;
    }

    /**
     * Sets all products field.
     *
     * @param bool|null $value
     */
    public function allProducts(?bool $value): self
    {
        $this->instance->setAllProducts($value);
        return $this;
    }

    /**
     * Unsets all products field.
     */
    public function unsetAllProducts(): self
    {
        $this->instance->unsetAllProducts();
        return $this;
    }

    /**
     * Initializes a new Catalog Product Set object.
     */
    public function build(): CatalogProductSet
    {
        return CoreHelper::clone($this->instance);
    }
}
