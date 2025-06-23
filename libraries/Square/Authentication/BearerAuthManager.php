<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Authentication;

use EDD\Vendor\Core\Authentication\CoreAuth;
use EDD\Vendor\Square\ConfigurationDefaults;
use EDD\Vendor\Core\Request\Parameters\HeaderParam;
use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\BearerAuthCredentials;

/**
 * Utility class for authorization and token management.
 */
class BearerAuthManager extends CoreAuth implements BearerAuthCredentials
{
    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        parent::__construct(
            HeaderParam::init('Authorization', CoreHelper::getBearerAuthString($this->getAccessToken()))
                ->requiredNonEmpty()
        );
    }

    /**
     * String value for accessToken.
     */
    public function getAccessToken(): string
    {
        return $this->config['accessToken'] ?? ConfigurationDefaults::ACCESS_TOKEN;
    }

    /**
     * Checks if provided credentials match with existing ones.
     *
     * @param string $accessToken The OAuth 2.0 Access Token to use for API requests.
     */
    public function equals(string $accessToken): bool
    {
        return $accessToken == $this->getAccessToken();
    }
}
