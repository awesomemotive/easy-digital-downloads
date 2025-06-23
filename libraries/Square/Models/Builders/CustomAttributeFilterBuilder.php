<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomAttributeFilter;
use EDD\Vendor\Square\Models\Range;

/**
 * Builder for model CustomAttributeFilter
 *
 * @see CustomAttributeFilter
 */
class CustomAttributeFilterBuilder
{
    /**
     * @var CustomAttributeFilter
     */
    private $instance;

    private function __construct(CustomAttributeFilter $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Custom Attribute Filter Builder object.
     */
    public static function init(): self
    {
        return new self(new CustomAttributeFilter());
    }

    /**
     * Sets custom attribute definition id field.
     *
     * @param string|null $value
     */
    public function customAttributeDefinitionId(?string $value): self
    {
        $this->instance->setCustomAttributeDefinitionId($value);
        return $this;
    }

    /**
     * Unsets custom attribute definition id field.
     */
    public function unsetCustomAttributeDefinitionId(): self
    {
        $this->instance->unsetCustomAttributeDefinitionId();
        return $this;
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
     * Unsets key field.
     */
    public function unsetKey(): self
    {
        $this->instance->unsetKey();
        return $this;
    }

    /**
     * Sets string filter field.
     *
     * @param string|null $value
     */
    public function stringFilter(?string $value): self
    {
        $this->instance->setStringFilter($value);
        return $this;
    }

    /**
     * Unsets string filter field.
     */
    public function unsetStringFilter(): self
    {
        $this->instance->unsetStringFilter();
        return $this;
    }

    /**
     * Sets number filter field.
     *
     * @param Range|null $value
     */
    public function numberFilter(?Range $value): self
    {
        $this->instance->setNumberFilter($value);
        return $this;
    }

    /**
     * Sets selection uids filter field.
     *
     * @param string[]|null $value
     */
    public function selectionUidsFilter(?array $value): self
    {
        $this->instance->setSelectionUidsFilter($value);
        return $this;
    }

    /**
     * Unsets selection uids filter field.
     */
    public function unsetSelectionUidsFilter(): self
    {
        $this->instance->unsetSelectionUidsFilter();
        return $this;
    }

    /**
     * Sets bool filter field.
     *
     * @param bool|null $value
     */
    public function boolFilter(?bool $value): self
    {
        $this->instance->setBoolFilter($value);
        return $this;
    }

    /**
     * Unsets bool filter field.
     */
    public function unsetBoolFilter(): self
    {
        $this->instance->unsetBoolFilter();
        return $this;
    }

    /**
     * Initializes a new Custom Attribute Filter object.
     */
    public function build(): CustomAttributeFilter
    {
        return CoreHelper::clone($this->instance);
    }
}
