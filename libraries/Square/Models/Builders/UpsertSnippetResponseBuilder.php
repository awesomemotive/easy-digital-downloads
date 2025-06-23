<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\Snippet;
use EDD\Vendor\Square\Models\UpsertSnippetResponse;

/**
 * Builder for model UpsertSnippetResponse
 *
 * @see UpsertSnippetResponse
 */
class UpsertSnippetResponseBuilder
{
    /**
     * @var UpsertSnippetResponse
     */
    private $instance;

    private function __construct(UpsertSnippetResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Upsert Snippet Response Builder object.
     */
    public static function init(): self
    {
        return new self(new UpsertSnippetResponse());
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
     * Sets snippet field.
     *
     * @param Snippet|null $value
     */
    public function snippet(?Snippet $value): self
    {
        $this->instance->setSnippet($value);
        return $this;
    }

    /**
     * Initializes a new Upsert Snippet Response object.
     */
    public function build(): UpsertSnippetResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
