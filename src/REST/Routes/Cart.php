<?php
/**
 * Cart REST Routes
 *
 * Registers REST API routes for cart functionality.
 *
 * @package     EDD\REST\Routes
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.2
 */

namespace EDD\REST\Routes;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\REST\Security;
use EDD\REST\Controllers\Cart as Controller;

/**
 * Cart class
 *
 * Handles REST API route registration for cart operations.
 *
 * @since 3.6.2
 */
class Cart extends Route {

	/**
	 * REST API base.
	 *
	 * @since 3.6.2
	 * @var string
	 */
	const BASE = 'cart';

	/**
	 * Cart controller instance.
	 *
	 * @since 3.6.2
	 * @var Controller
	 */
	private $controller;

	/**
	 * Security instance.
	 *
	 * @since 3.6.2
	 * @var Security
	 */
	private $security;

	/**
	 * Constructor.
	 *
	 * @since 3.6.2
	 */
	public function __construct() {
		$this->security   = new Security();
		$this->controller = new Controller( $this->security );
	}

	/**
	 * Register routes.
	 *
	 * @since 3.6.2
	 * @return void
	 */
	public function register() {
		// Add item to cart.
		register_rest_route(
			self::NAMESPACE,
			'/' . self::$version . '/' . self::BASE . '/add',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this->controller, 'add_item' ),
				'permission_callback' => array( $this->security, 'validate_token' ),
				'args'                => array(
					'download_id' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
						'validate_callback' => array( $this->controller, 'validate_download_id' ),
					),
					'price_id'    => array(
						'required'          => false,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'quantity'    => array(
						'required'          => false,
						'type'              => 'integer',
						'default'           => 1,
						'sanitize_callback' => 'absint',
					),
					'options'     => array(
						'required'          => false,
						'type'              => 'object',
						'default'           => array(),
						'sanitize_callback' => array( $this->controller, 'sanitize_options' ),
					),
				),
			)
		);

		// Remove item from cart.
		register_rest_route(
			self::NAMESPACE,
			'/' . self::$version . '/' . self::BASE . '/remove',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this->controller, 'remove_item' ),
				'permission_callback' => array( $this->security, 'validate_token' ),
				'args'                => array(
					'cart_key' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		// Update quantity.
		register_rest_route(
			self::NAMESPACE,
			'/' . self::$version . '/' . self::BASE . '/update-quantity',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this->controller, 'update_quantity' ),
				'permission_callback' => array( $this->security, 'validate_token' ),
				'args'                => array(
					'cart_key' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
					'quantity' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
						'validate_callback' => array( $this->controller, 'validate_quantity' ),
					),
				),
			)
		);

		// Get cart contents.
		register_rest_route(
			self::NAMESPACE,
			'/' . self::$version . '/' . self::BASE . '/contents',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this->controller, 'get_contents' ),
				'permission_callback' => array( $this->security, 'validate_token' ),
			)
		);

		// Get fresh token.
		register_rest_route(
			self::NAMESPACE,
			'/' . self::$version . '/' . self::BASE . '/token',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this->controller, 'get_token' ),
				'permission_callback' => '__return_true',
			)
		);
	}
}
