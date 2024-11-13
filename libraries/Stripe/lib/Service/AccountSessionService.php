<?php

// File generated from our OpenAPI spec

namespace EDD\Vendor\Stripe\Service;

/**
 * @phpstan-import-type RequestOptionsArray from \EDD\Vendor\Stripe\Util\RequestOptions
 * @psalm-import-type RequestOptionsArray from \EDD\Vendor\Stripe\Util\RequestOptions
 */
class AccountSessionService extends \EDD\Vendor\Stripe\Service\AbstractService
{
    /**
     * Creates a AccountSession object that includes a single-use token that the
     * platform can use on their front-end to grant client-side API access.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\AccountSession
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/account_sessions', $params, $opts);
    }
}
