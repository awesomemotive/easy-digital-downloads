<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Apis;

use EDD\Vendor\Core\Request\Parameters\BodyParam;
use EDD\Vendor\Core\Request\Parameters\HeaderParam;
use EDD\Vendor\Core\Request\Parameters\QueryParam;
use EDD\Vendor\Core\Request\Parameters\TemplateParam;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestMethod;
use EDD\Vendor\Square\Http\ApiResponse;
use EDD\Vendor\Square\Models\BulkRetrieveBookingsRequest;
use EDD\Vendor\Square\Models\BulkRetrieveBookingsResponse;
use EDD\Vendor\Square\Models\BulkRetrieveTeamMemberBookingProfilesRequest;
use EDD\Vendor\Square\Models\BulkRetrieveTeamMemberBookingProfilesResponse;
use EDD\Vendor\Square\Models\CancelBookingRequest;
use EDD\Vendor\Square\Models\CancelBookingResponse;
use EDD\Vendor\Square\Models\CreateBookingRequest;
use EDD\Vendor\Square\Models\CreateBookingResponse;
use EDD\Vendor\Square\Models\ListBookingsResponse;
use EDD\Vendor\Square\Models\ListLocationBookingProfilesResponse;
use EDD\Vendor\Square\Models\ListTeamMemberBookingProfilesResponse;
use EDD\Vendor\Square\Models\RetrieveBookingResponse;
use EDD\Vendor\Square\Models\RetrieveBusinessBookingProfileResponse;
use EDD\Vendor\Square\Models\RetrieveLocationBookingProfileResponse;
use EDD\Vendor\Square\Models\RetrieveTeamMemberBookingProfileResponse;
use EDD\Vendor\Square\Models\SearchAvailabilityRequest;
use EDD\Vendor\Square\Models\SearchAvailabilityResponse;
use EDD\Vendor\Square\Models\UpdateBookingRequest;
use EDD\Vendor\Square\Models\UpdateBookingResponse;

class BookingsApi extends BaseApi
{
    /**
     * Retrieve a collection of bookings.
     *
     * To call this endpoint with buyer-level permissions, set `APPOINTMENTS_READ` for the OAuth scope.
     * To call this endpoint with seller-level permissions, set `APPOINTMENTS_ALL_READ` and
     * `APPOINTMENTS_READ` for the OAuth scope.
     *
     * @param int|null $limit The maximum number of results per page to return in a paged response.
     * @param string|null $cursor The pagination cursor from the preceding response to return the
     *        next page of the results. Do not set this when retrieving the first page of the
     *        results.
     * @param string|null $customerId The [customer](entity:Customer) for whom to retrieve bookings.
     *        If this is not set, bookings for all customers are retrieved.
     * @param string|null $teamMemberId The team member for whom to retrieve bookings. If this is
     *        not set, bookings of all members are retrieved.
     * @param string|null $locationId The location for which to retrieve bookings. If this is not
     *        set, all locations' bookings are retrieved.
     * @param string|null $startAtMin The RFC 3339 timestamp specifying the earliest of the start
     *        time. If this is not set, the current time is used.
     * @param string|null $startAtMax The RFC 3339 timestamp specifying the latest of the start
     *        time. If this is not set, the time of 31 days after `start_at_min` is used.
     *
     * @return ApiResponse Response from the API call
     */
    public function listBookings(
        ?int $limit = null,
        ?string $cursor = null,
        ?string $customerId = null,
        ?string $teamMemberId = null,
        ?string $locationId = null,
        ?string $startAtMin = null,
        ?string $startAtMax = null
    ): ApiResponse {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/bookings')
            ->auth('global')
            ->parameters(
                QueryParam::init('limit', $limit),
                QueryParam::init('cursor', $cursor),
                QueryParam::init('customer_id', $customerId),
                QueryParam::init('team_member_id', $teamMemberId),
                QueryParam::init('location_id', $locationId),
                QueryParam::init('start_at_min', $startAtMin),
                QueryParam::init('start_at_max', $startAtMax)
            );

        $_resHandler = $this->responseHandler()->type(ListBookingsResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Creates a booking.
     *
     * The required input must include the following:
     * - `Booking.location_id`
     * - `Booking.start_at`
     * - `Booking.AppointmentSegment.team_member_id`
     * - `Booking.AppointmentSegment.service_variation_id`
     * - `Booking.AppointmentSegment.service_variation_version`
     *
     * To call this endpoint with buyer-level permissions, set `APPOINTMENTS_WRITE` for the OAuth scope.
     * To call this endpoint with seller-level permissions, set `APPOINTMENTS_ALL_WRITE` and
     * `APPOINTMENTS_WRITE` for the OAuth scope.
     *
     * For calls to this endpoint with seller-level permissions to succeed, the seller must have subscribed
     * to *Appointments Plus*
     * or *Appointments Premium*.
     *
     * @param CreateBookingRequest $body An object containing the fields to POST for the request.
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function createBooking(CreateBookingRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/bookings')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(CreateBookingResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Searches for availabilities for booking.
     *
     * To call this endpoint with buyer-level permissions, set `APPOINTMENTS_READ` for the OAuth scope.
     * To call this endpoint with seller-level permissions, set `APPOINTMENTS_ALL_READ` and
     * `APPOINTMENTS_READ` for the OAuth scope.
     *
     * @param SearchAvailabilityRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function searchAvailability(SearchAvailabilityRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/bookings/availability/search')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(SearchAvailabilityResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Bulk-Retrieves a list of bookings by booking IDs.
     *
     * To call this endpoint with buyer-level permissions, set `APPOINTMENTS_READ` for the OAuth scope.
     * To call this endpoint with seller-level permissions, set `APPOINTMENTS_ALL_READ` and
     * `APPOINTMENTS_READ` for the OAuth scope.
     *
     * @param BulkRetrieveBookingsRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function bulkRetrieveBookings(BulkRetrieveBookingsRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/bookings/bulk-retrieve')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(BulkRetrieveBookingsResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves a seller's booking profile.
     *
     * @return ApiResponse Response from the API call
     */
    public function retrieveBusinessBookingProfile(): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/bookings/business-booking-profile')
            ->auth('global');

        $_resHandler = $this->responseHandler()
            ->type(RetrieveBusinessBookingProfileResponse::class)
            ->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Lists location booking profiles of a seller.
     *
     * @param int|null $limit The maximum number of results to return in a paged response.
     * @param string|null $cursor The pagination cursor from the preceding response to return the
     *        next page of the results. Do not set this when retrieving the first page of the
     *        results.
     *
     * @return ApiResponse Response from the API call
     */
    public function listLocationBookingProfiles(?int $limit = null, ?string $cursor = null): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/bookings/location-booking-profiles')
            ->auth('global')
            ->parameters(QueryParam::init('limit', $limit), QueryParam::init('cursor', $cursor));

        $_resHandler = $this->responseHandler()->type(ListLocationBookingProfilesResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves a seller's location booking profile.
     *
     * @param string $locationId The ID of the location to retrieve the booking profile.
     *
     * @return ApiResponse Response from the API call
     */
    public function retrieveLocationBookingProfile(string $locationId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(
            RequestMethod::GET,
            '/v2/bookings/location-booking-profiles/{location_id}'
        )->auth('global')->parameters(TemplateParam::init('location_id', $locationId));

        $_resHandler = $this->responseHandler()
            ->type(RetrieveLocationBookingProfileResponse::class)
            ->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Lists booking profiles for team members.
     *
     * @param bool|null $bookableOnly Indicates whether to include only bookable team members in the
     *        returned result (`true`) or not (`false`).
     * @param int|null $limit The maximum number of results to return in a paged response.
     * @param string|null $cursor The pagination cursor from the preceding response to return the
     *        next page of the results. Do not set this when retrieving the first page of the
     *        results.
     * @param string|null $locationId Indicates whether to include only team members enabled at the
     *        given location in the returned result.
     *
     * @return ApiResponse Response from the API call
     */
    public function listTeamMemberBookingProfiles(
        ?bool $bookableOnly = false,
        ?int $limit = null,
        ?string $cursor = null,
        ?string $locationId = null
    ): ApiResponse {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/bookings/team-member-booking-profiles')
            ->auth('global')
            ->parameters(
                QueryParam::init('bookable_only', $bookableOnly),
                QueryParam::init('limit', $limit),
                QueryParam::init('cursor', $cursor),
                QueryParam::init('location_id', $locationId)
            );

        $_resHandler = $this->responseHandler()
            ->type(ListTeamMemberBookingProfilesResponse::class)
            ->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves one or more team members' booking profiles.
     *
     * @param BulkRetrieveTeamMemberBookingProfilesRequest $body An object containing the fields to
     *        POST for the request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function bulkRetrieveTeamMemberBookingProfiles(
        BulkRetrieveTeamMemberBookingProfilesRequest $body
    ): ApiResponse {
        $_reqBuilder = $this->requestBuilder(
            RequestMethod::POST,
            '/v2/bookings/team-member-booking-profiles/bulk-retrieve'
        )->auth('global')->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()
            ->type(BulkRetrieveTeamMemberBookingProfilesResponse::class)
            ->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves a team member's booking profile.
     *
     * @param string $teamMemberId The ID of the team member to retrieve.
     *
     * @return ApiResponse Response from the API call
     */
    public function retrieveTeamMemberBookingProfile(string $teamMemberId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(
            RequestMethod::GET,
            '/v2/bookings/team-member-booking-profiles/{team_member_id}'
        )->auth('global')->parameters(TemplateParam::init('team_member_id', $teamMemberId));

        $_resHandler = $this->responseHandler()
            ->type(RetrieveTeamMemberBookingProfileResponse::class)
            ->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves a booking.
     *
     * To call this endpoint with buyer-level permissions, set `APPOINTMENTS_READ` for the OAuth scope.
     * To call this endpoint with seller-level permissions, set `APPOINTMENTS_ALL_READ` and
     * `APPOINTMENTS_READ` for the OAuth scope.
     *
     * @param string $bookingId The ID of the [Booking](entity:Booking) object representing the
     *        to-be-retrieved booking.
     *
     * @return ApiResponse Response from the API call
     */
    public function retrieveBooking(string $bookingId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/bookings/{booking_id}')
            ->auth('global')
            ->parameters(TemplateParam::init('booking_id', $bookingId));

        $_resHandler = $this->responseHandler()->type(RetrieveBookingResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Updates a booking.
     *
     * To call this endpoint with buyer-level permissions, set `APPOINTMENTS_WRITE` for the OAuth scope.
     * To call this endpoint with seller-level permissions, set `APPOINTMENTS_ALL_WRITE` and
     * `APPOINTMENTS_WRITE` for the OAuth scope.
     *
     * For calls to this endpoint with seller-level permissions to succeed, the seller must have subscribed
     * to *Appointments Plus*
     * or *Appointments Premium*.
     *
     * @param string $bookingId The ID of the [Booking](entity:Booking) object representing the
     *        to-be-updated booking.
     * @param UpdateBookingRequest $body An object containing the fields to POST for the request.
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function updateBooking(string $bookingId, UpdateBookingRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::PUT, '/v2/bookings/{booking_id}')
            ->auth('global')
            ->parameters(
                TemplateParam::init('booking_id', $bookingId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(UpdateBookingResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Cancels an existing booking.
     *
     * To call this endpoint with buyer-level permissions, set `APPOINTMENTS_WRITE` for the OAuth scope.
     * To call this endpoint with seller-level permissions, set `APPOINTMENTS_ALL_WRITE` and
     * `APPOINTMENTS_WRITE` for the OAuth scope.
     *
     * For calls to this endpoint with seller-level permissions to succeed, the seller must have subscribed
     * to *Appointments Plus*
     * or *Appointments Premium*.
     *
     * @param string $bookingId The ID of the [Booking](entity:Booking) object representing the
     *        to-be-cancelled booking.
     * @param CancelBookingRequest $body An object containing the fields to POST for the request.
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function cancelBooking(string $bookingId, CancelBookingRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/bookings/{booking_id}/cancel')
            ->auth('global')
            ->parameters(
                TemplateParam::init('booking_id', $bookingId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(CancelBookingResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }
}
