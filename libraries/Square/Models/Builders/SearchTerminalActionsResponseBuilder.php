<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Error;
use EDD\Vendor\Square\Models\SearchTerminalActionsResponse;
use EDD\Vendor\Square\Models\TerminalAction;

/**
 * Builder for model SearchTerminalActionsResponse
 *
 * @see SearchTerminalActionsResponse
 */
class SearchTerminalActionsResponseBuilder
{
    /**
     * @var SearchTerminalActionsResponse
     */
    private $instance;

    private function __construct(SearchTerminalActionsResponse $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Search Terminal Actions Response Builder object.
     */
    public static function init(): self
    {
        return new self(new SearchTerminalActionsResponse());
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
     * @param TerminalAction[]|null $value
     */
    public function action(?array $value): self
    {
        $this->instance->setAction($value);
        return $this;
    }

    /**
     * Sets cursor field.
     *
     * @param string|null $value
     */
    public function cursor(?string $value): self
    {
        $this->instance->setCursor($value);
        return $this;
    }

    /**
     * Initializes a new Search Terminal Actions Response object.
     */
    public function build(): SearchTerminalActionsResponse
    {
        return CoreHelper::clone($this->instance);
    }
}
