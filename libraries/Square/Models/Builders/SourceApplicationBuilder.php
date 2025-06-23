<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SourceApplication;

/**
 * Builder for model SourceApplication
 *
 * @see SourceApplication
 */
class SourceApplicationBuilder
{
    /**
     * @var SourceApplication
     */
    private $instance;

    private function __construct(SourceApplication $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Source Application Builder object.
     */
    public static function init(): self
    {
        return new self(new SourceApplication());
    }

    /**
     * Sets product field.
     *
     * @param string|null $value
     */
    public function product(?string $value): self
    {
        $this->instance->setProduct($value);
        return $this;
    }

    /**
     * Sets application id field.
     *
     * @param string|null $value
     */
    public function applicationId(?string $value): self
    {
        $this->instance->setApplicationId($value);
        return $this;
    }

    /**
     * Unsets application id field.
     */
    public function unsetApplicationId(): self
    {
        $this->instance->unsetApplicationId();
        return $this;
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
     * Initializes a new Source Application object.
     */
    public function build(): SourceApplication
    {
        return CoreHelper::clone($this->instance);
    }
}
