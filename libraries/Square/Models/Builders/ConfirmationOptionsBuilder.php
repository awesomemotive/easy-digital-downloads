<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\ConfirmationDecision;
use EDD\Vendor\Square\Models\ConfirmationOptions;

/**
 * Builder for model ConfirmationOptions
 *
 * @see ConfirmationOptions
 */
class ConfirmationOptionsBuilder
{
    /**
     * @var ConfirmationOptions
     */
    private $instance;

    private function __construct(ConfirmationOptions $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Confirmation Options Builder object.
     *
     * @param string $title
     * @param string $body
     * @param string $agreeButtonText
     */
    public static function init(string $title, string $body, string $agreeButtonText): self
    {
        return new self(new ConfirmationOptions($title, $body, $agreeButtonText));
    }

    /**
     * Sets disagree button text field.
     *
     * @param string|null $value
     */
    public function disagreeButtonText(?string $value): self
    {
        $this->instance->setDisagreeButtonText($value);
        return $this;
    }

    /**
     * Unsets disagree button text field.
     */
    public function unsetDisagreeButtonText(): self
    {
        $this->instance->unsetDisagreeButtonText();
        return $this;
    }

    /**
     * Sets decision field.
     *
     * @param ConfirmationDecision|null $value
     */
    public function decision(?ConfirmationDecision $value): self
    {
        $this->instance->setDecision($value);
        return $this;
    }

    /**
     * Initializes a new Confirmation Options object.
     */
    public function build(): ConfirmationOptions
    {
        return CoreHelper::clone($this->instance);
    }
}
