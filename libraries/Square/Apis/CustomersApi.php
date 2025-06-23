<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Apis;

use EDD\Vendor\Core\Request\Parameters\BodyParam;
use EDD\Vendor\Core\Request\Parameters\HeaderParam;
use EDD\Vendor\Core\Request\Parameters\QueryParam;
use EDD\Vendor\Core\Request\Parameters\TemplateParam;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestMethod;
use EDD\Vendor\Square\Http\ApiResponse;
use EDD\Vendor\Square\Models\AddGroupToCustomerResponse;
use EDD\Vendor\Square\Models\BulkCreateCustomersRequest;
use EDD\Vendor\Square\Models\BulkCreateCustomersResponse;
use EDD\Vendor\Square\Models\BulkDeleteCustomersRequest;
use EDD\Vendor\Square\Models\BulkDeleteCustomersResponse;
use EDD\Vendor\Square\Models\BulkRetrieveCustomersRequest;
use EDD\Vendor\Square\Models\BulkRetrieveCustomersResponse;
use EDD\Vendor\Square\Models\BulkUpdateCustomersRequest;
use EDD\Vendor\Square\Models\BulkUpdateCustomersResponse;
use EDD\Vendor\Square\Models\CreateCustomerCardRequest;
use EDD\Vendor\Square\Models\CreateCustomerCardResponse;
use EDD\Vendor\Square\Models\CreateCustomerRequest;
use EDD\Vendor\Square\Models\CreateCustomerResponse;
use EDD\Vendor\Square\Models\DeleteCustomerCardResponse;
use EDD\Vendor\Square\Models\DeleteCustomerResponse;
use EDD\Vendor\Square\Models\ListCustomersResponse;
use EDD\Vendor\Square\Models\RemoveGroupFromCustomerResponse;
use EDD\Vendor\Square\Models\RetrieveCustomerResponse;
use EDD\Vendor\Square\Models\SearchCustomersRequest;
use EDD\Vendor\Square\Models\SearchCustomersResponse;
use EDD\Vendor\Square\Models\UpdateCustomerRequest;
use EDD\Vendor\Square\Models\UpdateCustomerResponse;

class CustomersApi extends BaseApi
{
    /**
     * Lists customer profiles associated with a EDD\Vendor\Square account.
     *
     * Under normal operating conditions, newly created or updated customer profiles become available
     * for the listing operation in well under 30 seconds. Occasionally, propagation of the new or updated
     * profiles can take closer to one minute or longer, especially during network incidents and outages.
     *
     * @param string|null $cursor A pagination cursor returned by a previous call to this endpoint.
     *        Provide this cursor to retrieve the next set of results for your original query.
     *
     *        For more information, see [Pagination](https://developer.squareup.com/docs/build-
     *        basics/common-api-patterns/pagination).
     * @param int|null $limit The maximum number of results to return in a single page. This limit
     *        is advisory. The response might contain more or fewer results.
     *        If the specified limit is less than 1 or greater than 100, EDD\Vendor\Square returns a `400
     *        VALUE_TOO_LOW` or `400 VALUE_TOO_HIGH` error. The default value is 100.
     *
     *        For more information, see [Pagination](https://developer.squareup.com/docs/build-
     *        basics/common-api-patterns/pagination).
     * @param string|null $sortField Indicates how customers should be sorted. The default value is
     *        `DEFAULT`.
     * @param string|null $sortOrder Indicates whether customers should be sorted in ascending
     *        (`ASC`) or
     *        descending (`DESC`) order.
     *
     *        The default value is `ASC`.
     * @param bool|null $count Indicates whether to return the total count of customers in the
     *        `count` field of the response.
     *
     *        The default value is `false`.
     *
     * @return ApiResponse Response from the API call
     */
    public function listCustomers(
        ?string $cursor = null,
        ?int $limit = null,
        ?string $sortField = null,
        ?string $sortOrder = null,
        ?bool $count = false
    ): ApiResponse {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/customers')
            ->auth('global')
            ->parameters(
                QueryParam::init('cursor', $cursor),
                QueryParam::init('limit', $limit),
                QueryParam::init('sort_field', $sortField),
                QueryParam::init('sort_order', $sortOrder),
                QueryParam::init('count', $count)
            );

        $_resHandler = $this->responseHandler()->type(ListCustomersResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Creates a new customer for a business.
     *
     * You must provide at least one of the following values in your request to this
     * endpoint:
     *
     * - `given_name`
     * - `family_name`
     * - `company_name`
     * - `email_address`
     * - `phone_number`
     *
     * @param CreateCustomerRequest $body An object containing the fields to POST for the request.
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function createCustomer(CreateCustomerRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/customers')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(CreateCustomerResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Creates multiple [customer profiles]($m/Customer) for a business.
     *
     * This endpoint takes a map of individual create requests and returns a map of responses.
     *
     * You must provide at least one of the following values in each create request:
     *
     * - `given_name`
     * - `family_name`
     * - `company_name`
     * - `email_address`
     * - `phone_number`
     *
     * @param BulkCreateCustomersRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function bulkCreateCustomers(BulkCreateCustomersRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/customers/bulk-create')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(BulkCreateCustomersResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Deletes multiple customer profiles.
     *
     * The endpoint takes a list of customer IDs and returns a map of responses.
     *
     * @param BulkDeleteCustomersRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function bulkDeleteCustomers(BulkDeleteCustomersRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/customers/bulk-delete')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(BulkDeleteCustomersResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves multiple customer profiles.
     *
     * This endpoint takes a list of customer IDs and returns a map of responses.
     *
     * @param BulkRetrieveCustomersRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function bulkRetrieveCustomers(BulkRetrieveCustomersRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/customers/bulk-retrieve')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(BulkRetrieveCustomersResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Updates multiple customer profiles.
     *
     * This endpoint takes a map of individual update requests and returns a map of responses.
     *
     * You cannot use this endpoint to change cards on file. To make changes, use the [Cards API]($e/Cards)
     * or [Gift Cards API]($e/GiftCards).
     *
     * @param BulkUpdateCustomersRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function bulkUpdateCustomers(BulkUpdateCustomersRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/customers/bulk-update')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(BulkUpdateCustomersResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Searches the customer profiles associated with a EDD\Vendor\Square account using one or more supported query
     * filters.
     *
     * Calling `SearchCustomers` without any explicit query filter returns all
     * customer profiles ordered alphabetically based on `given_name` and
     * `family_name`.
     *
     * Under normal operating conditions, newly created or updated customer profiles become available
     * for the search operation in well under 30 seconds. Occasionally, propagation of the new or updated
     * profiles can take closer to one minute or longer, especially during network incidents and outages.
     *
     * @param SearchCustomersRequest $body An object containing the fields to POST for the request.
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function searchCustomers(SearchCustomersRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/customers/search')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(SearchCustomersResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Deletes a customer profile from a business. This operation also unlinks any associated cards on file.
     *
     * To delete a customer profile that was created by merging existing profiles, you must use the ID of
     * the newly created profile.
     *
     * @param string $customerId The ID of the customer to delete.
     * @param int|null $version The current version of the customer profile. As a best practice, you
     *        should include this parameter to enable [optimistic concurrency](https://developer.
     *        squareup.com/docs/build-basics/common-api-patterns/optimistic-concurrency) control.
     *        For more information, see [Delete a customer profile](https://developer.squareup.
     *        com/docs/customers-api/use-the-api/keep-records#delete-customer-profile).
     *
     * @return ApiResponse Response from the API call
     */
    public function deleteCustomer(string $customerId, ?int $version = null): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::DELETE, '/v2/customers/{customer_id}')
            ->auth('global')
            ->parameters(TemplateParam::init('customer_id', $customerId), QueryParam::init('version', $version));

        $_resHandler = $this->responseHandler()->type(DeleteCustomerResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Returns details for a single customer.
     *
     * @param string $customerId The ID of the customer to retrieve.
     *
     * @return ApiResponse Response from the API call
     */
    public function retrieveCustomer(string $customerId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/customers/{customer_id}')
            ->auth('global')
            ->parameters(TemplateParam::init('customer_id', $customerId));

        $_resHandler = $this->responseHandler()->type(RetrieveCustomerResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Updates a customer profile. This endpoint supports sparse updates, so only new or changed fields are
     * required in the request.
     * To add or update a field, specify the new value. To remove a field, specify `null`.
     *
     * To update a customer profile that was created by merging existing profiles, you must use the ID of
     * the newly created profile.
     *
     * You cannot use this endpoint to change cards on file. To make changes, use the [Cards API]($e/Cards)
     * or [Gift Cards API]($e/GiftCards).
     *
     * @param string $customerId The ID of the customer to update.
     * @param UpdateCustomerRequest $body An object containing the fields to POST for the request.
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function updateCustomer(string $customerId, UpdateCustomerRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::PUT, '/v2/customers/{customer_id}')
            ->auth('global')
            ->parameters(
                TemplateParam::init('customer_id', $customerId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(UpdateCustomerResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Adds a card on file to an existing customer.
     *
     * As with charges, calls to `CreateCustomerCard` are idempotent. Multiple
     * calls with the same card nonce return the same card record that was created
     * with the provided nonce during the _first_ call.
     *
     * @deprecated
     *
     * @param string $customerId The EDD\Vendor\Square ID of the customer profile the card is linked to.
     * @param CreateCustomerCardRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function createCustomerCard(string $customerId, CreateCustomerCardRequest $body): ApiResponse
    {
        trigger_error('Method ' . __METHOD__ . ' is deprecated.', E_USER_DEPRECATED);

        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/customers/{customer_id}/cards')
            ->auth('global')
            ->parameters(
                TemplateParam::init('customer_id', $customerId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(CreateCustomerCardResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Removes a card on file from a customer.
     *
     * @deprecated
     *
     * @param string $customerId The ID of the customer that the card on file belongs to.
     * @param string $cardId The ID of the card on file to delete.
     *
     * @return ApiResponse Response from the API call
     */
    public function deleteCustomerCard(string $customerId, string $cardId): ApiResponse
    {
        trigger_error('Method ' . __METHOD__ . ' is deprecated.', E_USER_DEPRECATED);

        $_reqBuilder = $this->requestBuilder(RequestMethod::DELETE, '/v2/customers/{customer_id}/cards/{card_id}')
            ->auth('global')
            ->parameters(TemplateParam::init('customer_id', $customerId), TemplateParam::init('card_id', $cardId));

        $_resHandler = $this->responseHandler()->type(DeleteCustomerCardResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Removes a group membership from a customer.
     *
     * The customer is identified by the `customer_id` value
     * and the customer group is identified by the `group_id` value.
     *
     * @param string $customerId The ID of the customer to remove from the group.
     * @param string $groupId The ID of the customer group to remove the customer from.
     *
     * @return ApiResponse Response from the API call
     */
    public function removeGroupFromCustomer(string $customerId, string $groupId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(
            RequestMethod::DELETE,
            '/v2/customers/{customer_id}/groups/{group_id}'
        )
            ->auth('global')
            ->parameters(
                TemplateParam::init('customer_id', $customerId),
                TemplateParam::init('group_id', $groupId)
            );

        $_resHandler = $this->responseHandler()->type(RemoveGroupFromCustomerResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Adds a group membership to a customer.
     *
     * The customer is identified by the `customer_id` value
     * and the customer group is identified by the `group_id` value.
     *
     * @param string $customerId The ID of the customer to add to a group.
     * @param string $groupId The ID of the customer group to add the customer to.
     *
     * @return ApiResponse Response from the API call
     */
    public function addGroupToCustomer(string $customerId, string $groupId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::PUT, '/v2/customers/{customer_id}/groups/{group_id}')
            ->auth('global')
            ->parameters(
                TemplateParam::init('customer_id', $customerId),
                TemplateParam::init('group_id', $groupId)
            );

        $_resHandler = $this->responseHandler()->type(AddGroupToCustomerResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }
}
