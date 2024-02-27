<?php
/**
 * Updates the checkout behavior.
 *
 * @since 3.1.1
 */
namespace EDD\Pro\Checkout;

use EDD\EventManagement\SubscriberInterface;

class GeoIP implements SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'wp_enqueue_scripts'           => 'enqueue',
			'wp_ajax_edd_pro_geoip'        => 'get_address',
			'wp_ajax_nopriv_edd_pro_geoip' => 'get_address',
			'edd_built_order'              => 'add_ip_to_data',
		);
	}

	/**
	 * Enqueues the scripts needed for the geoip request.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function enqueue() {
		if ( ! $this->can_enqueue() ) {
			return;
		}
		wp_enqueue_script( 'edd-pro-checkout', EDD_PLUGIN_URL . 'assets/pro/js/checkout.js', array( 'edd-checkout-global' ), EDD_VERSION, true );
		wp_localize_script(
			'edd-pro-checkout',
			'EDDProCheckout',
			array(
				'ajax'  => edd_get_ajax_url(),
				'api'   => 'https://geo.easydigitaldownloads.com/v3/geolocate/json',
				'taxes' => edd_use_taxes(),
				'debug' => edd_doing_script_debug(),
				'nonce' => wp_create_nonce( 'edd-pro-geoip' ),
			)
		);
	}

	/**
	 * Builds the address from the geolocation endpoint.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function get_address() {

		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'edd-pro-geoip' ) ) {
			wp_send_json_error( 'nonce failed' );
		}

		$address = array(
			'country' => $this->filter_input( 'country_iso' ),
			'city'    => $this->filter_input( 'city' ),
			'state'   => $this->get_state(),
			'ip'      => $this->filter_input( 'ip' ),
		);

		if ( ! is_user_logged_in() ) {
			wp_send_json_success( $address );
		}
		$user_address = edd_get_customer_address();
		foreach ( $address as $key => $field ) {
			if ( ! empty( $user_address[ $key ] ) ) {
				unset( $address[ $key ] );
			}
		}

		wp_send_json_success( $address );
	}

	/**
	 * If the IP address was added to the form data, add it to the order object.
	 *
	 * @since 3.1.1
	 * @param int $order_id
	 * @return void
	 */
	public function add_ip_to_data( $order_id ) {
		$ip = $this->filter_input( 'edd_pro_ip' );
		if ( empty( $ip ) ) {
			return;
		}
		edd_update_order(
			$order_id,
			array(
				'ip' => $ip,
			)
		);
	}

	/**
	 * Helper function to get the region code or name from the API response.
	 *
	 * @since 3.1.1
	 * @return string
	 */
	private function get_state() {
		$country_iso = $this->filter_input( 'country_iso' );
		$states      = edd_get_shop_states( $country_iso );
		$region_code = $this->filter_input( 'region_code' );

		// If the region code is in the states array, it will work for the address selector.
		if ( array_key_exists( $region_code, $states ) ) {
			return sanitize_text_field( $region_code );
		}

		// Japan region codes in EDD are `JP01`, etc.
		if ( 'JP' === $country_iso && array_key_exists( $country_iso . $region_code, $states ) ) {
			return sanitize_text_field( $country_iso . $region_code );
		}

		// For Great Britain, we can try to get the region code from the city.
		if ( 'GB' === $country_iso ) {
			$city = $this->filter_input( 'city' );

			return array_search( $city, edd_get_united_kingdom_states_list(), true );
		}

		// Probably this is a state that's not available from a list so return the full region name.
		return $this->filter_input( 'region_name' );
	}

	/**
	 * Sanitizes an input string.
	 *
	 * @since 3.1.1
	 * @param string $key
	 * @return false|string
	 */
	private function filter_input( $key ) {
		return filter_input( INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS );
	}

	/**
	 * Determines if the scripts can be enqueued.
	 *
	 * @since 3.2.8
	 * @return bool
	 */
	private function can_enqueue() {
		if ( ! edd_is_checkout() || EDD()->cart->is_empty() || edd_is_inactive_pro() ) {
			return false;
		}
		$option = edd_get_option( 'geolocation', 'enabled' );
		if ( 'disabled' === $option || ( 'logged_out' === $option && is_user_logged_in() ) ) {
			return false;
		}

		return true;
	}
}
