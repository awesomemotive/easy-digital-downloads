<?php
/**
 * Cart REST Controller
 *
 * Handles REST API endpoints for cart operations.
 *
 * @package     EDD\REST\Controllers
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.2
 */

namespace EDD\REST\Controllers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Cart\Data;
use EDD\REST\Security;

/**
 * Cart controller class.
 *
 * Handles cart operations via REST API.
 *
 * @since 3.6.2
 */
class Cart {

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
	 * @param Security $security Security instance.
	 */
	public function __construct( Security $security ) {
		$this->security = $security;
	}

	/**
	 * Add item to cart.
	 *
	 * @since 3.6.2
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function add_item( $request ) {
		$download_id = $request->get_param( 'download_id' );
		$price_id    = $request->get_param( 'price_id' );
		$quantity    = $request->get_param( 'quantity' );

		// Build options array.
		$options = array();
		if ( null !== $price_id ) {
			$options['price_id'] = $price_id;
		}

		// Add quantity if enabled.
		if ( edd_item_quantities_enabled() && $quantity > 1 ) {
			$options['quantity'] = $quantity;
		}

		// Merge any additional options from the request.
		$additional_options = $request->get_param( 'options' );
		if ( ! empty( $additional_options ) && is_array( $additional_options ) ) {
			// Sanitize to ensure protected keys are filtered out.
			$additional_options = $this->sanitize_options( $additional_options );
			$options            = array_merge( $options, $additional_options );
		}

		// Add to cart.
		$item_key = edd_add_to_cart( $download_id, $options );

		if ( false === $item_key ) {
			return new \WP_Error(
				'add_failed',
				__( 'Failed to add item to cart.', 'easy-digital-downloads' ),
				array( 'status' => 400 )
			);
		}

		/**
		 * Fires after item is added to cart via REST API.
		 *
		 * @since 3.6.2
		 * @param int   $download_id Download ID.
		 * @param array $options     Cart options.
		 * @param int   $item_key    Cart item key.
		 */
		do_action( 'edd_rest_cart_item_added', $download_id, $options, $item_key );

		// Get updated cart data.
		$cart_data = Data::get_cart_data();

		$timestamp = time();
		return new \WP_REST_Response(
			array(
				'success'       => true,
				/* translators: %s is the product name */
				'addedToCart'   => sprintf( __( '%s added to cart.', 'easy-digital-downloads' ), edd_get_download_name( $download_id, $price_id ) ),
				'cart'          => $cart_data,
				'item_position' => $item_key,
				'token'         => $this->security->generate_token( $timestamp ),
				'timestamp'     => $timestamp,
			),
			200
		);
	}

	/**
	 * Remove item from cart.
	 *
	 * @since 3.6.2
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function remove_item( $request ) {
		$cart_key = $request->get_param( 'cart_key' );

		// Get cart item before removal for action hook.
		$cart_contents = edd_get_cart_contents();

		if ( ! isset( $cart_contents[ $cart_key ] ) ) {
			return new \WP_Error(
				'item_not_found',
				__( 'Cart item not found.', 'easy-digital-downloads' ),
				array( 'status' => 404 )
			);
		}

		// Store the download ID and name before removal.
		$download_id   = $cart_contents[ $cart_key ]['id'];
		$download_name = edd_get_cart_item_name( $cart_contents[ $cart_key ] );

		// Remove from cart.
		edd_remove_from_cart( $cart_key );

		/**
		 * Fires after item is removed from cart via REST API.
		 *
		 * @since 3.6.2
		 * @param int   $cart_key  Cart item key.
		 * @param array $cart_item Cart item data.
		 */
		do_action( 'edd_rest_cart_item_removed', $cart_key, $cart_contents[ $cart_key ] );

		// Get updated cart data.
		$cart_data = Data::get_cart_data();

		$timestamp = time();
		return new \WP_REST_Response(
			array(
				'success'         => true,
				/* translators: %s is the product name */
				'removedFromCart' => sprintf( __( '%s removed from cart.', 'easy-digital-downloads' ), $download_name ),
				'cart'            => $cart_data,
				'download_id'     => $download_id,
				'token'           => $this->security->generate_token( $timestamp ),
				'timestamp'       => $timestamp,
			),
			200
		);
	}

	/**
	 * Update item quantity.
	 *
	 * @since 3.6.2
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_quantity( $request ) {
		if ( ! edd_item_quantities_enabled() ) {
			return new \WP_Error(
				'quantities_disabled',
				__( 'Item quantities are not enabled.', 'easy-digital-downloads' ),
				array( 'status' => 400 )
			);
		}

		$cart_key = $request->get_param( 'cart_key' );
		$quantity = $request->get_param( 'quantity' );

		// Get current cart item.
		$cart_contents = edd_get_cart_contents();
		if ( ! isset( $cart_contents[ $cart_key ] ) ) {
			return new \WP_Error(
				'item_not_found',
				__( 'Cart item not found.', 'easy-digital-downloads' ),
				array( 'status' => 404 )
			);
		}

		$old_quantity = isset( $cart_contents[ $cart_key ]['quantity'] ) ? $cart_contents[ $cart_key ]['quantity'] : 1;

		// If quantity is 0, remove the item.
		if ( 0 === $quantity ) {
			return $this->remove_item( $request );
		}

		// Update quantity.
		edd_set_cart_item_quantity( $cart_contents[ $cart_key ]['id'], $quantity, $cart_contents[ $cart_key ]['options'] );

		/**
		 * Fires after item quantity is updated via REST API.
		 *
		 * @since 3.6.2
		 * @param int $cart_key     Cart item key.
		 * @param int $quantity     New quantity.
		 * @param int $old_quantity Old quantity.
		 */
		do_action( 'edd_rest_cart_quantity_updated', $cart_key, $quantity, $old_quantity );

		// Get updated cart data.
		$cart_data = Data::get_cart_data();

		$timestamp = time();
		return new \WP_REST_Response(
			array(
				'success'   => true,
				'message'   => __( 'Quantity updated.', 'easy-digital-downloads' ),
				'cart'      => $cart_data,
				'token'     => $this->security->generate_token( $timestamp ),
				'timestamp' => $timestamp,
			),
			200
		);
	}

	/**
	 * Get cart contents.
	 *
	 * @since 3.6.2
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_contents( $request ) {
		$cart_data = Data::get_cart_data();

		$timestamp = time();
		return new \WP_REST_Response(
			array_merge(
				$cart_data,
				array(
					'token'     => $this->security->generate_token( $timestamp ),
					'timestamp' => $timestamp,
				)
			),
			200
		);
	}

	/**
	 * Get fresh token.
	 *
	 * @since 3.6.2
	 * @param \WP_REST_Request $request Request object.
	 * @return \WP_REST_Response
	 */
	public function get_token( $request ) {
		return new \WP_REST_Response(
			array(
				'token' => $this->security->generate_token(),
			),
			200
		);
	}

	/**
	 * Validate download ID.
	 *
	 * @since 3.6.2
	 * @param int              $value   Download ID.
	 * @param \WP_REST_Request $request Request object.
	 * @param string           $param   Parameter name.
	 * @return bool|\WP_Error
	 */
	public function validate_download_id( $value, $request, $param ) {
		$download = edd_get_download( $value );

		if ( ! $download || 'download' !== $download->post_type ) {
			return new \WP_Error(
				'invalid_download',
				__( 'Invalid download ID.', 'easy-digital-downloads' ),
				array( 'status' => 400 )
			);
		}

		// Check if download is purchasable.
		if ( ! $download->can_purchase() ) {
			return new \WP_Error(
				'download_not_available',
				__( 'This download is not available for purchase.', 'easy-digital-downloads' ),
				array( 'status' => 400 )
			);
		}

		return true;
	}

	/**
	 * Validate quantity.
	 *
	 * @since 3.6.2
	 * @param int              $value   Quantity.
	 * @param \WP_REST_Request $request Request object.
	 * @param string           $param   Parameter name.
	 * @return bool|\WP_Error
	 */
	public function validate_quantity( $value, $request, $param ) {
		if ( $value < 0 || ! is_numeric( $value ) ) {
			return new \WP_Error(
				'invalid_quantity',
				__( 'Quantity must be a positive number.', 'easy-digital-downloads' ),
				array( 'status' => 400 )
			);
		}

		return true;
	}

	/**
	 * Sanitize cart item options.
	 *
	 * Filters out protected keys that should not be set via the REST API options parameter.
	 *
	 * @since 3.6.2
	 * @param mixed $options Options from the request.
	 * @return array Sanitized options array.
	 */
	public function sanitize_options( $options ) {
		if ( ! is_array( $options ) ) {
			return array();
		}

		// Keys that are managed by other parameters or internal logic.
		$protected_keys = array(
			'price_id',
			'quantity',
			'id',
			'hash',
		);

		/**
		 * Filters the protected option keys that cannot be set via the REST API.
		 *
		 * @since 3.6.2
		 * @param array $protected_keys Keys that are protected from being set.
		 */
		$protected_keys = apply_filters( 'edd_rest_cart_protected_option_keys', $protected_keys );

		// Remove protected keys.
		$options = array_diff_key( $options, array_flip( $protected_keys ) );

		// Sanitize remaining values based on type.
		$sanitized = array();
		foreach ( $options as $key => $value ) {
			$key = sanitize_key( $key );
			if ( is_bool( $value ) ) {
				$sanitized[ $key ] = $value;
			} elseif ( is_int( $value ) ) {
				$sanitized[ $key ] = intval( $value );
			} elseif ( is_string( $value ) ) {
				$sanitized[ $key ] = sanitize_text_field( $value );
			}
			// Skip arrays/objects for simplicity - only allow scalar values.
		}

		return $sanitized;
	}
}
