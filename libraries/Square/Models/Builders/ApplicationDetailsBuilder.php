<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ApplicationDetails;

/**
 * Builder for model ApplicationDetails
 *
 * @see ApplicationDetails
 */
class ApplicationDetailsBuilder
{
    /**
     * @var ApplicationDetails
     */
    private $instance;

    private function __construct(ApplicationDetails $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Application Details Builder object.
     */
    public static function init(): self
    {
        return new self(new ApplicationDetails());
    }

    /**
     * Sets square product field.
     *
     * @param string|null $value
     */
    public function squareProduct(?string $value): self
    {
        $this->instance->setSquareProduct($value);
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
     * Initializes a new Application Details object.
     */
    public function build(): ApplicationDetails
    {
        return CoreHelper::clone($this->instance);
    }
}
