<?php

namespace EDD\Vendor\Stripe\Exception\OAuth;

/**
 * Implements properties and methods common to all (non-SPL) EDD\Vendor\Stripe OAuth
 * exceptions.
 */
abstract class OAuthErrorException extends \EDD\Vendor\Stripe\Exception\ApiErrorException
{
    protected function constructErrorObject()
    {
        if (null === $this->jsonBody) {
            return null;
        }

        return \EDD\Vendor\Stripe\OAuthErrorObject::constructFrom($this->jsonBody);
    }
}
