<?php
/**
 * Download Object
 *
 * @package     EDD
 * @subpackage  Classes/Download
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.2
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Download Class
 *
 * @since 2.2
 */
class EDD_Download {

	/**
	 * The download ID
	 *
	 * @since 2.2
	 */
	public $ID = 0;

	/**
	 * The download price
	 *
	 * @since 2.2
	 */
	private $price;

	/**
	 * The download prices, if Variable Prices are enabled
	 *
	 * @since 2.2
	 */
	private $prices;

	/**
	 * The download files
	 *
	 * @since 2.2
	 */
	private $files;

	/**
	 * The download's file download limit
	 *
	 * @since 2.2
	 */
	private $file_download_limit;

	/**
	 * The download type, default or bundle
	 *
	 * @since 2.2
	 */
	private $type;

	/**
	 * The bundled downloads, if this is a bundle type
	 *
	 * @since 2.2
	 */
	private $bundled_downloads;

	/**
	 * The download's sale count
	 *
	 * @since 2.2
	 */
	private $sales;

	/**
	 * The download's total earnings
	 *
	 * @since 2.2
	 */
	private $earnings;

	/**
	 * The download's notes
	 *
	 * @since 2.2
	 */
	private $notes;

	/**
	 * The download sku
	 *
	 * @since 2.2
	 */
	private $sku;

	/**
	 * The download's purchase button behavior
	 *
	 * @since 2.2
	 */
	private $button_behavior;

	/**
	 * Get things going
	 *
	 * @since 2.2
	 */
	public function __construct( $_id = false, $_args = array() ) {

		if( false === $_id ) {

			$defaults = array(
				'post_type'   => 'download',
				'post_status' => 'draft',
				'post_title'  => __( 'New Download Product', 'edd' )
			);

			$args = wp_parse_args( $_args, $defaults );

			$_id  = wp_insert_post( $args, true );

		}

		$download = WP_Post::get_instance( $_id );

		if( ! is_object( $download ) ) {
			return false;
		}

		if( ! is_a( $download, 'WP_Post' ) ) {
			return false;
		}

		if( 'download' !== $download->post_type ) {
			return false;
		}

		foreach ( $download as $key => $value ) {

			$this->$key = $value;

		}

	}

	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since 2.2
	 */
	public function __get( $key ) {

		if( method_exists( $this, 'get_' . $key ) ) {

			return call_user_func( array( $this, 'get_' . $key ) );

		} else {

			return new WP_Error( 'edd-download-invalid-property', sprintf( __( 'Can\'t get property %s', 'edd' ), $key ) );

		}

	}

	/**
	 * Retrieve the ID
	 *
	 * @since 2.2
	 * @return int
	 */
	public function get_ID() {

		return $this->ID;

	}

	/**
	 * Retrieve the price
	 *
	 * @since 2.2
	 * @return float
	 */
	public function get_price() {

		if ( ! isset( $this->price ) ) {

			$this->price = get_post_meta( $this->ID, 'edd_price', true );

			if ( $this->price ) {

				$this->price = edd_sanitize_amount( $this->price );

			} else {

				$this->price = 0;

			}

		}

		return apply_filters( 'edd_get_download_price', $this->price, $this->ID );
	}

	/**
	 * Retrieve the variable prices
	 *
	 * @since 2.2
	 * @return array
	 */
	public function get_prices() {

		if( ! isset( $this->prices ) ) {

			$this->prices = get_post_meta( $this->ID, 'edd_variable_prices', true );

		}

		return apply_filters( 'edd_get_variable_prices', $this->prices, $this->ID );

	}

	/**
	 * Determine if single price mode is enabled or disabled
	 *
	 * @since 2.2
	 * @return bool
	 */
	public function is_single_price_mode() {

		$ret = get_post_meta( $this->ID, '_edd_price_options_mode', true );

		return (bool) apply_filters( 'edd_single_price_option_mode', $ret, $this->ID );

	}

	/**
	 * Determine if the download has variable prices enabled
	 *
	 * @since 2.2
	 * @return bool
	 */
	public function has_variable_prices() {

		$ret = get_post_meta( $this->ID, '_variable_pricing', true );

		return (bool) apply_filters( 'edd_has_variable_prices', $ret, $this->ID );

	}

	/**
	 * Retrieve the file downloads
	 *
	 * @since 2.2
	 * @param integer $variable_price_id
	 * @return array
	 */
	public function get_files( $variable_price_id = null ) {

		if( ! isset( $this->files ) ) {

			$this->files = array();

			// Bundled products are not allowed to have files
			if( $this->is_bundled_download() ) {
				return $this->files;
			}

			$download_files = get_post_meta( $this->ID, 'edd_download_files', true );

			if ( $download_files ) {


				if ( ! is_null( $variable_price_id ) && $this->has_variable_prices() ) {

					foreach ( $download_files as $key => $file_info ) {

						if ( isset( $file_info['condition'] ) ) {

							if ( $file_info['condition'] == $variable_price_id || 'all' === $file_info['condition'] ) {

								$this->files[ $key ] = $file_info;

							}

						}

					}

				} else {

					$this->files = $download_files;

				}

			}

		}

		return apply_filters( 'edd_download_files', $this->files, $this->ID, $variable_price_id );

	}

	/**
	 * Retrieve the file download limit
	 *
	 * @since 2.2
	 * @return int
	 */
	public function get_file_download_limit() {

		if( ! isset( $this->file_download_limit ) ) {

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

			$this->file_download_limit = $ret;

		}

		return absint( apply_filters( 'edd_file_download_limit', $this->file_download_limit, $this->ID ) );

	}

	/**
	 * Retrieve the price option that has access to the specified file
	 *
	 * @since 2.2
	 * @return int|string
	 */
	public function get_file_price_condition( $file_key = 0 ) {

		$files    = edd_get_download_files( $this->ID );
		$condition = isset( $files[ $file_key ]['condition']) ? $files[ $file_key ]['condition'] : 'all';

		return apply_filters( 'edd_get_file_price_condition', $condition, $this->ID, $files );

	}

	/**
	 * Retrieve the download type, default or bundle
	 *
	 * @since 2.2
	 * @return string
	 */
	public function get_type() {

		if( ! isset( $this->type ) ) {

			$this->type = get_post_meta( $this->ID, '_edd_product_type', true );

			if( empty( $this->type ) ) {
				$this->type = 'default';
			}

		}

		return apply_filters( 'edd_get_download_type', $this->type, $this->ID );

	}

	/**
	 * Determine if this is a bundled download
	 *
	 * @since 2.2
	 * @return bool
	 */
	public function is_bundled_download() {
		return 'bundle' === $this->get_type();
	}

	/**
	 * Retrieves the Download IDs that are bundled with this Download
	 *
	 * @since 2.2
	 * @return array
	 */
	public function get_bundled_downloads() {

		if( ! isset( $this->bundled_downloads ) ) {

			$this->bundled_downloads = get_post_meta( $this->ID, '_edd_bundled_products', true );

		}

		return (array) apply_filters( 'edd_get_bundled_products', $this->bundled_downloads, $this->ID );

	}

	/**
	 * Retrieve the download notes
	 *
	 * @since 2.2
	 * @return string
	 */
	public function get_notes() {

		if( ! isset( $this->notes ) ) {

			$this->notes = get_post_meta( $this->ID, 'edd_product_notes', true );

		}

		return (string) apply_filters( 'edd_product_notes', $this->notes, $this->ID );

	}

	/**
	 * Retrieve the download sku
	 *
	 * @since 2.2
	 * @return string
	 */
	public function get_sku() {

		if( ! isset( $this->sku ) ) {

			$this->sku = get_post_meta( $this->ID, 'edd_sku', true );

			if ( empty( $this->sku ) ) {
				$this->sku = '-';
			}

		}

		return apply_filters( 'edd_get_download_sku', $this->sku, $this->ID );

	}

	/**
	 * Retrieve the purchase button behavior
	 *
	 * @since 2.2
	 * @return string
	 */
	public function get_button_behavior() {

		if( ! isset( $this->button_behavior ) ) {

			$this->button_behavior = get_post_meta( $this->ID, '_edd_button_behavior', true );

			if( empty( $this->button_behavior ) ) {

				$this->button_behavior = 'add_to_cart';

			}

		}

		return apply_filters( 'edd_get_download_button_behavior', $this->button_behavior, $this->ID );

	}

	/**
	 * Retrieve the sale count for the download
	 *
	 * @since 2.2
	 * @return int
	 */
	public function get_sales() {

		if( ! isset( $this->sales ) ) {

			if ( '' == get_post_meta( $this->ID, '_edd_download_sales', true ) ) {
				add_post_meta( $this->ID, '_edd_download_sales', 0 );
			} // End if

			$this->sales = get_post_meta( $this->ID, '_edd_download_sales', true );

			if ( $this->sales < 0 ) {
				// Never let sales be less than zero
				$this->sales = 0;
			}

		}

		return $this->sales;

	}

	/**
	 * Increment the sale count by one
	 *
	 * @since 2.2
	 * @param int $quantity The quantity to increase the sales by
	 * @return int|false
	 */
	public function increase_sales( $quantity = 1 ) {

		$sales = edd_get_download_sales_stats( $this->ID );
		$sales = $sales + absint( $quantity );

		if ( update_post_meta( $this->ID, '_edd_download_sales', $sales ) ) {
			$this->sales = $sales;
			return $sales;
		}

		return false;
	}

	/**
	 * Decrement the sale count by one
	 *
	 * @since 2.2
	 * @param int $quantity The quantity to decrease by
	 * @return int|false
	 */
	public function decrease_sales( $quantity = 1 ) {

		$sales = edd_get_download_sales_stats( $this->ID );

		// Only decrease if not already zero
		if ( $sales > 0 ) {
			$sales = $sales - absint( $quantity );
		}

		$sales = absint( $sales ); // Sales should never drop below 0

		if ( update_post_meta( $this->ID, '_edd_download_sales', $sales ) ) {
			$this->sales = $sales;
			return $sales;
		}

		return false;

	}

	/**
	 * Retrieve the total earnings for the download
	 *
	 * @since 2.2
	 * @return float
	 */
	public function get_earnings() {

		if ( ! isset( $this->earnings ) ) {

			if ( '' == get_post_meta( $this->ID, '_edd_download_earnings', true ) ) {
				add_post_meta( $this->ID, '_edd_download_earnings', 0 );
			}

			$this->earnings = get_post_meta( $this->ID, '_edd_download_earnings', true );

			if( $this->earnings < 0 ) {
				// Never let earnings be less than zero
				$this->earnings = 0;
			}

		}

		return $this->earnings;

	}

	/**
	 * Increase the earnings by the given amount
	 *
	 * @since 2.2
	 * @return float|false
	 */
	public function increase_earnings( $amount = 0 ) {

		$earnings = edd_get_download_earnings_stats( $this->ID );
		$earnings = $earnings + (float) $amount;

		if ( update_post_meta( $this->ID, '_edd_download_earnings', $earnings ) ) {
			$this->earnings = $earnings;
			return $earnings;
		}

		return false;

	}

	/**
	 * Decrease the earnings by the given amount
	 *
	 * @since 2.2
	 * @param integer $amount
	 * @return float|false
	 */
	public function decrease_earnings( $amount ) {

		$earnings = edd_get_download_earnings_stats( $this->ID );

		if ( $earnings > 0 ) // Only decrease if greater than zero
			$earnings = $earnings - (float) $amount;

		if ( update_post_meta( $this->ID, '_edd_download_earnings', $earnings ) ) {
			$this->earnings = $earnings;
			return $earnings;
		}

		return false;

	}

	/**
	 * Determine if the download is free or if the given price ID is free
	 *
	 * @since 2.2
	 * @return bool
	 */
	public function is_free( $price_id = false ) {

		$is_free = false;
		$variable_pricing = edd_has_variable_prices( $this->ID );

		if ( $variable_pricing && ! is_null( $price_id ) && $price_id !== false ) {

			$price = edd_get_price_option_amount( $this->ID, $price_id );

		} elseif ( $variable_pricing && $price_id === false ) {

			$lowest_price  = (float) edd_get_lowest_price_option( $this->ID );
			$highest_price = (float) edd_get_highest_price_option( $this->ID );

			if ( $lowest_price === 0.00 && $highest_price === 0.00 ) {
				$price = 0;
			}

		} elseif( ! $variable_pricing ) {

			$price = get_post_meta( $this->ID, 'edd_price', true );

		}

		if( isset( $price ) && (float) $price == 0 ) {
			$is_free = true;
		}

		return (bool) apply_filters( 'edd_is_free_download', $is_free, $this->ID, $price_id );

	}

}
