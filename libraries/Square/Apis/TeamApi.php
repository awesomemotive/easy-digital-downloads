<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Apis;

use EDD\Vendor\Core\Request\Parameters\BodyParam;
use EDD\Vendor\Core\Request\Parameters\HeaderParam;
use EDD\Vendor\Core\Request\Parameters\QueryParam;
use EDD\Vendor\Core\Request\Parameters\TemplateParam;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestMethod;
use EDD\Vendor\Square\Http\ApiResponse;
use EDD\Vendor\Square\Models\BulkCreateTeamMembersRequest;
use EDD\Vendor\Square\Models\BulkCreateTeamMembersResponse;
use EDD\Vendor\Square\Models\BulkUpdateTeamMembersRequest;
use EDD\Vendor\Square\Models\BulkUpdateTeamMembersResponse;
use EDD\Vendor\Square\Models\CreateJobRequest;
use EDD\Vendor\Square\Models\CreateJobResponse;
use EDD\Vendor\Square\Models\CreateTeamMemberRequest;
use EDD\Vendor\Square\Models\CreateTeamMemberResponse;
use EDD\Vendor\Square\Models\ListJobsResponse;
use EDD\Vendor\Square\Models\RetrieveJobResponse;
use EDD\Vendor\Square\Models\RetrieveTeamMemberResponse;
use EDD\Vendor\Square\Models\RetrieveWageSettingResponse;
use EDD\Vendor\Square\Models\SearchTeamMembersRequest;
use EDD\Vendor\Square\Models\SearchTeamMembersResponse;
use EDD\Vendor\Square\Models\UpdateJobRequest;
use EDD\Vendor\Square\Models\UpdateJobResponse;
use EDD\Vendor\Square\Models\UpdateTeamMemberRequest;
use EDD\Vendor\Square\Models\UpdateTeamMemberResponse;
use EDD\Vendor\Square\Models\UpdateWageSettingRequest;
use EDD\Vendor\Square\Models\UpdateWageSettingResponse;

class TeamApi extends BaseApi
{
    /**
     * Creates a single `TeamMember` object. The `TeamMember` object is returned on successful creates.
     * You must provide the following values in your request to this endpoint:
     * - `given_name`
     * - `family_name`
     *
     * Learn about [Troubleshooting the Team API](https://developer.squareup.
     * com/docs/team/troubleshooting#createteammember).
     *
     * @param CreateTeamMemberRequest $body An object containing the fields to POST for the request.
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function createTeamMember(CreateTeamMemberRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/team-members')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(CreateTeamMemberResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Creates multiple `TeamMember` objects. The created `TeamMember` objects are returned on successful
     * creates.
     * This process is non-transactional and processes as much of the request as possible. If one of the
     * creates in
     * the request cannot be successfully processed, the request is not marked as failed, but the body of
     * the response
     * contains explicit error information for the failed create.
     *
     * Learn about [Troubleshooting the Team API](https://developer.squareup.
     * com/docs/team/troubleshooting#bulk-create-team-members).
     *
     * @param BulkCreateTeamMembersRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function bulkCreateTeamMembers(BulkCreateTeamMembersRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/team-members/bulk-create')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(BulkCreateTeamMembersResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Updates multiple `TeamMember` objects. The updated `TeamMember` objects are returned on successful
     * updates.
     * This process is non-transactional and processes as much of the request as possible. If one of the
     * updates in
     * the request cannot be successfully processed, the request is not marked as failed, but the body of
     * the response
     * contains explicit error information for the failed update.
     * Learn about [Troubleshooting the Team API](https://developer.squareup.
     * com/docs/team/troubleshooting#bulk-update-team-members).
     *
     * @param BulkUpdateTeamMembersRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function bulkUpdateTeamMembers(BulkUpdateTeamMembersRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/team-members/bulk-update')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(BulkUpdateTeamMembersResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Lists jobs in a seller account. Results are sorted by title in ascending order.
     *
     * @param string|null $cursor The pagination cursor returned by the previous call to this
     *        endpoint. Provide this
     *        cursor to retrieve the next page of results for your original request. For more
     *        information,
     *        see [Pagination](https://developer.squareup.com/docs/build-basics/common-api-
     *        patterns/pagination).
     *
     * @return ApiResponse Response from the API call
     */
    public function listJobs(?string $cursor = null): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/team-members/jobs')
            ->auth('global')
            ->parameters(QueryParam::init('cursor', $cursor));

        $_resHandler = $this->responseHandler()->type(ListJobsResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Creates a job in a seller account. A job defines a title and tip eligibility. Note that
     * compensation is defined in a [job assignment]($m/JobAssignment) in a team member's wage setting.
     *
     * @param CreateJobRequest $body An object containing the fields to POST for the request. See
     *        the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function createJob(CreateJobRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/team-members/jobs')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(CreateJobResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves a specified job.
     *
     * @param string $jobId The ID of the job to retrieve.
     *
     * @return ApiResponse Response from the API call
     */
    public function retrieveJob(string $jobId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/team-members/jobs/{job_id}')
            ->auth('global')
            ->parameters(TemplateParam::init('job_id', $jobId));

        $_resHandler = $this->responseHandler()->type(RetrieveJobResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Updates the title or tip eligibility of a job. Changes to the title propagate to all
     * `JobAssignment`, `Shift`, and `TeamMemberWage` objects that reference the job ID. Changes to
     * tip eligibility propagate to all `TeamMemberWage` objects that reference the job ID.
     *
     * @param string $jobId The ID of the job to update.
     * @param UpdateJobRequest $body An object containing the fields to POST for the request. See
     *        the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function updateJob(string $jobId, UpdateJobRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::PUT, '/v2/team-members/jobs/{job_id}')
            ->auth('global')
            ->parameters(
                TemplateParam::init('job_id', $jobId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(UpdateJobResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Returns a paginated list of `TeamMember` objects for a business.
     * The list can be filtered by location IDs, `ACTIVE` or `INACTIVE` status, or whether
     * the team member is the EDD\Vendor\Square account owner.
     *
     * @param SearchTeamMembersRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function searchTeamMembers(SearchTeamMembersRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/team-members/search')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(SearchTeamMembersResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves a `TeamMember` object for the given `TeamMember.id`.
     * Learn about [Troubleshooting the Team API](https://developer.squareup.
     * com/docs/team/troubleshooting#retrieve-a-team-member).
     *
     * @param string $teamMemberId The ID of the team member to retrieve.
     *
     * @return ApiResponse Response from the API call
     */
    public function retrieveTeamMember(string $teamMemberId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/team-members/{team_member_id}')
            ->auth('global')
            ->parameters(TemplateParam::init('team_member_id', $teamMemberId));

        $_resHandler = $this->responseHandler()->type(RetrieveTeamMemberResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Updates a single `TeamMember` object. The `TeamMember` object is returned on successful updates.
     * Learn about [Troubleshooting the Team API](https://developer.squareup.
     * com/docs/team/troubleshooting#update-a-team-member).
     *
     * @param string $teamMemberId The ID of the team member to update.
     * @param UpdateTeamMemberRequest $body An object containing the fields to POST for the request.
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function updateTeamMember(string $teamMemberId, UpdateTeamMemberRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::PUT, '/v2/team-members/{team_member_id}')
            ->auth('global')
            ->parameters(
                TemplateParam::init('team_member_id', $teamMemberId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(UpdateTeamMemberResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves a `WageSetting` object for a team member specified
     * by `TeamMember.id`. For more information, see
     * [Troubleshooting the Team API](https://developer.squareup.
     * com/docs/team/troubleshooting#retrievewagesetting).
     *
     * EDD\Vendor\Square recommends using [RetrieveTeamMember]($e/Team/RetrieveTeamMember) or
     * [SearchTeamMembers]($e/Team/SearchTeamMembers)
     * to get this information directly from the `TeamMember.wage_setting` field.
     *
     * @param string $teamMemberId The ID of the team member for which to retrieve the wage setting.
     *
     * @return ApiResponse Response from the API call
     */
    public function retrieveWageSetting(string $teamMemberId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/team-members/{team_member_id}/wage-setting')
            ->auth('global')
            ->parameters(TemplateParam::init('team_member_id', $teamMemberId));

        $_resHandler = $this->responseHandler()->type(RetrieveWageSettingResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Creates or updates a `WageSetting` object. The object is created if a
     * `WageSetting` with the specified `team_member_id` doesn't exist. Otherwise,
     * it fully replaces the `WageSetting` object for the team member.
     * The `WageSetting` is returned on a successful update. For more information, see
     * [Troubleshooting the Team API](https://developer.squareup.com/docs/team/troubleshooting#create-or-
     * update-a-wage-setting).
     *
     * EDD\Vendor\Square recommends using [CreateTeamMember]($e/Team/CreateTeamMember) or
     * [UpdateTeamMember]($e/Team/UpdateTeamMember)
     * to manage the `TeamMember.wage_setting` field directly.
     *
     * @param string $teamMemberId The ID of the team member for which to update the `WageSetting`
     *        object.
     * @param UpdateWageSettingRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function updateWageSetting(string $teamMemberId, UpdateWageSettingRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::PUT, '/v2/team-members/{team_member_id}/wage-setting')
            ->auth('global')
            ->parameters(
                TemplateParam::init('team_member_id', $teamMemberId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(UpdateWageSettingResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }
}
