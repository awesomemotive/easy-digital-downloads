<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Apis;

use EDD\Vendor\Core\Request\Parameters\BodyParam;
use EDD\Vendor\Core\Request\Parameters\HeaderParam;
use EDD\Vendor\Core\Request\Parameters\QueryParam;
use EDD\Vendor\Core\Request\Parameters\TemplateParam;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestMethod;
use EDD\Vendor\Square\Http\ApiResponse;
use EDD\Vendor\Square\Models\BulkDeleteOrderCustomAttributesRequest;
use EDD\Vendor\Square\Models\BulkDeleteOrderCustomAttributesResponse;
use EDD\Vendor\Square\Models\BulkUpsertOrderCustomAttributesRequest;
use EDD\Vendor\Square\Models\BulkUpsertOrderCustomAttributesResponse;
use EDD\Vendor\Square\Models\CreateOrderCustomAttributeDefinitionRequest;
use EDD\Vendor\Square\Models\CreateOrderCustomAttributeDefinitionResponse;
use EDD\Vendor\Square\Models\DeleteOrderCustomAttributeDefinitionResponse;
use EDD\Vendor\Square\Models\DeleteOrderCustomAttributeResponse;
use EDD\Vendor\Square\Models\ListOrderCustomAttributeDefinitionsResponse;
use EDD\Vendor\Square\Models\ListOrderCustomAttributesResponse;
use EDD\Vendor\Square\Models\RetrieveOrderCustomAttributeDefinitionResponse;
use EDD\Vendor\Square\Models\RetrieveOrderCustomAttributeResponse;
use EDD\Vendor\Square\Models\UpdateOrderCustomAttributeDefinitionRequest;
use EDD\Vendor\Square\Models\UpdateOrderCustomAttributeDefinitionResponse;
use EDD\Vendor\Square\Models\UpsertOrderCustomAttributeRequest;
use EDD\Vendor\Square\Models\UpsertOrderCustomAttributeResponse;

class OrderCustomAttributesApi extends BaseApi
{
    /**
     * Lists the order-related [custom attribute definitions]($m/CustomAttributeDefinition) that belong to
     * a EDD\Vendor\Square seller account.
     *
     * When all response pages are retrieved, the results include all custom attribute definitions
     * that are visible to the requesting application, including those that are created by other
     * applications and set to `VISIBILITY_READ_ONLY` or `VISIBILITY_READ_WRITE_VALUES`. Note that
     * seller-defined custom attributes (also known as custom fields) are always set to
     * `VISIBILITY_READ_WRITE_VALUES`.
     *
     * @param string|null $visibilityFilter Requests that all of the custom attributes be returned,
     *        or only those that are read-only or read-write.
     * @param string|null $cursor The cursor returned in the paged response from the previous call
     *        to this endpoint.
     *        Provide this cursor to retrieve the next page of results for your original request.
     *        For more information, see [Pagination](https://developer.squareup.com/docs/working-
     *        with-apis/pagination).
     * @param int|null $limit The maximum number of results to return in a single paged response.
     *        This limit is advisory.
     *        The response might contain more or fewer results. The minimum value is 1 and the
     *        maximum value is 100.
     *        The default value is 20.
     *        For more information, see [Pagination](https://developer.squareup.com/docs/working-
     *        with-apis/pagination).
     *
     * @return ApiResponse Response from the API call
     */
    public function listOrderCustomAttributeDefinitions(
        ?string $visibilityFilter = null,
        ?string $cursor = null,
        ?int $limit = null
    ): ApiResponse {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/orders/custom-attribute-definitions')
            ->auth('global')
            ->parameters(
                QueryParam::init('visibility_filter', $visibilityFilter),
                QueryParam::init('cursor', $cursor),
                QueryParam::init('limit', $limit)
            );

        $_resHandler = $this->responseHandler()
            ->type(ListOrderCustomAttributeDefinitionsResponse::class)
            ->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Creates an order-related custom attribute definition.  Use this endpoint to
     * define a custom attribute that can be associated with orders.
     *
     * After creating a custom attribute definition, you can set the custom attribute for orders
     * in the EDD\Vendor\Square seller account.
     *
     * @param CreateOrderCustomAttributeDefinitionRequest $body An object containing the fields to
     *        POST for the request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function createOrderCustomAttributeDefinition(
        CreateOrderCustomAttributeDefinitionRequest $body
    ): ApiResponse {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/orders/custom-attribute-definitions')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()
            ->type(CreateOrderCustomAttributeDefinitionResponse::class)
            ->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Deletes an order-related [custom attribute definition]($m/CustomAttributeDefinition) from a EDD\Vendor\Square
     * seller account.
     *
     * Only the definition owner can delete a custom attribute definition.
     *
     * @param string $key The key of the custom attribute definition to delete.
     *
     * @return ApiResponse Response from the API call
     */
    public function deleteOrderCustomAttributeDefinition(string $key): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(
            RequestMethod::DELETE,
            '/v2/orders/custom-attribute-definitions/{key}'
        )->auth('global')->parameters(TemplateParam::init('key', $key));

        $_resHandler = $this->responseHandler()
            ->type(DeleteOrderCustomAttributeDefinitionResponse::class)
            ->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves an order-related [custom attribute definition]($m/CustomAttributeDefinition) from a EDD\Vendor\Square
     * seller account.
     *
     * To retrieve a custom attribute definition created by another application, the `visibility`
     * setting must be `VISIBILITY_READ_ONLY` or `VISIBILITY_READ_WRITE_VALUES`. Note that seller-defined
     * custom attributes
     * (also known as custom fields) are always set to `VISIBILITY_READ_WRITE_VALUES`.
     *
     * @param string $key The key of the custom attribute definition to retrieve.
     * @param int|null $version To enable [optimistic
     *        concurrency](https://developer.squareup.com/docs/build-basics/common-api-
     *        patterns/optimistic-concurrency)
     *        control, include this optional field and specify the current version of the custom
     *        attribute.
     *
     * @return ApiResponse Response from the API call
     */
    public function retrieveOrderCustomAttributeDefinition(string $key, ?int $version = null): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/orders/custom-attribute-definitions/{key}')
            ->auth('global')
            ->parameters(TemplateParam::init('key', $key), QueryParam::init('version', $version));

        $_resHandler = $this->responseHandler()
            ->type(RetrieveOrderCustomAttributeDefinitionResponse::class)
            ->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Updates an order-related custom attribute definition for a EDD\Vendor\Square seller account.
     *
     * Only the definition owner can update a custom attribute definition. Note that sellers can view all
     * custom attributes in exported customer data, including those set to `VISIBILITY_HIDDEN`.
     *
     * @param string $key The key of the custom attribute definition to update.
     * @param UpdateOrderCustomAttributeDefinitionRequest $body An object containing the fields to
     *        POST for the request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function updateOrderCustomAttributeDefinition(
        string $key,
        UpdateOrderCustomAttributeDefinitionRequest $body
    ): ApiResponse {
        $_reqBuilder = $this->requestBuilder(RequestMethod::PUT, '/v2/orders/custom-attribute-definitions/{key}')
            ->auth('global')
            ->parameters(
                TemplateParam::init('key', $key),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()
            ->type(UpdateOrderCustomAttributeDefinitionResponse::class)
            ->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Deletes order [custom attributes]($m/CustomAttribute) as a bulk operation.
     *
     * Use this endpoint to delete one or more custom attributes from one or more orders.
     * A custom attribute is based on a custom attribute definition in a EDD\Vendor\Square seller account.  (To create
     * a
     * custom attribute definition, use the
     * [CreateOrderCustomAttributeDefinition]($e/OrderCustomAttributes/CreateOrderCustomAttributeDefinition
     * ) endpoint.)
     *
     * This `BulkDeleteOrderCustomAttributes` endpoint accepts a map of 1 to 25 individual delete
     * requests and returns a map of individual delete responses. Each delete request has a unique ID
     * and provides an order ID and custom attribute. Each delete response is returned with the ID
     * of the corresponding request.
     *
     * To delete a custom attribute owned by another application, the `visibility` setting
     * must be `VISIBILITY_READ_WRITE_VALUES`. Note that seller-defined custom attributes
     * (also known as custom fields) are always set to `VISIBILITY_READ_WRITE_VALUES`.
     *
     * @param BulkDeleteOrderCustomAttributesRequest $body An object containing the fields to POST
     *        for the request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function bulkDeleteOrderCustomAttributes(BulkDeleteOrderCustomAttributesRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/orders/custom-attributes/bulk-delete')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()
            ->type(BulkDeleteOrderCustomAttributesResponse::class)
            ->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Creates or updates order [custom attributes]($m/CustomAttribute) as a bulk operation.
     *
     * Use this endpoint to delete one or more custom attributes from one or more orders.
     * A custom attribute is based on a custom attribute definition in a EDD\Vendor\Square seller account.  (To create
     * a
     * custom attribute definition, use the
     * [CreateOrderCustomAttributeDefinition]($e/OrderCustomAttributes/CreateOrderCustomAttributeDefinition
     * ) endpoint.)
     *
     * This `BulkUpsertOrderCustomAttributes` endpoint accepts a map of 1 to 25 individual upsert
     * requests and returns a map of individual upsert responses. Each upsert request has a unique ID
     * and provides an order ID and custom attribute. Each upsert response is returned with the ID
     * of the corresponding request.
     *
     * To create or update a custom attribute owned by another application, the `visibility` setting
     * must be `VISIBILITY_READ_WRITE_VALUES`. Note that seller-defined custom attributes
     * (also known as custom fields) are always set to `VISIBILITY_READ_WRITE_VALUES`.
     *
     * @param BulkUpsertOrderCustomAttributesRequest $body An object containing the fields to POST
     *        for the request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function bulkUpsertOrderCustomAttributes(BulkUpsertOrderCustomAttributesRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/orders/custom-attributes/bulk-upsert')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()
            ->type(BulkUpsertOrderCustomAttributesResponse::class)
            ->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Lists the [custom attributes]($m/CustomAttribute) associated with an order.
     *
     * You can use the `with_definitions` query parameter to also retrieve custom attribute definitions
     * in the same call.
     *
     * When all response pages are retrieved, the results include all custom attributes that are
     * visible to the requesting application, including those that are owned by other applications
     * and set to `VISIBILITY_READ_ONLY` or `VISIBILITY_READ_WRITE_VALUES`.
     *
     * @param string $orderId The ID of the target [order](entity:Order).
     * @param string|null $visibilityFilter Requests that all of the custom attributes be returned,
     *        or only those that are read-only or read-write.
     * @param string|null $cursor The cursor returned in the paged response from the previous call
     *        to this endpoint.
     *        Provide this cursor to retrieve the next page of results for your original request.
     *        For more information, see [Pagination](https://developer.squareup.com/docs/working-
     *        with-apis/pagination).
     * @param int|null $limit The maximum number of results to return in a single paged response.
     *        This limit is advisory.
     *        The response might contain more or fewer results. The minimum value is 1 and the
     *        maximum value is 100.
     *        The default value is 20.
     *        For more information, see [Pagination](https://developer.squareup.com/docs/working-
     *        with-apis/pagination).
     * @param bool|null $withDefinitions Indicates whether to return the [custom attribute
     *        definition](entity:CustomAttributeDefinition) in the `definition` field of each
     *        custom attribute. Set this parameter to `true` to get the name and description of
     *        each custom attribute,
     *        information about the data type, or other definition details. The default value is
     *        `false`.
     *
     * @return ApiResponse Response from the API call
     */
    public function listOrderCustomAttributes(
        string $orderId,
        ?string $visibilityFilter = null,
        ?string $cursor = null,
        ?int $limit = null,
        ?bool $withDefinitions = false
    ): ApiResponse {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/orders/{order_id}/custom-attributes')
            ->auth('global')
            ->parameters(
                TemplateParam::init('order_id', $orderId),
                QueryParam::init('visibility_filter', $visibilityFilter),
                QueryParam::init('cursor', $cursor),
                QueryParam::init('limit', $limit),
                QueryParam::init('with_definitions', $withDefinitions)
            );

        $_resHandler = $this->responseHandler()->type(ListOrderCustomAttributesResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Deletes a [custom attribute]($m/CustomAttribute) associated with a customer profile.
     *
     * To delete a custom attribute owned by another application, the `visibility` setting must be
     * `VISIBILITY_READ_WRITE_VALUES`. Note that seller-defined custom attributes
     * (also known as custom fields) are always set to `VISIBILITY_READ_WRITE_VALUES`.
     *
     * @param string $orderId The ID of the target [order](entity:Order).
     * @param string $customAttributeKey The key of the custom attribute to delete. This key must
     *        match the key of an
     *        existing custom attribute definition.
     *
     * @return ApiResponse Response from the API call
     */
    public function deleteOrderCustomAttribute(string $orderId, string $customAttributeKey): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(
            RequestMethod::DELETE,
            '/v2/orders/{order_id}/custom-attributes/{custom_attribute_key}'
        )
            ->auth('global')
            ->parameters(
                TemplateParam::init('order_id', $orderId),
                TemplateParam::init('custom_attribute_key', $customAttributeKey)
            );

        $_resHandler = $this->responseHandler()->type(DeleteOrderCustomAttributeResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves a [custom attribute]($m/CustomAttribute) associated with an order.
     *
     * You can use the `with_definition` query parameter to also retrieve the custom attribute definition
     * in the same call.
     *
     * To retrieve a custom attribute owned by another application, the `visibility` setting must be
     * `VISIBILITY_READ_ONLY` or `VISIBILITY_READ_WRITE_VALUES`. Note that seller-defined custom
     * attributes
     * also known as custom fields) are always set to `VISIBILITY_READ_WRITE_VALUES`.
     *
     * @param string $orderId The ID of the target [order](entity:Order).
     * @param string $customAttributeKey The key of the custom attribute to retrieve. This key must
     *        match the key of an
     *        existing custom attribute definition.
     * @param int|null $version To enable [optimistic
     *        concurrency](https://developer.squareup.com/docs/build-basics/common-api-
     *        patterns/optimistic-concurrency)
     *        control, include this optional field and specify the current version of the custom
     *        attribute.
     * @param bool|null $withDefinition Indicates whether to return the [custom attribute
     *        definition](entity:CustomAttributeDefinition) in the `definition` field of each
     *        custom attribute. Set this parameter to `true` to get the name and description of
     *        each custom attribute,
     *        information about the data type, or other definition details. The default value is
     *        `false`.
     *
     * @return ApiResponse Response from the API call
     */
    public function retrieveOrderCustomAttribute(
        string $orderId,
        string $customAttributeKey,
        ?int $version = null,
        ?bool $withDefinition = false
    ): ApiResponse {
        $_reqBuilder = $this->requestBuilder(
            RequestMethod::GET,
            '/v2/orders/{order_id}/custom-attributes/{custom_attribute_key}'
        )
            ->auth('global')
            ->parameters(
                TemplateParam::init('order_id', $orderId),
                TemplateParam::init('custom_attribute_key', $customAttributeKey),
                QueryParam::init('version', $version),
                QueryParam::init('with_definition', $withDefinition)
            );

        $_resHandler = $this->responseHandler()
            ->type(RetrieveOrderCustomAttributeResponse::class)
            ->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Creates or updates a [custom attribute]($m/CustomAttribute) for an order.
     *
     * Use this endpoint to set the value of a custom attribute for a specific order.
     * A custom attribute is based on a custom attribute definition in a EDD\Vendor\Square seller account. (To create
     * a
     * custom attribute definition, use the
     * [CreateOrderCustomAttributeDefinition]($e/OrderCustomAttributes/CreateOrderCustomAttributeDefinition
     * ) endpoint.)
     *
     * To create or update a custom attribute owned by another application, the `visibility` setting
     * must be `VISIBILITY_READ_WRITE_VALUES`. Note that seller-defined custom attributes
     * (also known as custom fields) are always set to `VISIBILITY_READ_WRITE_VALUES`.
     *
     * @param string $orderId The ID of the target [order](entity:Order).
     * @param string $customAttributeKey The key of the custom attribute to create or update. This
     *        key must match the key
     *        of an existing custom attribute definition.
     * @param UpsertOrderCustomAttributeRequest $body An object containing the fields to POST for
     *        the request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function upsertOrderCustomAttribute(
        string $orderId,
        string $customAttributeKey,
        UpsertOrderCustomAttributeRequest $body
    ): ApiResponse {
        $_reqBuilder = $this->requestBuilder(
            RequestMethod::POST,
            '/v2/orders/{order_id}/custom-attributes/{custom_attribute_key}'
        )
            ->auth('global')
            ->parameters(
                TemplateParam::init('order_id', $orderId),
                TemplateParam::init('custom_attribute_key', $customAttributeKey),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(UpsertOrderCustomAttributeResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }
}
