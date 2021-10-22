<?php
/**
 * Endpoint.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\API\v3;

abstract class Endpoint {

	public static $namespace = 'edd/v3';

	/**
	 * Registers the endpoint(s).
	 *
	 * @since 3.x
	 *
	 * @return void
	 */
	abstract public function register();

}
