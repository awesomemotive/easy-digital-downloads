<?php
/**
 * Log Pruning REST Routes
 *
 * Registers REST API routes for log pruning functionality.
 *
 * @package     EDD\REST\Routes
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.4
 */

namespace EDD\REST\Routes;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\REST\Controllers\LogPruning as Controller;

/**
 * LogPruning class
 *
 * Handles REST API route registration for log pruning operations.
 *
 * @since 3.6.4
 */
class LogPruning extends Route {

	/**
	 * REST API base.
	 *
	 * @since 3.6.4
	 * @var string
	 */
	const BASE = 'logs/prune';

	/**
	 * LogPruning controller instance.
	 *
	 * @since 3.6.4
	 * @var Controller
	 */
	private $controller;

	/**
	 * Constructor.
	 *
	 * @since 3.6.4
	 */
	public function __construct() {
		$this->controller = new Controller();
	}

	/**
	 * Register routes.
	 *
	 * @since 3.6.4
	 * @return void
	 */
	public function register() {
		// Prune logs endpoint.
		register_rest_route(
			self::NAMESPACE,
			'/' . self::$version . '/' . self::BASE,
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this->controller, 'prune' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'log_type' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_key',
						'description'       => __( 'The log type to prune.', 'easy-digital-downloads' ),
					),
					'days' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
						'validate_callback' => function ( $value ) {
							$value = absint( $value );
							if ( $value < 1 || $value > 3650 ) {
								return new \WP_Error(
									'rest_invalid_param',
									__( 'Days must be between 1 and 3650.', 'easy-digital-downloads' ),
									array( 'status' => 400 )
								);
							}
							return true;
						},
						'description'       => __( 'Number of days to keep logs.', 'easy-digital-downloads' ),
						'minimum'           => 1,
					),
				),
			)
		);
	}

	/**
	 * Check permission for log pruning.
	 *
	 * Uses standard WordPress REST authentication (cookies + nonce).
	 *
	 * @since 3.6.4
	 * @param \WP_REST_Request $request Request object.
	 * @return bool|\WP_Error
	 */
	public function check_permission( $request ) {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'You do not have permission to perform this action.', 'easy-digital-downloads' ),
				array( 'status' => 403 )
			);
		}

		return true;
	}
}
