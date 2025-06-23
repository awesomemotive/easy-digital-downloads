<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\DeleteSnippetResponse;
use EDD\Vendor\Square\Models\Error;

/**
 * Builder for model DeleteSnippetResponse
 *
 * @see DeleteSnippetResponse
 */
class DeleteSnippetResponseBuilder
{
    /**
     * @var DeleteSnippetResponse
     */
    private $instance;

    private function __construct(DeleteSnippetResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Delete Snippet Response Builder object.
     */
    public static function init(): self
    {
        return new self(new DeleteSnippetResponse());
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
     * Initializes a new Delete Snippet Response object.
     */
    public function build(): DeleteSnippetResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
