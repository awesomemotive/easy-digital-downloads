<?php

namespace EDD\Stripe;

defined( 'ABSPATH' ) || exit;

/**
 * The class to manage the Stripe Connect properties.
 */
class Connect {

	/**
	 * Whether the site is connected to Stripe with Stripe Connect.
	 *
	 * @var bool
	 */
	public $is_connected;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->is_connected = ! empty( $this->get_connect_id() );
	}

	/**
	 * Gets the connect ID.
	 *
	 * @return string|false
	 */
	public function get_connect_id() {
		return edd_get_option( 'stripe_connect_account_id', false );
	}
}
