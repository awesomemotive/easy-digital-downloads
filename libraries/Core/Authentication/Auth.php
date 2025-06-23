<?php

declare(strict_types=1);

namespace EDD\Vendor\Core\Authentication;

use EDD\Vendor\Core\Exceptions\AuthValidationException;
use EDD\Vendor\CoreInterfaces\Core\Authentication\AuthGroup;
use EDD\Vendor\CoreInterfaces\Core\Authentication\AuthInterface;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestSetterInterface;
use EDD\Vendor\CoreInterfaces\Core\Request\TypeValidatorInterface;
use InvalidArgumentException;

/**
 * Use to group multiple Auth schemes with either `AND` or `OR`
 */
class Auth implements AuthInterface
{
    /**
     * @param self|string ...$auths
     */
    public static function and(...$auths): self
    {
        return new self($auths, AuthGroup::AND);
    }

    /**
     * @param self|string ...$auths
     */
    public static function or(...$auths): self
    {
        return new self($auths, AuthGroup::OR);
    }

    /**
     * @var array<Auth|string>
     */
    private $auths;

    /**
     * @var AuthInterface[]
     */
    private $selectedAuthGroups = [];

    /**
     * @var AuthInterface[]
     */
    private $validatedAuthGroups = [];

    /**
     * @var string
     */
    private $groupType;

    /**
     * @param array $auths
     * @param string $groupType
     */
    private function __construct(array $auths, string $groupType)
    {
        $this->auths = $auths;
        $this->groupType = $groupType;
    }

    /**
     * @param array<string,AuthInterface> $authManagers
     */
    public function withAuthManagers(array $authManagers): self
    {
        $this->selectedAuthGroups = array_map(function ($auth) use ($authManagers) {
            if (is_string($auth) && isset($authManagers[$auth])) {
                return $authManagers[$auth];
            } elseif ($auth instanceof Auth) {
                return $auth->withAuthManagers($authManagers);
            }
            throw new InvalidArgumentException("AuthManager not found with name: " . json_encode($auth));
        }, $this->auths);
        return $this;
    }

    /**
     * @throws AuthValidationException
     */
    public function validate(TypeValidatorInterface $validator): void
    {
        $this->validatedAuthGroups = [];
        $errors = array_filter(array_map(function ($authGroup) use ($validator) {
            try {
                $authGroup->validate($validator);
                if ($this->groupType == AuthGroup::AND || empty($this->validatedAuthGroups)) {
                    // Add all authGroups as validated in AND group
                    // but only the first one in OR group
                    $this->validatedAuthGroups[] = $authGroup;
                }
                return false;
            } catch (InvalidArgumentException $e) {
                return $e->getMessage();
            }
        }, $this->selectedAuthGroups));

        if (empty($errors) || ($this->groupType == AuthGroup::OR && !empty($this->validatedAuthGroups))) {
            return;
        }

        // throw exception if unable to apply Any Single authentication in AND group
        // OR if unable to apply All authentication in OR group
        throw AuthValidationException::init($errors);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function apply(RequestSetterInterface $request): void
    {
        $this->validatedAuthGroups = array_map(function ($authGroup) use ($request) {
            $authGroup->apply($request);
            return $authGroup;
        }, $this->validatedAuthGroups);
    }
}
