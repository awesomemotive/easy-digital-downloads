<?php

// File generated from our OpenAPI spec

namespace EDD\Vendor\Stripe\Service;

/**
 * @phpstan-import-type RequestOptionsArray from \EDD\Vendor\Stripe\Util\RequestOptions
 * @psalm-import-type RequestOptionsArray from \EDD\Vendor\Stripe\Util\RequestOptions
 */
class ExchangeRateService extends \EDD\Vendor\Stripe\Service\AbstractService
{
    /**
     * Returns a list of objects that contain the rates at which foreign currencies are
     * converted to one another. Only shows the currencies for which EDD\Vendor\Stripe supports.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Collection<\EDD\Vendor\Stripe\ExchangeRate>
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/exchange_rates', $params, $opts);
    }

    /**
     * Retrieves the exchange rates from the given currency to every supported
     * currency.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\ExchangeRate
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/exchange_rates/%s', $id), $params, $opts);
    }
}
