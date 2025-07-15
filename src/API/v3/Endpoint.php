<?php
/**
 * Endpoint for API v3
 *
 * @package   EDD\API\v3
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.11.4
 */

namespace EDD\API\v3;

/**
 * Abstract class for API endpoints.
 *
 * @since 2.11.4
 */
abstract class Endpoint {

	/**
	 * The namespace for the endpoint.
	 *
	 * @var string
	 */
	public static $namespace = 'edd/v3';

	/**
	 * Registers the endpoint(s).
	 *
	 * @since 2.11.4
	 *
	 * @return void
	 */
	abstract public function register();
}
