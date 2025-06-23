<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DismissTerminalActionResponse;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\TerminalAction;

/**
 * Builder for model DismissTerminalActionResponse
 *
 * @see DismissTerminalActionResponse
 */
class DismissTerminalActionResponseBuilder
{
    /**
     * @var DismissTerminalActionResponse
     */
    private $instance;

    private function __construct(DismissTerminalActionResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Dismiss Terminal Action Response Builder object.
     */
    public static function init(): self
    {
        return new self(new DismissTerminalActionResponse());
    }

    /**
     * Sets errors field.
     *
     * @param Error[]|null $value
     */
    public function errors(?array $value): self
    {
        $this->instance->setErrors($value);
        return $this;
    }

    /**
     * Sets action field.
     *
     * @param TerminalAction|null $value
     */
    public function action(?TerminalAction $value): self
    {
        $this->instance->setAction($value);
        return $this;
    }

    /**
     * Initializes a new Dismiss Terminal Action Response object.
     */
    public function build(): DismissTerminalActionResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
