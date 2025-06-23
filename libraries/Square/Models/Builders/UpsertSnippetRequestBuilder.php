<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Snippet;
use EDD\Vendor\Square\Models\UpsertSnippetRequest;

/**
 * Builder for model UpsertSnippetRequest
 *
 * @see UpsertSnippetRequest
 */
class UpsertSnippetRequestBuilder
{
    /**
     * @var UpsertSnippetRequest
     */
    private $instance;

    private function __construct(UpsertSnippetRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Upsert Snippet Request Builder object.
     *
     * @param Snippet $snippet
     */
    public static function init(Snippet $snippet): self
    {
        return new self(new UpsertSnippetRequest($snippet));
    }

    /**
     * Initializes a new Upsert Snippet Request object.
     */
    public function build(): UpsertSnippetRequest
    {
        return CoreHelper::clone($this->instance);
    }
}
