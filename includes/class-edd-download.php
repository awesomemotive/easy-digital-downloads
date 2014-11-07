<?php
/**
 * Download Object
 *
 * @package     EDD
 * @subpackage  Classes/Download
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.2
*/

/**
 * EDD_Download Class
 *
 * @since 2.2
 */
class EDD_Download {

	public $price;

	public $prices;

	public $files;

	public $file_download_limit;

	public $type;

	public $bundled_downloads;

	public $sales;

	public $earnings;

	public $notes;

	public $sku;

	public $button_behavior;

	/**
	 * Get things going
	 *
	 * @since 2.2
	 */
	public function __construct( $_id = false, $_args = array() ) {

		if( empty( $_id ) ) {

			$defaults = array(
				'post_type'   => 'download',
				'post_status' => 'draft',
				'post_title'  => __( 'New Download Product', 'edd' )
			);

			$args = wp_parse_args( $_args, $defaults );

			$_id  = wp_insert_post( $args, true );

		}

		$download = WP_Post::get_instance( $_id );

		foreach ( $download as $key => $value ) {

			$this->$key = $value;

		}

		foreach ( get_object_vars( $this ) as $key => $value ) {

			if( method_exists( $this, 'get_' . $key ) ) {

				$value = call_user_func( array( $this, 'get_' . $key ) );

			}

			$this->$key = $value;

		}

	}

	public function get_price() {

		$price = get_post_meta( $this->ID, 'edd_price', true );

		if ( $price ) {

			$price = edd_sanitize_amount( $price );

		} else {

			$price = 0;

		}

		return apply_filters( 'edd_get_download_price', $price, $this->ID );
	}

	public function get_prices() {

		$prices = get_post_meta( $this->ID, 'edd_variable_prices', true );

		return apply_filters( 'edd_get_variable_prices', $prices, $this->ID );

	}

	public function is_single_price_mode() {

		$ret = get_post_meta( $this->ID, '_edd_price_options_mode', true );

		return (bool) apply_filters( 'edd_single_price_option_mode', $ret, $this->ID );

	}

	public function has_variable_prices() {

		$ret = get_post_meta( $this->ID, '_variable_pricing', true );

		return (bool) apply_filters( 'edd_has_variable_prices', $ret, $this->ID );

	}

	public function get_files( $variable_price_id = null ) {

		$files = array();

		// Bundled products are not allowed to have files
		if( edd_is_bundled_product( $this->ID ) ) {
			return $files;
		}

		$download_files = get_post_meta( $this->ID, 'edd_download_files', true );

		if ( $download_files ) {


			if ( ! is_null( $variable_price_id ) && $this->has_variable_prices() ) {

				foreach ( $download_files as $key => $file_info ) {

					if ( isset( $file_info['condition'] ) ) {

						if ( $file_info['condition'] == $variable_price_id || 'all' === $file_info['condition'] ) {

							$files[ $key ] = $file_info;

						}

					}

				}

			} else {

				$files = $download_files;

			}

		}

		return apply_filters( 'edd_download_files', $files, $this->ID, $variable_price_id );

	}

	public function get_file_download_limit() {

		$ret    = 0;
		$limit  = get_post_meta( $this->ID, '_edd_download_limit', true );
		$global = edd_get_option( 'file_download_limit', 0 );

		if ( ! empty( $limit ) || ( is_numeric( $limit ) && (int)$limit == 0 ) ) {

			// Download specific limit
			$ret = absint( $limit );

		} else {

			// Global limit
			$ret = strlen( $limit ) == 0  || $global ? $global : 0;

		}

		return absint( apply_filters( 'edd_file_download_limit', $ret, $this->ID ) );

	}

	public function get_file_price_condition( $file_key = 0 ) {
	
		$files    = edd_get_download_files( $this->ID );
		$condition = isset( $files[ $file_key ]['condition']) ? $files[ $file_key ]['condition'] : 'all';

		return apply_filters( 'edd_get_file_price_condition', $condition, $this->ID, $files );
	
	}

	public function get_type() {

	}

	public function get_bundled_downloads() {

	}

	public function get_notes() {
	
		$notes = get_post_meta( $this->ID, 'edd_product_notes', true );		

		return (string) apply_filters( 'edd_product_notes', $notes, $this->ID );
	
	}

	public function get_sku() {

		$sku = get_post_meta( $this->ID, 'edd_sku', true );

		if ( empty( $sku ) ) {
			$sku = '-';
		}

		return apply_filters( 'edd_get_download_sku', $sku, $this->ID );

	}

	public function get_button_behavior() {

		$behavior = get_post_meta( $this->ID, '_edd_button_behavior', true );

		if( empty( $behavior ) ) {

			$behavior = 'add_to_cart';

		}

		return apply_filters( 'edd_get_download_button_behavior', $behavior, $this->ID );

	}

	public function get_sales() {
	
		if ( '' == get_post_meta( $this->ID, '_edd_download_sales', true ) ) {
			add_post_meta( $this->ID, '_edd_download_sales', 0 );
		} // End if

		$sales = get_post_meta( $this->ID, '_edd_download_sales', true );

		if ( $sales < 0 ) {
			// Never let sales be less than zero
			$sales = 0;
		}

		return $sales;

	}

	public function increase_sales() {

		$sales = edd_get_download_sales_stats( $this->ID );
		$sales = $sales + 1;
		
		if ( update_post_meta( $this->ID, '_edd_download_sales', $sales ) ) {
			return $sales;
		}

		return false;
	}

	public function decrease_sales() {
	
		$sales = edd_get_download_sales_stats( $this->ID );
		if ( $sales > 0 ) // Only decrease if not already zero
			$sales = $sales - 1;

		if ( update_post_meta( $this->ID, '_edd_download_sales', $sales ) ) {
			return $sales;
		}

		return false;
	
	}

	public function get_earnings() {
	
		if ( '' == get_post_meta( $this->ID, '_edd_download_earnings', true ) ) {
			add_post_meta( $this->ID, '_edd_download_earnings', 0 );
		}

		$earnings = get_post_meta( $this->ID, '_edd_download_earnings', true );

		if( $earnings < 0 ) {
			// Never let earnings be less than zero
			$earnings = 0;
		}

		return $earnings;

	}

	public function increase_earnings( $amount = 0 ) {
	
		$earnings = edd_get_download_earnings_stats( $this->ID );
		$earnings = $earnings + (float) $amount;

		if ( update_post_meta( $this->ID, '_edd_download_earnings', $earnings ) ) {
			return $earnings;
		}

		return false;
	
	}

	public function decrease_earnings( $amount ) {

		$earnings = edd_get_download_earnings_stats( $this->ID );

		if ( $earnings > 0 ) // Only decrease if greater than zero
			$earnings = $earnings - (float) $amount;

		if ( update_post_meta( $this->ID, '_edd_download_earnings', $earnings ) ) {
			return $earnings;
		}

		return false;

	}

	public function is_free( $price_id = false ) {

		$is_free = false;
		$variable_pricing = edd_has_variable_prices( $this->ID );

		if ( $variable_pricing && ! is_null( $price_id ) && $price_id !== false ) {
			$price = edd_get_price_option_amount( $this->ID, $price_id );
		} elseif( ! $variable_pricing ) {
			$price = get_post_meta( $this->ID, 'edd_price', true );
		}

		if( isset( $price ) && (float) $price == 0 ) {
			$is_free = true;
		}

		return (bool) apply_filters( 'edd_is_free_download', $is_free, $this->ID, $price_id );

	}

}