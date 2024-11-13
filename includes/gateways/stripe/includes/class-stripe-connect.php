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

	/**
	 * Gets the Stripe connect account country.
	 *
	 * @since 3.3.5
	 * @return string
	 */
	public function get_connect_country() {
		$edd_country = edd_get_option( 'base_country', '' );

		return edd_get_option(
			'stripe_connect_account_country',
			$edd_country
		);
	}
}
