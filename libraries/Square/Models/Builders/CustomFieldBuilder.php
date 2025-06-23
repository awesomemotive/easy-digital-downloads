<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\CustomField;

/**
 * Builder for model CustomField
 *
 * @see CustomField
 */
class CustomFieldBuilder
{
    /**
     * @var CustomField
     */
    private $instance;

    private function __construct(CustomField $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Custom Field Builder object.
     *
     * @param string $title
     */
    public static function init(string $title): self
    {
        return new self(new CustomField($title));
    }

    /**
     * Initializes a new Custom Field object.
     */
    public function build(): CustomField
    {
        return CoreHelper::clone($this->instance);
    }
}
