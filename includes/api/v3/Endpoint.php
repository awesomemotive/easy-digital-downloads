<?php
/**
 * Endpoint for API v3
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 * @since     2.11.4
 */

namespace EDD\API\v3;

abstract class Endpoint {

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
