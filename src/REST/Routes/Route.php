<?php
/**
 * Abstract REST Route
 *
 * @package     EDD\REST\Routes
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.2
 */

namespace EDD\REST\Routes;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Route class
 *
 * Abstract class for REST routes.
 *
 * @since 3.6.2
 */
abstract class Route {
	const NAMESPACE = 'edd';

	/**
	 * Version number.
	 *
	 * @since 3.6.2
	 * @var string
	 */
	public static $version = 'v3';

	/**
	 * Register the routes.
	 *
	 * @since 3.6.2
	 * @return void
	 */
	abstract public function register();
}
