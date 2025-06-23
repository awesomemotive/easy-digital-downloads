<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\PhaseInput;

/**
 * Builder for model PhaseInput
 *
 * @see PhaseInput
 */
class PhaseInputBuilder
{
    /**
     * @var PhaseInput
     */
    private $instance;

    private function __construct(PhaseInput $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Phase Input Builder object.
     *
     * @param int $ordinal
     */
    public static function init(int $ordinal): self
    {
        return new self(new PhaseInput($ordinal));
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
     * Initializes a new Phase Input object.
     */
    public function build(): PhaseInput
    {
        return CoreHelper::clone($this->instance);
    }
}
