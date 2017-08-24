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
	 * Declare the default properties in WP_Post as we can't extend it
	 * Anything we've declared above has been removed.
	 */
	public $post_author = 0;
	public $post_date = '0000-00-00 00:00:00';
	public $post_date_gmt = '0000-00-00 00:00:00';
	public $post_content = '';
	public $post_title = '';
	public $post_excerpt = '';
	public $post_status = 'publish';
	public $comment_status = 'open';
	public $ping_status = 'open';
	public $post_password = '';
	public $post_name = '';
	public $to_ping = '';
	public $pinged = '';
	public $post_modified = '0000-00-00 00:00:00';
	public $post_modified_gmt = '0000-00-00 00:00:00';
	public $post_content_filtered = '';
	public $post_parent = 0;
	public $guid = '';
	public $menu_order = 0;
	public $post_mime_type = '';
	public $comment_count = 0;
	public $filter;

	/**
	 * Get things going
	 *
	 * @since 2.2
	 */
	public function __construct( $_id = false, $_args = array() ) {

		$download = WP_Post::get_instance( $_id );

		return $this->setup_download( $download );

	}

	/**
	 * Given the download data, let's set the variables
	 *
	 * @since  2.3.6
	 * @param  WP_Post $download The WP_Post object for download.
	 * @return bool             If the setup was successful or not
	 */
	private function setup_download( $download ) {

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

			switch ( $key ) {

				default:
					$this->$key = $value;
					break;

			}

		}

		return true;

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

			return new WP_Error( 'edd-download-invalid-property', sprintf( __( 'Can\'t get property %s', 'easy-digital-downloads' ), $key ) );

		}

	}

	/**
	 * Creates a download
	 *
	 * @since  2.3.6
	 * @param  array  $data Array of attributes for a download
	 * @return mixed  false if data isn't passed and class not instantiated for creation, or New Download ID
	 */
	public function create( $data = array() ) {

		if ( $this->id != 0 ) {
			return false;
		}

		$defaults = array(
			'post_type'   => 'download',
			'post_status' => 'draft',
			'post_title'  => __( 'New Download Product', 'easy-digital-downloads' )
		);

		$args = wp_parse_args( $data, $defaults );

		/**
		 * Fired before a download is created
		 *
		 * @param array $args The post object arguments used for creation.
		 */
		do_action( 'edd_download_pre_create', $args );

		$id = wp_insert_post( $args, true );

		$download = WP_Post::get_instance( $id );

		/**
		 * Fired after a download is created
		 *
		 * @param int   $id   The post ID of the created item.
		 * @param array $args The post object arguments used for creation.
		 */
		do_action( 'edd_download_post_create', $id, $args );

		return $this->setup_download( $download );

	}

	/**
	 * Retrieve the ID
	 *
	 * @since 2.2
	 * @return int ID of the download
	 */
	public function get_ID() {

		return $this->ID;

	}

	/**
	 * Retrieve the download name
	 *
	 * @since 2.5.8
	 * @return string Name of the download
	 */
	public function get_name() {
		return get_the_title( $this->ID );
	}

	/**
	 * Retrieve the price
	 *
	 * @since 2.2
	 * @return float Price of the download
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

		/**
		 * Override the download price.
		 *
		 * @since 2.2
		 *
		 * @param string $price The download price(s).
		 * @param string|int $id The downloads ID.
		 */
		return apply_filters( 'edd_get_download_price', $this->price, $this->ID );

	}

	/**
	 * Retrieve the variable prices
	 *
	 * @since 2.2
	 * @return array List of the variable prices
	 */
	public function get_prices() {

		$this->prices = array();

		if( true === $this->has_variable_prices() ) {

			if ( empty( $this->prices ) ) {
				$this->prices = get_post_meta( $this->ID, 'edd_variable_prices', true );
			}

		}

		/**
		 * Override variable prices
		 *
		 * @since 2.2
		 *
		 * @param array $prices The array of variables prices.
		 * @param int|string The ID of the download.
		 */
		return apply_filters( 'edd_get_variable_prices', $this->prices, $this->ID );

	}

	/**
	 * Determine if single price mode is enabled or disabled
	 *
	 * @since 2.2
	 * @return bool True if download is in single price mode, false otherwise
	 */
	public function is_single_price_mode() {

		$ret = get_post_meta( $this->ID, '_edd_price_options_mode', true );

		/**
		 * Override the price mode for a download when checking if is in single price mode.
		 *
		 * @since 2.3
		 *
		 * @param bool $ret Is download in single price mode?
		 * @param int|string The ID of the download.
		 */
		return (bool) apply_filters( 'edd_single_price_option_mode', $ret, $this->ID );

	}

	/**
	 * Determine if the download has variable prices enabled
	 *
	 * @since 2.2
	 * @return bool True when the download has variable pricing enabled, false otherwise
	 */
	public function has_variable_prices() {

		$ret = get_post_meta( $this->ID, '_variable_pricing', true );

		/**
		 * Override whether the download has variables prices.
		 *
		 * @since 2.3
		 *
		 * @param bool $ret Does download have variable prices?
		 * @param int|string The ID of the download.
		 */
		return (bool) apply_filters( 'edd_has_variable_prices', $ret, $this->ID );

	}

	/**
	 * Retrieve the file downloads
	 *
	 * @since 2.2
	 * @param integer $variable_price_id
	 * @return array List of download files
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
	 * @return int Number of download limit
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

		$files    = $this->get_files();
		$condition = isset( $files[ $file_key ]['condition']) ? $files[ $file_key ]['condition'] : 'all';

		return apply_filters( 'edd_get_file_price_condition', $condition, $this->ID, $files );

	}

	/**
	 * Retrieve the download type, default or bundle
	 *
	 * @since 2.2
	 * @return string Type of download, either 'default' or 'bundle'
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
	 * @return bool True when download is a bundle, false otherwise
	 */
	public function is_bundled_download() {
		return 'bundle' === $this->get_type();
	}

	/**
	 * Retrieves the Download IDs that are bundled with this Download
	 *
	 * @since 2.2
	 * @return array List of bundled downloads
	 */
	public function get_bundled_downloads() {

		if( ! isset( $this->bundled_downloads ) ) {

			$this->bundled_downloads = (array) get_post_meta( $this->ID, '_edd_bundled_products', true );

		}

		return (array) apply_filters( 'edd_get_bundled_products', array_filter( $this->bundled_downloads ), $this->ID );

	}

	/**
	 * Retrieve the Download IDs that are bundled with this Download based on the variable pricing ID passed
	 *
	 * @since 2.7
	 * @access public
	 * @param int $price_id Variable pricing ID
	 * @return array List of bundled downloads
	 */
	public function get_variable_priced_bundled_downloads( $price_id = null ) {
		if ( null == $price_id ) {
			return $this->get_bundled_downloads();
		}

		$downloads         = array();
		$bundled_downloads = $this->get_bundled_downloads();
		$price_assignments = $this->get_bundle_pricing_variations();

		if ( ! $price_assignments ) {
			return $bundled_downloads;
		}

		$price_assignments = $price_assignments[0];
		$price_assignments = array_values( $price_assignments );

		foreach ( $price_assignments as $key => $value ) {
			if ( $value == $price_id || $value == 'all' ) {
				$downloads[] = $bundled_downloads[ $key ];
			}
		}

		return $downloads;
	}

	/**
	 * Retrieve the download notes
	 *
	 * @since 2.2
	 * @return string Note related to the download
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
	 * @return string SKU of the download
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

			if( empty( $this->button_behavior ) || ! edd_shop_supports_buy_now() ) {

				$this->button_behavior = 'add_to_cart';

			}

		}

		return apply_filters( 'edd_get_download_button_behavior', $this->button_behavior, $this->ID );

	}

	/**
	 * Retrieve the sale count for the download
	 *
	 * @since 2.2
	 * @return int Number of times this has been purchased
	 */
	public function get_sales() {

		if( ! isset( $this->sales ) ) {

			if ( '' == get_post_meta( $this->ID, '_edd_download_sales', true ) ) {
				add_post_meta( $this->ID, '_edd_download_sales', 0 );
			}

			$this->sales = get_post_meta( $this->ID, '_edd_download_sales', true );

			// Never let sales be less than zero
			$this->sales = max( $this->sales, 0 );

		}

		return $this->sales;

	}

	/**
	 * Increment the sale count by one
	 *
	 * @since 2.2
	 * @param int $quantity The quantity to increase the sales by
	 * @return int New number of total sales
	 */
	public function increase_sales( $quantity = 1 ) {

		$quantity    = absint( $quantity );
		$total_sales = $this->get_sales() + $quantity;

		if ( $this->update_meta( '_edd_download_sales', $total_sales ) ) {

			$this->sales = $total_sales;

			do_action( 'edd_download_increase_sales', $this->ID, $this->sales, $this );

			return $this->sales;

		}

		return false;
	}

	/**
	 * Decrement the sale count by one
	 *
	 * @since 2.2
	 * @param int $quantity The quantity to decrease by
	 * @return int New number of total sales
	 */
	public function decrease_sales( $quantity = 1 ) {

		// Only decrease if not already zero
		if ( $this->get_sales() > 0 ) {

			$quantity    = absint( $quantity );
			$total_sales = $this->get_sales() - $quantity;

			if ( $this->update_meta( '_edd_download_sales', $total_sales ) ) {

				$this->sales = $total_sales;

				do_action( 'edd_download_decrease_sales', $this->ID, $this->sales, $this );

				return $this->sales;

			}

		}

		return false;

	}

	/**
	 * Retrieve the total earnings for the download
	 *
	 * @since 2.2
	 * @return float Total download earnings
	 */
	public function get_earnings() {

		if ( ! isset( $this->earnings ) ) {

			if ( '' == get_post_meta( $this->ID, '_edd_download_earnings', true ) ) {
				add_post_meta( $this->ID, '_edd_download_earnings', 0 );
			}

			$this->earnings = get_post_meta( $this->ID, '_edd_download_earnings', true );

			// Never let earnings be less than zero
			$this->earnings = max( $this->earnings, 0 );

		}

		return $this->earnings;

	}

	/**
	 * Increase the earnings by the given amount
	 *
	 * @since 2.2
	 * @param int|float $amount Amount to increase the earnings by
	 * @return float New number of total earnings
	 */
	public function increase_earnings( $amount = 0 ) {

		$current_earnings = $this->get_earnings();
		$new_amount = apply_filters( 'edd_download_increase_earnings_amount', $current_earnings + (float) $amount, $current_earnings, $amount, $this );

		if ( $this->update_meta( '_edd_download_earnings', $new_amount ) ) {

			$this->earnings = $new_amount;

			do_action( 'edd_download_increase_earnings', $this->ID, $this->earnings, $this );

			return $this->earnings;

		}

		return false;

	}

	/**
	 * Decrease the earnings by the given amount
	 *
	 * @since 2.2
	 * @param int|float $amount Number to decrease earning with
	 * @return float New number of total earnings
	 */
	public function decrease_earnings( $amount ) {

		// Only decrease if greater than zero
		if ( $this->get_earnings() > 0 ) {

			$current_earnings = $this->get_earnings();
			$new_amount = apply_filters( 'edd_download_decrease_earnings_amount', $current_earnings - (float) $amount, $current_earnings, $amount, $this );

			if ( $this->update_meta( '_edd_download_earnings', $new_amount ) ) {

				$this->earnings = $new_amount;

				do_action( 'edd_download_decrease_earnings', $this->ID, $this->earnings, $this );

				return $this->earnings;

			}

		}

		return false;

	}

	/**
	 * Determine if the download is free or if the given price ID is free
	 *
	 * @since 2.2
	 * @param bool $price_id ID of variation if needed
	 * @return bool True when the download is free, false otherwise
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

	/**
	 * Is quantity input disabled on this product?
	 *
	 * @since 2.7
	 * @return bool
	 */
	public function quantities_disabled() {

		$ret = (bool) get_post_meta( $this->ID, '_edd_quantities_disabled', true );
		return apply_filters( 'edd_download_quantity_disabled', $ret, $this->ID );

	}

	/**
	 * Updates a single meta entry for the download
	 *
	 * @since  2.3
	 * @access private
	 * @param  string $meta_key   The meta_key to update
	 * @param  string|array|object $meta_value The value to put into the meta
	 * @return bool             The result of the update query
	 */
	private function update_meta( $meta_key = '', $meta_value = '' ) {

		global $wpdb;

		if ( empty( $meta_key ) || empty( $meta_value ) ) {
			return false;
		}

		// Make sure if it needs to be serialized, we do
		$meta_value = maybe_serialize( $meta_value );

		if ( is_numeric( $meta_value ) ) {
			$value_type = is_float( $meta_value ) ? '%f' : '%d';
		} else {
			$value_type = "'%s'";
		}

		$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = $value_type WHERE post_id = $this->ID AND meta_key = '%s'", $meta_value, $meta_key );

		if ( $wpdb->query( $sql ) ) {

			clean_post_cache( $this->ID );
			return true;

		}

		return false;
	}

	/**
	 * Checks if the download can be purchased
	 *
	 * NOTE: Currently only checks on edd_get_cart_contents() and edd_add_to_cart()
	 *
	 * @since  2.6.4
	 * @return bool If the current user can purcahse the download ID
	 */
	public function can_purchase() {
		$can_purchase = true;

		if ( ! current_user_can( 'edit_post', $this->ID ) && $this->post_status != 'publish' ) {
			$can_purchase = false;
		}

		return (bool) apply_filters( 'edd_can_purchase_download', $can_purchase, $this );
	}

	/**
	 * Get pricing variations for bundled items
	 *
	 * @since 2.7
	 * @return array
	 */
	public function get_bundle_pricing_variations() {
		return get_post_meta( $this->ID, '_edd_bundled_products_conditions' );
	}

}
