<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Apis;

use EDD\Vendor\Core\Request\Parameters\BodyParam;
use EDD\Vendor\Core\Request\Parameters\HeaderParam;
use EDD\Vendor\Core\Request\Parameters\QueryParam;
use EDD\Vendor\Core\Request\Parameters\TemplateParam;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestMethod;
use EDD\Vendor\Square\Http\ApiResponse;
use EDD\Vendor\Square\Models\BulkSwapPlanRequest;
use EDD\Vendor\Square\Models\BulkSwapPlanResponse;
use EDD\Vendor\Square\Models\CancelSubscriptionResponse;
use EDD\Vendor\Square\Models\ChangeBillingAnchorDateRequest;
use EDD\Vendor\Square\Models\ChangeBillingAnchorDateResponse;
use EDD\Vendor\Square\Models\CreateSubscriptionRequest;
use EDD\Vendor\Square\Models\CreateSubscriptionResponse;
use EDD\Vendor\Square\Models\DeleteSubscriptionActionResponse;
use EDD\Vendor\Square\Models\ListSubscriptionEventsResponse;
use EDD\Vendor\Square\Models\PauseSubscriptionRequest;
use EDD\Vendor\Square\Models\PauseSubscriptionResponse;
use EDD\Vendor\Square\Models\ResumeSubscriptionRequest;
use EDD\Vendor\Square\Models\ResumeSubscriptionResponse;
use EDD\Vendor\Square\Models\RetrieveSubscriptionResponse;
use EDD\Vendor\Square\Models\SearchSubscriptionsRequest;
use EDD\Vendor\Square\Models\SearchSubscriptionsResponse;
use EDD\Vendor\Square\Models\SwapPlanRequest;
use EDD\Vendor\Square\Models\SwapPlanResponse;
use EDD\Vendor\Square\Models\UpdateSubscriptionRequest;
use EDD\Vendor\Square\Models\UpdateSubscriptionResponse;

class SubscriptionsApi extends BaseApi
{
    /**
     * Enrolls a customer in a subscription.
     *
     * If you provide a card on file in the request, EDD\Vendor\Square charges the card for
     * the subscription. Otherwise, EDD\Vendor\Square sends an invoice to the customer's email
     * address. The subscription starts immediately, unless the request includes
     * the optional `start_date`. Each individual subscription is associated with a particular location.
     *
     * For more information, see [Create a subscription](https://developer.squareup.com/docs/subscriptions-
     * api/manage-subscriptions#create-a-subscription).
     *
     * @param CreateSubscriptionRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function createSubscription(CreateSubscriptionRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/subscriptions')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(CreateSubscriptionResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Schedules a plan variation change for all active subscriptions under a given plan
     * variation. For more information, see [Swap Subscription Plan Variations](https://developer.squareup.
     * com/docs/subscriptions-api/swap-plan-variations).
     *
     * @param BulkSwapPlanRequest $body An object containing the fields to POST for the request. See
     *        the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function bulkSwapPlan(BulkSwapPlanRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/subscriptions/bulk-swap-plan')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(BulkSwapPlanResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Searches for subscriptions.
     *
     * Results are ordered chronologically by subscription creation date. If
     * the request specifies more than one location ID,
     * the endpoint orders the result
     * by location ID, and then by creation date within each location. If no locations are given
     * in the query, all locations are searched.
     *
     * You can also optionally specify `customer_ids` to search by customer.
     * If left unset, all customers
     * associated with the specified locations are returned.
     * If the request specifies customer IDs, the endpoint orders results
     * first by location, within location by customer ID, and within
     * customer by subscription creation date.
     *
     * @param SearchSubscriptionsRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function searchSubscriptions(SearchSubscriptionsRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/subscriptions/search')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(SearchSubscriptionsResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves a specific subscription.
     *
     * @param string $subscriptionId The ID of the subscription to retrieve.
     * @param string|null $mInclude A query parameter to specify related information to be included
     *        in the response.
     *
     *        The supported query parameter values are:
     *
     *        - `actions`: to include scheduled actions on the targeted subscription.
     *
     * @return ApiResponse Response from the API call
     */
    public function retrieveSubscription(string $subscriptionId, ?string $mInclude = null): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/subscriptions/{subscription_id}')
            ->auth('global')
            ->parameters(
                TemplateParam::init('subscription_id', $subscriptionId),
                QueryParam::init('include', $mInclude)
            );

        $_resHandler = $this->responseHandler()->type(RetrieveSubscriptionResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Updates a subscription by modifying or clearing `subscription` field values.
     * To clear a field, set its value to `null`.
     *
     * @param string $subscriptionId The ID of the subscription to update.
     * @param UpdateSubscriptionRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function updateSubscription(string $subscriptionId, UpdateSubscriptionRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::PUT, '/v2/subscriptions/{subscription_id}')
            ->auth('global')
            ->parameters(
                TemplateParam::init('subscription_id', $subscriptionId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(UpdateSubscriptionResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Deletes a scheduled action for a subscription.
     *
     * @param string $subscriptionId The ID of the subscription the targeted action is to act upon.
     * @param string $actionId The ID of the targeted action to be deleted.
     *
     * @return ApiResponse Response from the API call
     */
    public function deleteSubscriptionAction(string $subscriptionId, string $actionId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(
            RequestMethod::DELETE,
            '/v2/subscriptions/{subscription_id}/actions/{action_id}'
        )
            ->auth('global')
            ->parameters(
                TemplateParam::init('subscription_id', $subscriptionId),
                TemplateParam::init('action_id', $actionId)
            );

        $_resHandler = $this->responseHandler()->type(DeleteSubscriptionActionResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Changes the [billing anchor date](https://developer.squareup.com/docs/subscriptions-api/subscription-
     * billing#billing-dates)
     * for a subscription.
     *
     * @param string $subscriptionId The ID of the subscription to update the billing anchor date.
     * @param ChangeBillingAnchorDateRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function changeBillingAnchorDate(string $subscriptionId, ChangeBillingAnchorDateRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(
            RequestMethod::POST,
            '/v2/subscriptions/{subscription_id}/billing-anchor'
        )
            ->auth('global')
            ->parameters(
                TemplateParam::init('subscription_id', $subscriptionId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(ChangeBillingAnchorDateResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Schedules a `CANCEL` action to cancel an active subscription. This
     * sets the `canceled_date` field to the end of the active billing period. After this date,
     * the subscription status changes from ACTIVE to CANCELED.
     *
     * @param string $subscriptionId The ID of the subscription to cancel.
     *
     * @return ApiResponse Response from the API call
     */
    public function cancelSubscription(string $subscriptionId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/subscriptions/{subscription_id}/cancel')
            ->auth('global')
            ->parameters(TemplateParam::init('subscription_id', $subscriptionId));

        $_resHandler = $this->responseHandler()->type(CancelSubscriptionResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Lists all [events](https://developer.squareup.com/docs/subscriptions-api/actions-events) for a
     * specific subscription.
     *
     * @param string $subscriptionId The ID of the subscription to retrieve the events for.
     * @param string|null $cursor When the total number of resulting subscription events exceeds the
     *        limit of a paged response,
     *        specify the cursor returned from a preceding response here to fetch the next set of
     *        results.
     *        If the cursor is unset, the response contains the last page of the results.
     *
     *        For more information, see [Pagination](https://developer.squareup.com/docs/build-
     *        basics/common-api-patterns/pagination).
     * @param int|null $limit The upper limit on the number of subscription events to return in a
     *        paged response.
     *
     * @return ApiResponse Response from the API call
     */
    public function listSubscriptionEvents(
        string $subscriptionId,
        ?string $cursor = null,
        ?int $limit = null
    ): ApiResponse {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/subscriptions/{subscription_id}/events')
            ->auth('global')
            ->parameters(
                TemplateParam::init('subscription_id', $subscriptionId),
                QueryParam::init('cursor', $cursor),
                QueryParam::init('limit', $limit)
            );

        $_resHandler = $this->responseHandler()->type(ListSubscriptionEventsResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Schedules a `PAUSE` action to pause an active subscription.
     *
     * @param string $subscriptionId The ID of the subscription to pause.
     * @param PauseSubscriptionRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function pauseSubscription(string $subscriptionId, PauseSubscriptionRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/subscriptions/{subscription_id}/pause')
            ->auth('global')
            ->parameters(
                TemplateParam::init('subscription_id', $subscriptionId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(PauseSubscriptionResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Schedules a `RESUME` action to resume a paused or a deactivated subscription.
     *
     * @param string $subscriptionId The ID of the subscription to resume.
     * @param ResumeSubscriptionRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function resumeSubscription(string $subscriptionId, ResumeSubscriptionRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/subscriptions/{subscription_id}/resume')
            ->auth('global')
            ->parameters(
                TemplateParam::init('subscription_id', $subscriptionId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(ResumeSubscriptionResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Schedules a `SWAP_PLAN` action to swap a subscription plan variation in an existing subscription.
     * For more information, see [Swap Subscription Plan Variations](https://developer.squareup.
     * com/docs/subscriptions-api/swap-plan-variations).
     *
     * @param string $subscriptionId The ID of the subscription to swap the subscription plan for.
     * @param SwapPlanRequest $body An object containing the fields to POST for the request. See the
     *        corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function swapPlan(string $subscriptionId, SwapPlanRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/subscriptions/{subscription_id}/swap-plan')
            ->auth('global')
            ->parameters(
                TemplateParam::init('subscription_id', $subscriptionId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(SwapPlanResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }
}
