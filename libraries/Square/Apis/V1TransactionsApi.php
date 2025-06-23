<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Apis;

use EDD\Vendor\Core\Request\Parameters\BodyParam;
use EDD\Vendor\Core\Request\Parameters\HeaderParam;
use EDD\Vendor\Core\Request\Parameters\QueryParam;
use EDD\Vendor\Core\Request\Parameters\TemplateParam;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestMethod;
use EDD\Vendor\Square\Http\ApiResponse;
use EDD\Vendor\Square\Models\V1Order;
use EDD\Vendor\Square\Models\V1UpdateOrderRequest;

class V1TransactionsApi extends BaseApi
{
    /**
     * Provides summary information for a merchant's online store orders.
     *
     * @deprecated
     *
     * @param string $locationId The ID of the location to list online store orders for.
     * @param string|null $order The order in which payments are listed in the response.
     * @param int|null $limit The maximum number of payments to return in a single response. This
     *        value cannot exceed 200.
     * @param string|null $batchToken A pagination cursor to retrieve the next set of results for
     *        your
     *        original query to the endpoint.
     *
     * @return ApiResponse Response from the API call
     */
    public function v1ListOrders(
        string $locationId,
        ?string $order = null,
        ?int $limit = null,
        ?string $batchToken = null
    ): ApiResponse {
        trigger_error('Method ' . __METHOD__ . ' is deprecated.', E_USER_DEPRECATED);

        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v1/{location_id}/orders')
            ->auth('global')
            ->parameters(
                TemplateParam::init('location_id', $locationId),
                QueryParam::init('order', $order),
                QueryParam::init('limit', $limit),
                QueryParam::init('batch_token', $batchToken)
            );

        $_resHandler = $this->responseHandler()->type(V1Order::class, 1)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Provides comprehensive information for a single online store order, including the order's history.
     *
     * @deprecated
     *
     * @param string $locationId The ID of the order's associated location.
     * @param string $orderId The order's Square-issued ID. You obtain this value from Order objects
     *        returned by the List Orders endpoint
     *
     * @return ApiResponse Response from the API call
     */
    public function v1RetrieveOrder(string $locationId, string $orderId): ApiResponse
    {
        trigger_error('Method ' . __METHOD__ . ' is deprecated.', E_USER_DEPRECATED);

        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v1/{location_id}/orders/{order_id}')
            ->auth('global')
            ->parameters(
                TemplateParam::init('location_id', $locationId),
                TemplateParam::init('order_id', $orderId)
            );

        $_resHandler = $this->responseHandler()->type(V1Order::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Updates the details of an online store order. Every update you perform on an order corresponds to
     * one of three actions:
     *
     * @deprecated
     *
     * @param string $locationId The ID of the order's associated location.
     * @param string $orderId The order's Square-issued ID. You obtain this value from Order objects
     *        returned by the List Orders endpoint
     * @param V1UpdateOrderRequest $body An object containing the fields to POST for the request.
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function v1UpdateOrder(string $locationId, string $orderId, V1UpdateOrderRequest $body): ApiResponse
    {
        trigger_error('Method ' . __METHOD__ . ' is deprecated.', E_USER_DEPRECATED);

        $_reqBuilder = $this->requestBuilder(RequestMethod::PUT, '/v1/{location_id}/orders/{order_id}')
            ->auth('global')
            ->parameters(
                TemplateParam::init('location_id', $locationId),
                TemplateParam::init('order_id', $orderId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(V1Order::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }
}
