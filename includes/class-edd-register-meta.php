<?php
/**
 *
 * This class is for registering our meta
 *
 * @package     EDD
 * @subpackage  Classes/Register Meta
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.5
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Register_Meta Class
 *
 * @since 2.5
 */
class EDD_Register_Meta {

	private static $instance;

	/**
	 * Setup the meta registration
	 *
	 * @since 2.5
	 */
	private function __construct() {
		$this->hooks();
	}

	/**
	 * Get the one true instance of EDD_Register_Meta.
	 *
	 * @since  2.5
	 * @return $instance
	 */
	static public function instance() {

		if ( !self::$instance ) {
			self::$instance = new EDD_Register_Meta();
		}

		return self::$instance;

	}

	/**
	 * Register the hooks to kick off meta registration.
	 *
	 * @since  2.5
	 * @return void
	 */
	private function hooks() {
		add_action( 'init', array( $this, 'register_download_meta' ) );
		add_action( 'init', array( $this, 'register_payment_meta' ) );
	}

	/**
	 * Register the meta for the download post type.
	 *
	 * @since  2.5
	 * @return void
	 */
	public function register_download_meta() {
		register_meta( 'post', '_edd_download_earnings' , 'edd_sanitize_amount' );
		register_meta( 'post', '_edd_download_sales'    , array( $this, 'intval_wrapper' ) );
		register_meta( 'post', 'edd_price'              , 'edd_sanitize_amount' );
		register_meta( 'post', 'edd_variable_prices'    , array( $this, 'sanitize_variable_prices' ) );
		register_meta( 'post', 'edd_download_files'     , array( $this, 'sanitize_files' ) );
		register_meta( 'post', '_edd_bundled_products'  , array( $this, 'sanitize_array' ) );
		register_meta( 'post', '_edd_button_behavior'   , 'sanitize_text_field' );
		register_meta( 'post', '_edd_default_price_id'  , array( $this, 'intval_wrapper' ) );
	}

	/**
	 * Register the meta for the edd_payment post type.
	 *
	 * @since  2.5
	 * @return void
	 */
	public function register_payment_meta() {
		register_meta( 'post', '_edd_payment_user_email',   'sanitize_email' );
		register_meta( 'post', '_edd_payment_customer_id',  array( $this, 'intval_wrapper' ) );
		register_meta( 'post', '_edd_payment_user_id',      array( $this, 'intval_wrapper' ) );
		register_meta( 'post', '_edd_payment_user_ip',      'sanitize_text_field' );
		register_meta( 'post', '_edd_payment_purchase_key', 'sanitize_text_field' );
		register_meta( 'post', '_edd_payment_total',        'edd_sanitize_amount' );
		register_meta( 'post', '_edd_payment_mode',         'sanitize_text_field' );
		register_meta( 'post', '_edd_payment_gateway',      'sanitize_text_field' );
		register_meta( 'post', '_edd_payment_meta',         array( $this, 'sanitize_array' ) );
		register_meta( 'post', '_edd_payment_tax',          'edd_sanitize_amount' );
		register_meta( 'post', '_edd_completed_date',       'sanitize_text_field' );
	}

	/**
	 * Wrapper for intval
	 * Setting intval as the callback was stating an improper number of arguements, this avoids that.
	 *
	 * @since  2.5
	 * @param  int $value The value to sanitize.
	 * @return int        The value sanitiezed to be an int.
	 */
	public function intval_wrapper( $value ) {
		return intval( $value );
	}

	/**
	 * Sanitize values that come in as arrays
	 *
	 * @since  2.5
	 * @param  array  $value The value passed into the meta.
	 * @return array         The sanitized value.
	 */
	public function sanitize_array( $value = array() ) {

		if ( ! is_array( $value ) ) {

			if ( is_object( $value ) ) {
				$value = (array) $value;
			}

			if ( is_serialized( $value ) ) {
				$value = (array) maybe_unserialize( $value );
			}

		}

		return $value;
	}

	/**
	 * Sanitize the variable prices
	 *
	 * Ensures prices are correctly mapped to an array starting with an index of 0
	 *
	 * @since 2.5
	 * @param array $prices Variable prices
	 * @return array $prices Array of the remapped variable prices
	 */
	function sanitize_variable_prices( $prices = array() ) {
		if ( ! is_array( $prices ) ) {
			return $prices;
		}

		foreach( $prices as $id => $price ) {

			if( empty( $price['amount'] ) && empty( $price['name'] ) ) {

				unset( $prices[ $id ] );
				continue;

			} elseif( empty( $price['amount'] ) ) {

				$price['amount'] = 0;

			}

			$prices[ $id ]['amount'] = edd_sanitize_amount( $price['amount'] );

		}

		return $prices;
	}

	/**
	 * Sanitize the file downloads
	 *
	 * Ensures files are correctly mapped to an array starting with an index of 0
	 *
	 * @since 2.5
	 * @param array $files Array of all the file downloads
	 * @return array $files Array of the remapped file downloads
	 */
	function sanitize_files( $files = array() ) {

		// Clean up filenames to ensure whitespaces are stripped
		foreach( $files as $id => $file ) {

			if( ! empty( $files[ $id ]['file'] ) ) {
				$files[ $id ]['file'] = trim( $file['file'] );
			}

			if( ! empty( $files[ $id ]['name'] ) ) {
				$files[ $id ]['name'] = trim( $file['name'] );
			}
		}

		// Make sure all files are rekeyed starting at 0
		return array_values( $files );
	}

}

EDD_Register_Meta::instance();
