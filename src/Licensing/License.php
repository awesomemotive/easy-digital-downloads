<?php

namespace EDD\Licensing;

defined( 'ABSPATH' ) || exit;

/**
 * Class to handle and normalize the license data.
 *
 * @since 3.1.1
 */
class License {

	/**
	 * The license status.
	 *
	 * @var string
	 */
	public $license = '';

	/**
	 * The product ID.
	 *
	 * @var int
	 */
	public $item_id;

	/**
	 * The product name.
	 *
	 * @var string
	 */
	public $item_name;

	/**
	 * The license activation limit.
	 *
	 * @var int
	 */
	public $license_limit;

	/**
	 * The number of sites on which this license is active.
	 *
	 * @var int
	 */
	public $site_count = 0;

	/**
	 * The license expiration date.
	 *
	 * @var string
	 */
	public $expires;

	/**
	 * The number of activations left.
	 *
	 * @var int
	 */
	public $activations_left;

	/**
	 * The order ID for the license.
	 *
	 * @var int
	 */
	public $payment_id;

	/**
	 * The product price ID.
	 *
	 * @var false|int
	 */
	public $price_id = false;

	/**
	 * The customer's pass ID.
	 *
	 * @var null|int
	 */
	public $pass_id;

	/**
	 * The error code for a license.
	 *
	 * @var string
	 */
	public $error;

	/**
	 * The license key.
	 *
	 * @var string
	 */
	public $key = '';

	/**
	 * Whether the API request was successful.
	 *
	 * @var bool
	 */
	public $success = false;

	/**
	 * The subscription status.
	 *
	 * @since 3.1.1
	 * @var string
	 */
	public $subscription;

	/**
	 * The subscription ID.
	 *
	 * @since 3.2.0
	 * @var string
	 */
	public $subscription_id;

	/**
	 * The product shortname.
	 *
	 * @var string
	 */
	private $product_shortname;

	/**
	 * The option name for the license data.
	 *
	 * @var string
	 */
	private $option_name;

	/**
	 * The option name for the license data.
	 *
	 * @var string
	 */
	private $custom_key_option;

	/**
	 * Whether the option is for a single site or site meta (multisite).
	 *
	 * @var bool
	 */
	private $single_site = true;

	/**
	 * The class constructor.
	 *
	 * @param string      $product_name The product/item name.
	 * @param null|string $custom_key_option For backwards compatibility with extensions who saved key data as a custom option name.
	 */
	public function __construct( $product_name, $custom_key_option = null ) {
		$this->product_shortname = 'edd_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $product_name ) ) );
		$this->option_name       = "{$this->product_shortname}_license_active";

		if ( 'pro' === $product_name ) {
			$this->option_name = "{$this->product_shortname}_license";
			$this->single_site = false;
		} elseif ( $custom_key_option && $custom_key_option !== $this->option_name ) {
			$this->custom_key_option = $custom_key_option;
		}

		$this->get();
	}

	/**
	 * Saves the license data option.
	 *
	 * @since 3.1.1
	 * @param object $license_data
	 * @return bool
	 */
	public function save( $license_data ) {
		if ( $this->single_site ) {
			$updated = update_option(
				$this->option_name,
				$license_data,
				false
			);
		} else {
			$updated = update_site_option(
				$this->option_name,
				$license_data
			);
		}

		$this->get();
		/**
		 * Fires after a license is saved.
		 *
		 * @since 3.1.4
		 * @param License $license      The license object.
		 * @param object  $license_data The license data.
		 */
		do_action( 'edd/license/saved', $this, $license_data ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

		return $updated;
	}

	/**
	 * Deletes a license key and related license data.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function delete() {
		if ( ! $this->single_site ) {
			delete_site_option( $this->option_name );
			delete_site_option( "{$this->product_shortname}_license_key" );
		} else {
			delete_option( $this->option_name );
			edd_delete_option( "{$this->product_shortname}_license_key" );
			if ( $this->custom_key_option ) {
				edd_delete_option( $this->custom_key_option );
			}
		}

		$this->get();
		/**
		 * Fires after a license is deleted.
		 *
		 * @since 3.1.4
		 * @param string  $product_shortname The product shortname.
		 * @param License $license           The license object.
		 */
		do_action( 'edd/license/deleted', $this->product_shortname, $this ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
	}

	/**
	 * Selectively update just one piece of the license data.
	 *
	 * @since 3.1.1
	 * @param array $data
	 * @return bool
	 */
	public function update( array $data ) {
		$option = $this->single_site ? get_option( $this->option_name, false ) : get_site_option( $this->option_name, false );
		$update = false;
		foreach ( $data as $key => $value ) {
			if ( $value !== $option->$key && in_array( $key, $this->get_editable_keys(), true ) ) {
				$option->$key = $value;
				$update       = true;
			}
		}

		return $update ? $this->save( $option ) : false;
	}

	/**
	 * Gets the license key for the license.
	 *
	 * @return string
	 */
	public function get_license_key() {

		$option_name = "{$this->product_shortname}_license_key";
		$option      = trim(
			$this->single_site ?
			edd_get_option( $option_name, '' ) :
			get_site_option( $option_name, '' )
		);

		if ( ! empty( $option ) || 'edd_pro' === $this->product_shortname ) {
			return $option;
		}

		/**
		 * Allows for backwards compatibility with old license options,
		 * i.e. if the plugins had license key fields previously, the license
		 * handler will automatically pick these up and use those in lieu of the
		 * user having to reactivate their license.
		 */
		return trim( $this->custom_key_option ? edd_get_option( $this->custom_key_option, '' ) : $option );
	}

	/**
	 * Gets the license object mapped to the class defaults.
	 *
	 * @return EDD\Licensing\License
	 */
	public function get() {
		$this->key = $this->get_license_key();
		if ( empty( $this->key ) ) {
			return $this;
		}
		$option = $this->single_site ? get_option( $this->option_name, false ) : get_site_option( $this->option_name, false );
		if ( ! $option ) {
			return $this;
		}

		foreach ( (array) $option as $key => $value ) {
			if ( property_exists( $this, $key ) ) {
				$this->$key = $value;
			}
		}

		if ( ! $this->success && is_null( $this->error ) && 'valid' !== $this->license ) {
			$this->error = $this->license;
		}

		return $this;
	}

	/**
	 * Whether the license is expired.
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	public function is_expired() {
		if ( ! empty( $this->license ) && 'expired' === $this->license ) {
			return true;
		}

		return ( ! empty( $this->error ) && 'expired' === $this->error );
	}

	/**
	 * Only allow certain keys to be modified.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	private function get_editable_keys() {
		return array( 'license', 'error', 'success', 'pass_id', 'subscription', 'subscription_id', 'expires' );
	}
}
