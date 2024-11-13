<?php

// File generated from our OpenAPI spec

namespace EDD\Vendor\Stripe\Service\Climate;

/**
 * @phpstan-import-type RequestOptionsArray from \EDD\Vendor\Stripe\Util\RequestOptions
 * @psalm-import-type RequestOptionsArray from \EDD\Vendor\Stripe\Util\RequestOptions
 */
class OrderService extends \EDD\Vendor\Stripe\Service\AbstractService
{
    /**
     * Lists all Climate order objects. The orders are returned sorted by creation
     * date, with the most recently created orders appearing first.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Collection<\EDD\Vendor\Stripe\Climate\Order>
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/climate/orders', $params, $opts);
    }

    /**
     * Cancels a Climate order. You can cancel an order within 24 hours of creation.
     * EDD\Vendor\Stripe refunds the reservation <code>amount_subtotal</code>, but not the
     * <code>amount_fees</code> for user-triggered cancellations. Frontier might cancel
     * reservations if suppliers fail to deliver. If Frontier cancels the reservation,
     * EDD\Vendor\Stripe provides 90 days advance notice and refunds the
     * <code>amount_total</code>.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Climate\Order
     */
    public function cancel($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/climate/orders/%s/cancel', $id), $params, $opts);
    }

    /**
     * Creates a Climate order object for a given Climate product. The order will be
     * processed immediately after creation and payment will be deducted your EDD\Vendor\Stripe
     * balance.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Climate\Order
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/climate/orders', $params, $opts);
    }

    /**
     * Retrieves the details of a Climate order object with the given ID.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Climate\Order
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/climate/orders/%s', $id), $params, $opts);
    }

    /**
     * Updates the specified order by setting the values of the parameters passed.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Climate\Order
     */
    public function update($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/climate/orders/%s', $id), $params, $opts);
    }
}
