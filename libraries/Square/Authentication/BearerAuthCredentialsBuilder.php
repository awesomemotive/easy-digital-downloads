<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Authentication;

use EDD\Vendor\Core\Utils\CoreHelper;

/**
 * Utility class for initializing BearerAuth security credentials.
 */
class BearerAuthCredentialsBuilder
{
    /**
     * @var array
     */
    private $config;

    private function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Initializer for BearerAuthCredentialsBuilder
     *
     * @param string $accessToken
     */
    public static function init(string $accessToken): self
    {
        return new self(['accessToken' => $accessToken]);
    }

    /**
     * Setter for AccessToken.
     *
     * @param string $accessToken
     *
     * @return $this
     */
    public function accessToken(string $accessToken): self
    {
        $this->config['accessToken'] = $accessToken;
        return $this;
    }

    public function getConfiguration(): array
    {
        return CoreHelper::clone($this->config);
    }
}
