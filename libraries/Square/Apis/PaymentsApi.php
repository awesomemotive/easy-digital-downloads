<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Apis;

use EDD\Vendor\Core\Request\Parameters\BodyParam;
use EDD\Vendor\Core\Request\Parameters\HeaderParam;
use EDD\Vendor\Core\Request\Parameters\QueryParam;
use EDD\Vendor\Core\Request\Parameters\TemplateParam;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestMethod;
use EDD\Vendor\Square\Http\ApiResponse;
use EDD\Vendor\Square\Models\CancelPaymentByIdempotencyKeyRequest;
use EDD\Vendor\Square\Models\CancelPaymentByIdempotencyKeyResponse;
use EDD\Vendor\Square\Models\CancelPaymentResponse;
use EDD\Vendor\Square\Models\CompletePaymentRequest;
use EDD\Vendor\Square\Models\CompletePaymentResponse;
use EDD\Vendor\Square\Models\CreatePaymentRequest;
use EDD\Vendor\Square\Models\CreatePaymentResponse;
use EDD\Vendor\Square\Models\GetPaymentResponse;
use EDD\Vendor\Square\Models\ListPaymentsResponse;
use EDD\Vendor\Square\Models\UpdatePaymentRequest;
use EDD\Vendor\Square\Models\UpdatePaymentResponse;

class PaymentsApi extends BaseApi
{
    /**
     * Retrieves a list of payments taken by the account making the request.
     *
     * Results are eventually consistent, and new payments or changes to payments might take several
     * seconds to appear.
     *
     * The maximum results per page is 100.
     *
     * @param string|null $beginTime Indicates the start of the time range to retrieve payments for,
     *        in RFC 3339 format.
     *        The range is determined using the `created_at` field for each Payment.
     *        Inclusive. Default: The current time minus one year.
     * @param string|null $endTime Indicates the end of the time range to retrieve payments for, in
     *        RFC 3339 format.  The
     *        range is determined using the `created_at` field for each Payment.
     *
     *        Default: The current time.
     * @param string|null $sortOrder The order in which results are listed by
     *        `ListPaymentsRequest.sort_field`:
     *        - `ASC` - Oldest to newest.
     *        - `DESC` - Newest to oldest (default).
     * @param string|null $cursor A pagination cursor returned by a previous call to this endpoint.
     *        Provide this cursor to retrieve the next set of results for the original query.
     *
     *        For more information, see [Pagination](https://developer.squareup.com/docs/build-
     *        basics/common-api-patterns/pagination).
     * @param string|null $locationId Limit results to the location supplied. By default, results
     *        are returned
     *        for the default (main) location associated with the seller.
     * @param int|null $total The exact amount in the `total_money` for a payment.
     * @param string|null $last4 The last four digits of a payment card.
     * @param string|null $cardBrand The brand of the payment card (for example, VISA).
     * @param int|null $limit The maximum number of results to be returned in a single page. It is
     *        possible to receive fewer results than the specified limit on a given page.
     *
     *        The default value of 100 is also the maximum allowed value. If the provided value is
     *        greater than 100, it is ignored and the default value is used instead.
     *
     *        Default: `100`
     * @param bool|null $isOfflinePayment Whether the payment was taken offline or not.
     * @param string|null $offlineBeginTime Indicates the start of the time range for which to
     *        retrieve offline payments, in RFC 3339
     *        format for timestamps. The range is determined using the
     *        `offline_payment_details.client_created_at` field for each Payment. If set, payments
     *        without a
     *        value set in `offline_payment_details.client_created_at` will not be returned.
     *
     *        Default: The current time.
     * @param string|null $offlineEndTime Indicates the end of the time range for which to retrieve
     *        offline payments, in RFC 3339
     *        format for timestamps. The range is determined using the
     *        `offline_payment_details.client_created_at` field for each Payment. If set, payments
     *        without a
     *        value set in `offline_payment_details.client_created_at` will not be returned.
     *
     *        Default: The current time.
     * @param string|null $updatedAtBeginTime Indicates the start of the time range to retrieve
     *        payments for, in RFC 3339 format.  The
     *        range is determined using the `updated_at` field for each Payment.
     * @param string|null $updatedAtEndTime Indicates the end of the time range to retrieve payments
     *        for, in RFC 3339 format.  The
     *        range is determined using the `updated_at` field for each Payment.
     * @param string|null $sortField The field used to sort results by. The default is `CREATED_AT`.
     *
     * @return ApiResponse Response from the API call
     */
    public function listPayments(
        ?string $beginTime = null,
        ?string $endTime = null,
        ?string $sortOrder = null,
        ?string $cursor = null,
        ?string $locationId = null,
        ?int $total = null,
        ?string $last4 = null,
        ?string $cardBrand = null,
        ?int $limit = null,
        ?bool $isOfflinePayment = false,
        ?string $offlineBeginTime = null,
        ?string $offlineEndTime = null,
        ?string $updatedAtBeginTime = null,
        ?string $updatedAtEndTime = null,
        ?string $sortField = null
    ): ApiResponse {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/payments')
            ->auth('global')
            ->parameters(
                QueryParam::init('begin_time', $beginTime),
                QueryParam::init('end_time', $endTime),
                QueryParam::init('sort_order', $sortOrder),
                QueryParam::init('cursor', $cursor),
                QueryParam::init('location_id', $locationId),
                QueryParam::init('total', $total),
                QueryParam::init('last_4', $last4),
                QueryParam::init('card_brand', $cardBrand),
                QueryParam::init('limit', $limit),
                QueryParam::init('is_offline_payment', $isOfflinePayment),
                QueryParam::init('offline_begin_time', $offlineBeginTime),
                QueryParam::init('offline_end_time', $offlineEndTime),
                QueryParam::init('updated_at_begin_time', $updatedAtBeginTime),
                QueryParam::init('updated_at_end_time', $updatedAtEndTime),
                QueryParam::init('sort_field', $sortField)
            );

        $_resHandler = $this->responseHandler()->type(ListPaymentsResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Creates a payment using the provided source. You can use this endpoint
     * to charge a card (credit/debit card or
     * EDD\Vendor\Square gift card) or record a payment that the seller received outside of EDD\Vendor\Square
     * (cash payment from a buyer or a payment that an external entity
     * processed on behalf of the seller).
     *
     * The endpoint creates a
     * `Payment` object and returns it in the response.
     *
     * @param CreatePaymentRequest $body An object containing the fields to POST for the request.
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function createPayment(CreatePaymentRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/payments')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(CreatePaymentResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Cancels (voids) a payment identified by the idempotency key that is specified in the
     * request.
     *
     * Use this method when the status of a `CreatePayment` request is unknown (for example, after you send
     * a
     * `CreatePayment` request, a network error occurs and you do not get a response). In this case, you
     * can
     * direct EDD\Vendor\Square to cancel the payment using this endpoint. In the request, you provide the same
     * idempotency key that you provided in your `CreatePayment` request that you want to cancel. After
     * canceling the payment, you can submit your `CreatePayment` request again.
     *
     * Note that if no payment with the specified idempotency key is found, no action is taken and the
     * endpoint
     * returns successfully.
     *
     * @param CancelPaymentByIdempotencyKeyRequest $body An object containing the fields to POST for
     *        the request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function cancelPaymentByIdempotencyKey(CancelPaymentByIdempotencyKeyRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/payments/cancel')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()
            ->type(CancelPaymentByIdempotencyKeyResponse::class)
            ->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves details for a specific payment.
     *
     * @param string $paymentId A unique ID for the desired payment.
     *
     * @return ApiResponse Response from the API call
     */
    public function getPayment(string $paymentId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/payments/{payment_id}')
            ->auth('global')
            ->parameters(TemplateParam::init('payment_id', $paymentId));

        $_resHandler = $this->responseHandler()->type(GetPaymentResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Updates a payment with the APPROVED status.
     * You can update the `amount_money` and `tip_money` using this endpoint.
     *
     * @param string $paymentId The ID of the payment to update.
     * @param UpdatePaymentRequest $body An object containing the fields to POST for the request.
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function updatePayment(string $paymentId, UpdatePaymentRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::PUT, '/v2/payments/{payment_id}')
            ->auth('global')
            ->parameters(
                TemplateParam::init('payment_id', $paymentId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(UpdatePaymentResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Cancels (voids) a payment. You can use this endpoint to cancel a payment with
     * the APPROVED `status`.
     *
     * @param string $paymentId The ID of the payment to cancel.
     *
     * @return ApiResponse Response from the API call
     */
    public function cancelPayment(string $paymentId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/payments/{payment_id}/cancel')
            ->auth('global')
            ->parameters(TemplateParam::init('payment_id', $paymentId));

        $_resHandler = $this->responseHandler()->type(CancelPaymentResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Completes (captures) a payment.
     * By default, payments are set to complete immediately after they are created.
     *
     * You can use this endpoint to complete a payment with the APPROVED `status`.
     *
     * @param string $paymentId The unique ID identifying the payment to be completed.
     * @param CompletePaymentRequest $body An object containing the fields to POST for the request.
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function completePayment(string $paymentId, CompletePaymentRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/payments/{payment_id}/complete')
            ->auth('global')
            ->parameters(
                TemplateParam::init('payment_id', $paymentId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(CompletePaymentResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }
}
