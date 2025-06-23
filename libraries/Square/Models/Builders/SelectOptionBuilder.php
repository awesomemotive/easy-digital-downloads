<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SelectOption;

/**
 * Builder for model SelectOption
 *
 * @see SelectOption
 */
class SelectOptionBuilder
{
    /**
     * @var SelectOption
     */
    private $instance;

    private function __construct(SelectOption $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Select Option Builder object.
     *
     * @param string $referenceId
     * @param string $title
     */
    public static function init(string $referenceId, string $title): self
    {
        return new self(new SelectOption($referenceId, $title));
    }

    /**
     * Initializes a new Select Option object.
     */
    public function build(): SelectOption
    {
        return CoreHelper::clone($this->instance);
    }
}
