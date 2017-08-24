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
		register_meta(
			'post',
			'_edd_download_earnings',
			array(
				'sanitize_callback' => 'edd_sanitize_amount',
				'type'              => 'float',
				'description'       => __( 'The total earnings for the specified product', 'easy-digital-downloads' ),
			)
		);

		// Pre-WordPress 4.6 compatibility
		if ( ! has_filter( 'sanitize_post_meta__edd_download_earnings' ) ) {
			add_filter( 'sanitize_post_meta__edd_download_earnings', 'edd_sanitize_amount', 10, 4 );
		}

		register_meta(
			'post',
			'_edd_download_sales',
			array(
				'sanitize_callback' => array( $this, 'intval_wrapper' ),
				'type'              => 'float',
				'description'       => __( 'The number of sales for the specified product.', 'easy-digital-downloads' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__edd_download_sales' ) ) {
			add_filter( 'sanitize_post_meta__edd_download_sales', array( $this, 'intval_wrapper' ), 10, 4 );
		}

		register_meta(
			'post',
			'edd_price',
			array(
				'sanitize_callback' => array( $this, 'sanitize_price' ),
				'type'              => 'float',
				'description'       => __( 'The price of the product.', 'easy-digital-downloads' ),
				'show_in_rest'      => true,
			)
		);

		if ( ! has_filter( 'sanitize_post_meta_edd_price' ) ) {
			add_filter( 'sanitize_post_meta_edd_price', array( $this, 'sanitize_price' ), 10, 4 );
		}

		register_meta(
			'post',
			'edd_variable_prices',
			array(
				'sanitize_callback' => array( $this, 'sanitize_variable_prices'),
				'type'              => 'array',
				'description'       => __( 'An array of variable prices for the product.', 'easy-digital-downloads' ),
				'show_in_rest'      => true,
			)
		);

		if ( ! has_filter( 'sanitize_post_meta_edd_variable_prices' ) ) {
			add_filter( 'sanitize_post_meta_edd_variable_prices', array( $this, 'sanitize_variable_prices'), 10, 4 );
		}

		register_meta(
			'post',
			'edd_download_files',
			array(
				'sanitize_callback' => array( $this, 'sanitize_files' ),
				'type'              => 'array',
				'description'       => __( 'The files associated with the product, available for download.', 'easy-digital-downloads' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta_edd_download_files' ) ) {
			add_filter( 'sanitize_post_meta_edd_download_files', array( $this, 'sanitize_files' ), 10, 4 );
		}

		register_meta(
			'post',
			'_edd_bundled_products',
			array(
				'sanitize_callback' => array( $this, 'sanitize_array' ),
				'type'              => 'array',
				'description'       => __( 'An array of product IDs to associate with a bundle.', 'easy-digital-downloads' ),
				'show_in_rest'      => true,
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__edd_bundled_products' ) ) {
			add_filter( 'sanitize_post_meta__edd_bundled_products', array( $this, 'sanitize_array' ), 10, 4 );
		}

		register_meta(
			'post',
			'_edd_button_behavior',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'string',
				'description'       => __( 'Defines how this products `Purchase` button should behave, either add to cart or buy now.', 'easy-digital-downloads' ),
				'show_in_rest'      => true,
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__edd_button_behavior' ) ) {
			add_filter( 'sanitize_post_meta__edd_button_behavior', 'sanitize_text_field', 10, 4 );
		}

		register_meta(
			'post',
			'_edd_default_price_id',
			array(
				'sanitize_callback' => array( $this, 'intval_wrapper' ),
				'type'              => 'int',
				'description'       => __( 'When variable pricing is enabled, this value defines which option should be chosen by default.', 'easy-digital-downloads' ),
				'show_in_rest'      => true,
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__edd_default_price_id' ) ) {
			add_filter( 'sanitize_post_meta__edd_default_price_id', array( $this, 'intval_wrapper' ), 10, 4 );
		}

	}

	/**
	 * Register the meta for the edd_payment post type.
	 *
	 * @since  2.5
	 * @return void
	 */
	public function register_payment_meta() {

		register_meta(
			'post',
			'_edd_payment_user_email',
			array(
				'sanitize_callback' => 'sanitize_email',
				'type'              => 'string',
				'description'       => __( 'The email address associated with the purchase.', 'easy-digital-downloads' ),
			)
		);

		// Pre-WordPress 4.6 compatibility
		if ( ! has_filter( 'sanitize_post_meta__edd_payment_user_email' ) ) {
			add_filter( 'sanitize_post_meta__edd_payment_user_email', 'sanitize_email', 10, 4 );
		}

		register_meta(
			'post',
			'_edd_payment_customer_id',
			array(
				'sanitize_callback' => array( $this, 'intval_wrapper' ),
				'type'              => 'int',
				'description'       => __( 'The Customer ID associated with the payment.', 'easy-digital-downloads' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__edd_payment_customer_id' ) ) {
			add_filter( 'sanitize_post_meta__edd_payment_customer_id', array( $this, 'intval_wrapper' ), 10, 4 );
		}

		register_meta(
			'post',
			'_edd_payment_user_id',
			array(
				'sanitize_callback' => array( $this, 'intval_wrapper' ),
				'type'              => 'int',
				'description'       => __( 'The User ID associated with the payment.', 'easy-digital-downloads' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__edd_payment_user_id' ) ) {
			add_filter( 'sanitize_post_meta__edd_payment_user_id', array( $this, 'intval_wrapper' ), 10, 4 );
		}

		register_meta(
			'post',
			'_edd_payment_user_ip',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'string',
				'description'       => __( 'The IP address the payment was made from.', 'easy-digital-downloads' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__edd_payment_user_ip' ) ) {
			add_filter( 'sanitize_post_meta__edd_payment_user_ip', 'sanitize_text_field', 10, 4 );
		}

		register_meta(
			'post',
			'_edd_payment_purchase_key',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'string',
				'description'       => __( 'The unique purchase key for this payment.', 'easy-digital-downloads' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__edd_payment_purchase_key' ) ) {
			add_filter( 'sanitize_post_meta__edd_payment_purchase_key', 'sanitize_text_field', 10, 4 );
		}

		register_meta(
			'post',
			'_edd_payment_total',
			array(
				'sanitize_callback' => 'edd_sanitize_amount',
				'type'              => 'float',
				'description'       => __( 'The purchase total for this payment.', 'easy-digital-downloads' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__edd_payment_total' ) ) {
			add_filter( 'sanitize_post_meta__edd_payment_total', 'edd_sanitize_amount', 10, 4 );
		}

		register_meta(
			'post',
			'_edd_payment_mode',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'string',
				'description'       => __( 'Identifies if the purchase was made in Test or Live mode.', 'easy-digital-downloads' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__edd_payment_mode' ) ) {
			add_filter( 'sanitize_post_meta__edd_payment_mode', 'sanitize_text_field', 10, 4 );
		}

		register_meta(
			'post',
			'_edd_payment_gateway',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'string',
				'description'       => __( 'The registered gateway that was used to process this payment.', 'easy-digital-downloads' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__edd_payment_gateway' ) ) {
			add_filter( 'sanitize_post_meta__edd_payment_gateway', 'sanitize_text_field', 10, 4 );
		}

		register_meta(
			'post',
			'_edd_payment_meta',
			array(
				'sanitize_callback' => array( $this, 'sanitize_array' ),
				'type'              => 'array',
				'description'       => __( 'Array of payment meta that contains cart details, downloads, amounts, taxes, discounts, and subtotals, etc.', 'easy-digital-downloads' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__edd_payment_meta' ) ) {
			add_filter( 'sanitize_post_meta__edd_payment_meta', array( $this, 'sanitize_array' ), 10, 4 );
		}

		register_meta(
			'post',
			'_edd_payment_tax',
			array(
				'sanitize_callback' => 'edd_sanitize_amount',
				'type'              => 'float',
				'description'       => __( 'The total amount of tax paid for this payment.', 'easy-digital-downloads' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__edd_payment_tax' ) ) {
			add_filter( 'sanitize_post_meta__edd_payment_tax', 'edd_sanitize_amount', 10, 4 );
		}

		register_meta(
			'post',
			'_edd_completed_date',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'type'              => 'string',
				'description'       => __( 'The date this payment was changed to the `completed` status.', 'easy-digital-downloads' ),
			)
		);

		if ( ! has_filter( 'sanitize_post_meta__edd_completed_date' ) ) {
			add_filter( 'sanitize_post_meta__edd_completed_date', 'sanitize_text_field', 10, 4 );
		}


	}

	/**
	 * Wrapper for intval
	 * Setting intval as the callback was stating an improper number of arguments, this avoids that.
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
	 * Perform some sanitization on the amount field including not allowing negative values by default
	 *
	 * @since  2.6.5
	 * @param  float $price The price to sanitize
	 * @return float        A sanitized price
	 */
	public function sanitize_price( $price ) {

		$allow_negative_prices = apply_filters( 'edd_allow_negative_prices', false );

		if ( ! $allow_negative_prices && $price < 0 ) {
			$price = 0;
		}

		return edd_sanitize_amount( $price );
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
	public function sanitize_variable_prices( $prices = array() ) {
		$prices = $this->remove_blank_rows( $prices );

		if ( ! is_array( $prices ) ) {
			return array();
		}

		foreach ( $prices as $id => $price ) {

			if ( empty( $price['amount'] ) && empty( $price['name'] ) ) {

				unset( $prices[ $id ] );
				continue;

			} elseif ( empty( $price['amount'] ) ) {

				$price['amount'] = 0;

			}

			$prices[ $id ]['amount'] = $this->sanitize_price( $price['amount'] );

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
		$files = $this->remove_blank_rows( $files );

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
		return $files;
	}

	/**
	 * Don't save blank rows.
	 *
	 * When saving, check the price and file table for blank rows.
	 * If the name of the price or file is empty, that row should not
	 * be saved.
	 *
	 * @since 2.5
	 * @param array $new Array of all the meta values
	 * @return array $new New meta value with empty keys removed
	 */
	private function remove_blank_rows( $new ) {

		if ( is_array( $new ) ) {
			foreach ( $new as $key => $value ) {
				if ( empty( $value['name'] ) && empty( $value['amount'] ) && empty( $value['file'] ) ) {
					unset( $new[ $key ] );
				}
			}
		}

		return $new;
	}

}
EDD_Register_Meta::instance();
