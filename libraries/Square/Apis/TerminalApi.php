<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Apis;

use EDD\Vendor\Core\Request\Parameters\BodyParam;
use EDD\Vendor\Core\Request\Parameters\HeaderParam;
use EDD\Vendor\Core\Request\Parameters\TemplateParam;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestMethod;
use EDD\Vendor\Square\Http\ApiResponse;
use EDD\Vendor\Square\Models\CancelTerminalActionResponse;
use EDD\Vendor\Square\Models\CancelTerminalCheckoutResponse;
use EDD\Vendor\Square\Models\CancelTerminalRefundResponse;
use EDD\Vendor\Square\Models\CreateTerminalActionRequest;
use EDD\Vendor\Square\Models\CreateTerminalActionResponse;
use EDD\Vendor\Square\Models\CreateTerminalCheckoutRequest;
use EDD\Vendor\Square\Models\CreateTerminalCheckoutResponse;
use EDD\Vendor\Square\Models\CreateTerminalRefundRequest;
use EDD\Vendor\Square\Models\CreateTerminalRefundResponse;
use EDD\Vendor\Square\Models\DismissTerminalActionResponse;
use EDD\Vendor\Square\Models\DismissTerminalCheckoutResponse;
use EDD\Vendor\Square\Models\DismissTerminalRefundResponse;
use EDD\Vendor\Square\Models\GetTerminalActionResponse;
use EDD\Vendor\Square\Models\GetTerminalCheckoutResponse;
use EDD\Vendor\Square\Models\GetTerminalRefundResponse;
use EDD\Vendor\Square\Models\SearchTerminalActionsRequest;
use EDD\Vendor\Square\Models\SearchTerminalActionsResponse;
use EDD\Vendor\Square\Models\SearchTerminalCheckoutsRequest;
use EDD\Vendor\Square\Models\SearchTerminalCheckoutsResponse;
use EDD\Vendor\Square\Models\SearchTerminalRefundsRequest;
use EDD\Vendor\Square\Models\SearchTerminalRefundsResponse;

class TerminalApi extends BaseApi
{
    /**
     * Creates a Terminal action request and sends it to the specified device.
     *
     * @param CreateTerminalActionRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function createTerminalAction(CreateTerminalActionRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/terminals/actions')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(CreateTerminalActionResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves a filtered list of Terminal action requests created by the account making the request.
     * Terminal action requests are available for 30 days.
     *
     * @param SearchTerminalActionsRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function searchTerminalActions(SearchTerminalActionsRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/terminals/actions/search')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(SearchTerminalActionsResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves a Terminal action request by `action_id`. Terminal action requests are available for 30
     * days.
     *
     * @param string $actionId Unique ID for the desired `TerminalAction`.
     *
     * @return ApiResponse Response from the API call
     */
    public function getTerminalAction(string $actionId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/terminals/actions/{action_id}')
            ->auth('global')
            ->parameters(TemplateParam::init('action_id', $actionId));

        $_resHandler = $this->responseHandler()->type(GetTerminalActionResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Cancels a Terminal action request if the status of the request permits it.
     *
     * @param string $actionId Unique ID for the desired `TerminalAction`.
     *
     * @return ApiResponse Response from the API call
     */
    public function cancelTerminalAction(string $actionId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/terminals/actions/{action_id}/cancel')
            ->auth('global')
            ->parameters(TemplateParam::init('action_id', $actionId));

        $_resHandler = $this->responseHandler()->type(CancelTerminalActionResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Dismisses a Terminal action request if the status and type of the request permits it.
     *
     * See [Link and Dismiss Actions](https://developer.squareup.com/docs/terminal-api/advanced-
     * features/custom-workflows/link-and-dismiss-actions) for more details.
     *
     * @param string $actionId Unique ID for the `TerminalAction` associated with the action to be
     *        dismissed.
     *
     * @return ApiResponse Response from the API call
     */
    public function dismissTerminalAction(string $actionId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/terminals/actions/{action_id}/dismiss')
            ->auth('global')
            ->parameters(TemplateParam::init('action_id', $actionId));

        $_resHandler = $this->responseHandler()->type(DismissTerminalActionResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Creates a Terminal checkout request and sends it to the specified device to take a payment
     * for the requested amount.
     *
     * @param CreateTerminalCheckoutRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function createTerminalCheckout(CreateTerminalCheckoutRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/terminals/checkouts')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(CreateTerminalCheckoutResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Returns a filtered list of Terminal checkout requests created by the application making the request.
     * Only Terminal checkout requests created for the merchant scoped to the OAuth token are returned.
     * Terminal checkout requests are available for 30 days.
     *
     * @param SearchTerminalCheckoutsRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function searchTerminalCheckouts(SearchTerminalCheckoutsRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/terminals/checkouts/search')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(SearchTerminalCheckoutsResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves a Terminal checkout request by `checkout_id`. Terminal checkout requests are available for
     * 30 days.
     *
     * @param string $checkoutId The unique ID for the desired `TerminalCheckout`.
     *
     * @return ApiResponse Response from the API call
     */
    public function getTerminalCheckout(string $checkoutId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/terminals/checkouts/{checkout_id}')
            ->auth('global')
            ->parameters(TemplateParam::init('checkout_id', $checkoutId));

        $_resHandler = $this->responseHandler()->type(GetTerminalCheckoutResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Cancels a Terminal checkout request if the status of the request permits it.
     *
     * @param string $checkoutId The unique ID for the desired `TerminalCheckout`.
     *
     * @return ApiResponse Response from the API call
     */
    public function cancelTerminalCheckout(string $checkoutId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/terminals/checkouts/{checkout_id}/cancel')
            ->auth('global')
            ->parameters(TemplateParam::init('checkout_id', $checkoutId));

        $_resHandler = $this->responseHandler()->type(CancelTerminalCheckoutResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Dismisses a Terminal checkout request if the status and type of the request permits it.
     *
     * @param string $checkoutId Unique ID for the `TerminalCheckout` associated with the checkout
     *        to be dismissed.
     *
     * @return ApiResponse Response from the API call
     */
    public function dismissTerminalCheckout(string $checkoutId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/terminals/checkouts/{checkout_id}/dismiss')
            ->auth('global')
            ->parameters(TemplateParam::init('checkout_id', $checkoutId));

        $_resHandler = $this->responseHandler()->type(DismissTerminalCheckoutResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Creates a request to refund an Interac payment completed on a EDD\Vendor\Square Terminal. Refunds for Interac
     * payments on a EDD\Vendor\Square Terminal are supported only for Interac debit cards in Canada. Other refunds
     * for Terminal payments should use the Refunds API. For more information, see [Refunds
     * API]($e/Refunds).
     *
     * @param CreateTerminalRefundRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function createTerminalRefund(CreateTerminalRefundRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/terminals/refunds')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(CreateTerminalRefundResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves a filtered list of Interac Terminal refund requests created by the seller making the
     * request. Terminal refund requests are available for 30 days.
     *
     * @param SearchTerminalRefundsRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function searchTerminalRefunds(SearchTerminalRefundsRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/terminals/refunds/search')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(SearchTerminalRefundsResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves an Interac Terminal refund object by ID. Terminal refund objects are available for 30 days.
     *
     * @param string $terminalRefundId The unique ID for the desired `TerminalRefund`.
     *
     * @return ApiResponse Response from the API call
     */
    public function getTerminalRefund(string $terminalRefundId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/terminals/refunds/{terminal_refund_id}')
            ->auth('global')
            ->parameters(TemplateParam::init('terminal_refund_id', $terminalRefundId));

        $_resHandler = $this->responseHandler()->type(GetTerminalRefundResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Cancels an Interac Terminal refund request by refund request ID if the status of the request permits
     * it.
     *
     * @param string $terminalRefundId The unique ID for the desired `TerminalRefund`.
     *
     * @return ApiResponse Response from the API call
     */
    public function cancelTerminalRefund(string $terminalRefundId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(
            RequestMethod::POST,
            '/v2/terminals/refunds/{terminal_refund_id}/cancel'
        )->auth('global')->parameters(TemplateParam::init('terminal_refund_id', $terminalRefundId));

        $_resHandler = $this->responseHandler()->type(CancelTerminalRefundResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Dismisses a Terminal refund request if the status and type of the request permits it.
     *
     * @param string $terminalRefundId Unique ID for the `TerminalRefund` associated with the refund
     *        to be dismissed.
     *
     * @return ApiResponse Response from the API call
     */
    public function dismissTerminalRefund(string $terminalRefundId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(
            RequestMethod::POST,
            '/v2/terminals/refunds/{terminal_refund_id}/dismiss'
        )->auth('global')->parameters(TemplateParam::init('terminal_refund_id', $terminalRefundId));

        $_resHandler = $this->responseHandler()->type(DismissTerminalRefundResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }
}
