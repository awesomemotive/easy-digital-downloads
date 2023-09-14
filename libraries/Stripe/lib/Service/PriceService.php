<?php

// File generated from our OpenAPI spec

namespace EDD\Vendor\Stripe\Service;

class PriceService extends \EDD\Vendor\Stripe\Service\AbstractService
{
    /**
     * Returns a list of your prices.
     *
     * @param null|array $params
     * @param null|array|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Collection<\EDD\Vendor\Stripe\Price>
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/prices', $params, $opts);
    }

    /**
     * Creates a new price for an existing product. The price can be recurring or
     * one-time.
     *
     * @param null|array $params
     * @param null|array|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Price
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/prices', $params, $opts);
    }

    /**
     * Retrieves the price with the given ID.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Price
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/prices/%s', $id), $params, $opts);
    }

    /**
     * Search for prices you’ve previously created using Stripe’s <a
     * href="/docs/search#search-query-language">Search Query Language</a>. Don’t use
     * search in read-after-write flows where strict consistency is necessary. Under
     * normal operating conditions, data is searchable in less than a minute.
     * Occasionally, propagation of new or updated data can be up to an hour behind
     * during outages. Search functionality is not available to merchants in India.
     *
     * @param null|array $params
     * @param null|array|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\SearchResult<\EDD\Vendor\Stripe\Price>
     */
    public function search($params = null, $opts = null)
    {
        return $this->requestSearchResult('get', '/v1/prices/search', $params, $opts);
    }

    /**
     * Updates the specified price by setting the values of the parameters passed. Any
     * parameters not provided are left unchanged.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Price
     */
    public function update($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/prices/%s', $id), $params, $opts);
    }
}
