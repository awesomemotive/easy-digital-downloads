<?php

namespace EDD\Sessions;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Utils\Exception;

/**
 * Cart Class
 *
 * @since 3.3.0
 */
class Cart {

	/**
	 * The cart data.
	 *
	 * @since 3.3.0
	 * @var \EDD_Cart
	 */
	private $cart;

	/**
	 * Cart constructor.
	 *
	 * @param \EDD_Cart $cart The cart object.
	 * @throws Exception If the cart object is invalid.
	 */
	public function __construct( $cart ) {
		if ( ! $cart instanceof \EDD_Cart ) {
			throw new Exception( 'Invalid cart object.' );
		}
		$this->cart = $cart;
		add_action( 'edd_post_remove_from_cart', array( $this, 'maybe_clear_session' ) );
	}

	/**
	 * Gets the cart contents.
	 *
	 * @since 3.3.0
	 */
	public function get_contents() {
		$this->cart->contents = EDD()->session->get( 'edd_cart' );

		do_action( 'edd_cart_contents_loaded_from_session', $this->cart );
	}

	/**
	 * Gets the cart discounts.
	 *
	 * @since 3.3.0
	 */
	public function get_discounts() {
		$this->cart->discounts = EDD()->session->get( 'cart_discounts' );

		do_action( 'edd_cart_discounts_loaded_from_session', $this->cart );
	}

	/**
	 * Empties the cart from the session.
	 *
	 * @return void
	 */
	public function empty_cart() {
		// Remove cart contents.
		EDD()->session->set( 'edd_cart', null );

		// Remove all cart fees.
		EDD()->session->set( 'edd_cart_fees', null );

		// Remove any resuming payments.
		EDD()->session->set( 'edd_resume_payment', null );

		// Remove any cart cookies.
		EDD()->session->set_cart_cookie( false );

		// Remove any cart discounts.
		$this->remove_all_discounts();
		$this->cart->contents = array();
	}

	/**
	 * Removes all discounts from the cart.
	 *
	 * @return void
	 */
	public function remove_all_discounts() {
		EDD()->session->set( 'cart_discounts', null );
		$this->cart->discounts = array();

		do_action( 'edd_cart_discounts_removed' );
	}

	/**
	 * Maybe clear the session.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public function maybe_clear_session() {
		if ( ! $this->cart->is_empty() ) {
			return;
		}
		if ( $this->cart->has_discounts() ) {
			return;
		}

		EDD()->session->set_cart_cookie( false );
	}
}
