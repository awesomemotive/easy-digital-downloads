<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Job;

/**
 * Builder for model Job
 *
 * @see Job
 */
class JobBuilder
{
    /**
     * @var Job
     */
    private $instance;

    private function __construct(Job $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Job Builder object.
     */
    public static function init(): self
    {
        return new self(new Job());
    }

    /**
     * Sets id field.
     *
     * @param string|null $value
     */
    public function id(?string $value): self
    {
        $this->instance->setId($value);
        return $this;
    }

    /**
     * Sets title field.
     *
     * @param string|null $value
     */
    public function title(?string $value): self
    {
        $this->instance->setTitle($value);
        return $this;
    }

    /**
     * Unsets title field.
     */
    public function unsetTitle(): self
    {
        $this->instance->unsetTitle();
        return $this;
    }

    /**
     * Sets is tip eligible field.
     *
     * @param bool|null $value
     */
    public function isTipEligible(?bool $value): self
    {
        $this->instance->setIsTipEligible($value);
        return $this;
    }

    /**
     * Unsets is tip eligible field.
     */
    public function unsetIsTipEligible(): self
    {
        $this->instance->unsetIsTipEligible();
        return $this;
    }

    /**
     * Sets created at field.
     *
     * @param string|null $value
     */
    public function createdAt(?string $value): self
    {
        $this->instance->setCreatedAt($value);
        return $this;
    }

    /**
     * Sets updated at field.
     *
     * @param string|null $value
     */
    public function updatedAt(?string $value): self
    {
        $this->instance->setUpdatedAt($value);
        return $this;
    }

    /**
     * Sets version field.
     *
     * @param int|null $value
     */
    public function version(?int $value): self
    {
        $this->instance->setVersion($value);
        return $this;
    }

    /**
     * Initializes a new Job object.
     */
    public function build(): Job
    {
        return CoreHelper::clone($this->instance);
    }
}
