<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Apis;

use EDD\Vendor\Core\Request\Parameters\BodyParam;
use EDD\Vendor\Core\Request\Parameters\HeaderParam;
use EDD\Vendor\Core\Request\Parameters\QueryParam;
use EDD\Vendor\Core\Request\Parameters\TemplateParam;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestMethod;
use EDD\Vendor\Square\Http\ApiResponse;
use EDD\Vendor\Square\Models\CreateCustomerGroupRequest;
use EDD\Vendor\Square\Models\CreateCustomerGroupResponse;
use EDD\Vendor\Square\Models\DeleteCustomerGroupResponse;
use EDD\Vendor\Square\Models\ListCustomerGroupsResponse;
use EDD\Vendor\Square\Models\RetrieveCustomerGroupResponse;
use EDD\Vendor\Square\Models\UpdateCustomerGroupRequest;
use EDD\Vendor\Square\Models\UpdateCustomerGroupResponse;

class CustomerGroupsApi extends BaseApi
{
    /**
     * Retrieves the list of customer groups of a business.
     *
     * @param string|null $cursor A pagination cursor returned by a previous call to this endpoint.
     *        Provide this cursor to retrieve the next set of results for your original query.
     *
     *        For more information, see [Pagination](https://developer.squareup.com/docs/build-
     *        basics/common-api-patterns/pagination).
     * @param int|null $limit The maximum number of results to return in a single page. This limit
     *        is advisory. The response might contain more or fewer results.
     *        If the limit is less than 1 or greater than 50, EDD\Vendor\Square returns a `400 VALUE_TOO_LOW`
     *        or `400 VALUE_TOO_HIGH` error. The default value is 50.
     *
     *        For more information, see [Pagination](https://developer.squareup.com/docs/build-
     *        basics/common-api-patterns/pagination).
     *
     * @return ApiResponse Response from the API call
     */
    public function listCustomerGroups(?string $cursor = null, ?int $limit = null): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/customers/groups')
            ->auth('global')
            ->parameters(QueryParam::init('cursor', $cursor), QueryParam::init('limit', $limit));

        $_resHandler = $this->responseHandler()->type(ListCustomerGroupsResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Creates a new customer group for a business.
     *
     * The request must include the `name` value of the group.
     *
     * @param CreateCustomerGroupRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function createCustomerGroup(CreateCustomerGroupRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/customers/groups')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(CreateCustomerGroupResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Deletes a customer group as identified by the `group_id` value.
     *
     * @param string $groupId The ID of the customer group to delete.
     *
     * @return ApiResponse Response from the API call
     */
    public function deleteCustomerGroup(string $groupId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::DELETE, '/v2/customers/groups/{group_id}')
            ->auth('global')
            ->parameters(TemplateParam::init('group_id', $groupId));

        $_resHandler = $this->responseHandler()->type(DeleteCustomerGroupResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves a specific customer group as identified by the `group_id` value.
     *
     * @param string $groupId The ID of the customer group to retrieve.
     *
     * @return ApiResponse Response from the API call
     */
    public function retrieveCustomerGroup(string $groupId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/customers/groups/{group_id}')
            ->auth('global')
            ->parameters(TemplateParam::init('group_id', $groupId));

        $_resHandler = $this->responseHandler()->type(RetrieveCustomerGroupResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Updates a customer group as identified by the `group_id` value.
     *
     * @param string $groupId The ID of the customer group to update.
     * @param UpdateCustomerGroupRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function updateCustomerGroup(string $groupId, UpdateCustomerGroupRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::PUT, '/v2/customers/groups/{group_id}')
            ->auth('global')
            ->parameters(
                TemplateParam::init('group_id', $groupId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(UpdateCustomerGroupResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }
}
