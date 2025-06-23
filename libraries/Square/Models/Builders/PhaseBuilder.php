<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Phase;

/**
 * Builder for model Phase
 *
 * @see Phase
 */
class PhaseBuilder
{
    /**
     * @var Phase
     */
    private $instance;

    private function __construct(Phase $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Phase Builder object.
     */
    public static function init(): self
    {
        return new self(new Phase());
    }

    /**
     * Sets uid field.
     *
     * @param string|null $value
     */
    public function uid(?string $value): self
    {
        $this->instance->setUid($value);
        return $this;
    }

    /**
     * Unsets uid field.
     */
    public function unsetUid(): self
    {
        $this->instance->unsetUid();
        return $this;
    }

    /**
     * Sets ordinal field.
     *
     * @param int|null $value
     */
    public function ordinal(?int $value): self
    {
        $this->instance->setOrdinal($value);
        return $this;
    }

    /**
     * Unsets ordinal field.
     */
    public function unsetOrdinal(): self
    {
        $this->instance->unsetOrdinal();
        return $this;
    }

    /**
     * Sets order template id field.
     *
     * @param string|null $value
     */
    public function orderTemplateId(?string $value): self
    {
        $this->instance->setOrderTemplateId($value);
        return $this;
    }

    /**
     * Unsets order template id field.
     */
    public function unsetOrderTemplateId(): self
    {
        $this->instance->unsetOrderTemplateId();
        return $this;
    }

    /**
     * Sets plan phase uid field.
     *
     * @param string|null $value
     */
    public function planPhaseUid(?string $value): self
    {
        $this->instance->setPlanPhaseUid($value);
        return $this;
    }

    /**
     * Unsets plan phase uid field.
     */
    public function unsetPlanPhaseUid(): self
    {
        $this->instance->unsetPlanPhaseUid();
        return $this;
    }

    /**
     * Initializes a new Phase object.
     */
    public function build(): Phase
    {
        return CoreHelper::clone($this->instance);
    }
}
