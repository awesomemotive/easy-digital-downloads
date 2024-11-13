<?php

// File generated from our OpenAPI spec

namespace EDD\Vendor\Stripe\Service;

/**
 * @phpstan-import-type RequestOptionsArray from \EDD\Vendor\Stripe\Util\RequestOptions
 * @psalm-import-type RequestOptionsArray from \EDD\Vendor\Stripe\Util\RequestOptions
 */
class PayoutService extends \EDD\Vendor\Stripe\Service\AbstractService
{
    /**
     * Returns a list of existing payouts sent to third-party bank accounts or payouts
     * that EDD\Vendor\Stripe sent to you. The payouts return in sorted order, with the most
     * recently created payouts appearing first.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Collection<\EDD\Vendor\Stripe\Payout>
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/payouts', $params, $opts);
    }

    /**
     * You can cancel a previously created payout if its status is
     * <code>pending</code>. EDD\Vendor\Stripe refunds the funds to your available balance. You
     * can’t cancel automatic EDD\Vendor\Stripe payouts.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Payout
     */
    public function cancel($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/payouts/%s/cancel', $id), $params, $opts);
    }

    /**
     * To send funds to your own bank account, create a new payout object. Your <a
     * href="#balance">EDD\Vendor\Stripe balance</a> must cover the payout amount. If it doesn’t,
     * you receive an “Insufficient Funds” error.
     *
     * If your API key is in test mode, money won’t actually be sent, though every
     * other action occurs as if you’re in live mode.
     *
     * If you create a manual payout on a EDD\Vendor\Stripe account that uses multiple payment
     * source types, you need to specify the source type balance that the payout draws
     * from. The <a href="#balance_object">balance object</a> details available and
     * pending amounts by source type.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Payout
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/payouts', $params, $opts);
    }

    /**
     * Retrieves the details of an existing payout. Supply the unique payout ID from
     * either a payout creation request or the payout list. EDD\Vendor\Stripe returns the
     * corresponding payout information.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Payout
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/payouts/%s', $id), $params, $opts);
    }

    /**
     * Reverses a payout by debiting the destination bank account. At this time, you
     * can only reverse payouts for connected accounts to US bank accounts. If the
     * payout is manual and in the <code>pending</code> status, use
     * <code>/v1/payouts/:id/cancel</code> instead.
     *
     * By requesting a reversal through <code>/v1/payouts/:id/reverse</code>, you
     * confirm that the authorized signatory of the selected bank account authorizes
     * the debit on the bank account and that no other authorization is required.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Payout
     */
    public function reverse($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/payouts/%s/reverse', $id), $params, $opts);
    }

    /**
     * Updates the specified payout by setting the values of the parameters you pass.
     * We don’t change parameters that you don’t provide. This request only accepts the
     * metadata as arguments.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Payout
     */
    public function update($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/payouts/%s', $id), $params, $opts);
    }
}
