<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Apis;

use EDD\Vendor\Core\Request\Parameters\BodyParam;
use EDD\Vendor\Core\Request\Parameters\HeaderParam;
use EDD\Vendor\Core\Request\Parameters\QueryParam;
use EDD\Vendor\Core\Request\Parameters\TemplateParam;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestMethod;
use EDD\Vendor\Square\Http\ApiResponse;
use EDD\Vendor\Square\Models\CreateCheckoutRequest;
use EDD\Vendor\Square\Models\CreateCheckoutResponse;
use EDD\Vendor\Square\Models\CreatePaymentLinkRequest;
use EDD\Vendor\Square\Models\CreatePaymentLinkResponse;
use EDD\Vendor\Square\Models\DeletePaymentLinkResponse;
use EDD\Vendor\Square\Models\ListPaymentLinksResponse;
use EDD\Vendor\Square\Models\RetrieveLocationSettingsResponse;
use EDD\Vendor\Square\Models\RetrieveMerchantSettingsResponse;
use EDD\Vendor\Square\Models\RetrievePaymentLinkResponse;
use EDD\Vendor\Square\Models\UpdateLocationSettingsRequest;
use EDD\Vendor\Square\Models\UpdateLocationSettingsResponse;
use EDD\Vendor\Square\Models\UpdateMerchantSettingsRequest;
use EDD\Vendor\Square\Models\UpdateMerchantSettingsResponse;
use EDD\Vendor\Square\Models\UpdatePaymentLinkRequest;
use EDD\Vendor\Square\Models\UpdatePaymentLinkResponse;

class CheckoutApi extends BaseApi
{
    /**
     * Links a `checkoutId` to a `checkout_page_url` that customers are
     * directed to in order to provide their payment information using a
     * payment processing workflow hosted on connect.squareup.com.
     *
     *
     * NOTE: The Checkout API has been updated with new features.
     * For more information, see [Checkout API highlights](https://developer.squareup.com/docs/checkout-
     * api#checkout-api-highlights).
     *
     * @deprecated
     *
     * @param string $locationId The ID of the business location to associate the checkout with.
     * @param CreateCheckoutRequest $body An object containing the fields to POST for the request.
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function createCheckout(string $locationId, CreateCheckoutRequest $body): ApiResponse
    {
        trigger_error('Method ' . __METHOD__ . ' is deprecated.', E_USER_DEPRECATED);

        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/locations/{location_id}/checkouts')
            ->auth('global')
            ->parameters(
                TemplateParam::init('location_id', $locationId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(CreateCheckoutResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves the location-level settings for a Square-hosted checkout page.
     *
     * @param string $locationId The ID of the location for which to retrieve settings.
     *
     * @return ApiResponse Response from the API call
     */
    public function retrieveLocationSettings(string $locationId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(
            RequestMethod::GET,
            '/v2/online-checkout/location-settings/{location_id}'
        )->auth('global')->parameters(TemplateParam::init('location_id', $locationId));

        $_resHandler = $this->responseHandler()->type(RetrieveLocationSettingsResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Updates the location-level settings for a Square-hosted checkout page.
     *
     * @param string $locationId The ID of the location for which to retrieve settings.
     * @param UpdateLocationSettingsRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function updateLocationSettings(string $locationId, UpdateLocationSettingsRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(
            RequestMethod::PUT,
            '/v2/online-checkout/location-settings/{location_id}'
        )
            ->auth('global')
            ->parameters(
                TemplateParam::init('location_id', $locationId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(UpdateLocationSettingsResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves the merchant-level settings for a Square-hosted checkout page.
     *
     * @return ApiResponse Response from the API call
     */
    public function retrieveMerchantSettings(): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/online-checkout/merchant-settings')
            ->auth('global');

        $_resHandler = $this->responseHandler()->type(RetrieveMerchantSettingsResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Updates the merchant-level settings for a Square-hosted checkout page.
     *
     * @param UpdateMerchantSettingsRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function updateMerchantSettings(UpdateMerchantSettingsRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::PUT, '/v2/online-checkout/merchant-settings')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(UpdateMerchantSettingsResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Lists all payment links.
     *
     * @param string|null $cursor A pagination cursor returned by a previous call to this endpoint.
     *        Provide this cursor to retrieve the next set of results for the original query.
     *        If a cursor is not provided, the endpoint returns the first page of the results.
     *        For more  information, see [Pagination](https://developer.squareup.com/docs/build-
     *        basics/common-api-patterns/pagination).
     * @param int|null $limit A limit on the number of results to return per page. The limit is
     *        advisory and
     *        the implementation might return more or less results. If the supplied limit is
     *        negative, zero, or
     *        greater than the maximum limit of 1000, it is ignored.
     *
     *        Default value: `100`
     *
     * @return ApiResponse Response from the API call
     */
    public function listPaymentLinks(?string $cursor = null, ?int $limit = null): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/online-checkout/payment-links')
            ->auth('global')
            ->parameters(QueryParam::init('cursor', $cursor), QueryParam::init('limit', $limit));

        $_resHandler = $this->responseHandler()->type(ListPaymentLinksResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Creates a Square-hosted checkout page. Applications can share the resulting payment link with their
     * buyer to pay for goods and services.
     *
     * @param CreatePaymentLinkRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function createPaymentLink(CreatePaymentLinkRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/online-checkout/payment-links')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(CreatePaymentLinkResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Deletes a payment link.
     *
     * @param string $id The ID of the payment link to delete.
     *
     * @return ApiResponse Response from the API call
     */
    public function deletePaymentLink(string $id): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::DELETE, '/v2/online-checkout/payment-links/{id}')
            ->auth('global')
            ->parameters(TemplateParam::init('id', $id));

        $_resHandler = $this->responseHandler()->type(DeletePaymentLinkResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves a payment link.
     *
     * @param string $id The ID of link to retrieve.
     *
     * @return ApiResponse Response from the API call
     */
    public function retrievePaymentLink(string $id): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/online-checkout/payment-links/{id}')
            ->auth('global')
            ->parameters(TemplateParam::init('id', $id));

        $_resHandler = $this->responseHandler()->type(RetrievePaymentLinkResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Updates a payment link. You can update the `payment_link` fields such as
     * `description`, `checkout_options`, and  `pre_populated_data`.
     * You cannot update other fields such as the `order_id`, `version`, `URL`, or `timestamp` field.
     *
     * @param string $id The ID of the payment link to update.
     * @param UpdatePaymentLinkRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function updatePaymentLink(string $id, UpdatePaymentLinkRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::PUT, '/v2/online-checkout/payment-links/{id}')
            ->auth('global')
            ->parameters(
                TemplateParam::init('id', $id),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(UpdatePaymentLinkResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }
}
