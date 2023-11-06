<?php
/**
 * Download Object
 *
 * @package     EDD
 * @subpackage  Classes/Download
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.2
*/

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Models\Download;
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
	 * @var int
	 */
	public $ID = 0;

	/**
	 * The download price
	 *
	 * @since 2.2
	 * @var float
	 */
	private $price;

	/**
	 * The download prices, if Variable Prices are enabled
	 *
	 * @since 2.2
	 * @var array
	 */
	private $prices;

	/**
	 * The download files
	 *
	 * @since 2.2
	 * @var array
	 */
	private $files;

	/**
	 * The file download limit
	 *
	 * @since 2.2
	 * @var int
	 */
	private $file_download_limit;

	/**
	 * The refund window
	 *
	 * @since 2.2
	 * @var int
	 */
	private $refund_window;

	/**
	 * The download type, default or bundle
	 *
	 * @since 2.2
	 * @var string
	 */
	private $type;

	/**
	 * The bundled downloads, if this is a bundle type
	 *
	 * @since 2.2
	 * @var array
	 */
	private $bundled_downloads;

	/**
	 * The sale count
	 *
	 * @since 2.2
	 * @var int
	 */
	private $sales;

	/**
	 * The total earnings
	 *
	 * @since 2.2
	 * @var float
	 */
	private $earnings;

	/**
	 * The notes
	 *
	 * @since 2.2
	 * @var string
	 */
	private $notes;

	/**
	 * The download SKU
	 *
	 * @since 2.2
	 * @var string
	 */
	private $sku;

	/**
	 * The purchase button behavior
	 *
	 * @since 2.2
	 * @var string
	 */
	private $button_behavior;

	/**
	 * Declare the default properties in WP_Post as we can't extend it
	 * Anything we've declared above has been removed.
	 */
	/**
	 * ID of post author.
	 *
	 * A numeric string, for compatibility reasons.
	 *
	 * @since 3.5.0 - WP Version
	 * @var string
	 */
	public $post_author = 0;

	/**
	 * The post's local publication time.
	 *
	 * @since 3.5.0 - WP Version
	 * @var string
	 */
	public $post_date = '0000-00-00 00:00:00';

	/**
	 * The post's GMT publication time.
	 *
	 * @since 3.5.0 - WP Version
	 * @var string
	 */
	public $post_date_gmt = '0000-00-00 00:00:00';

	/**
	 * The post's content.
	 *
	 * @since 3.5.0 - WP Version
	 * @var string
	 */
	public $post_content = '';

	/**
	 * The post's title.
	 *
	 * @since 3.5.0 - WP Version
	 * @var string
	 */
	public $post_title = '';

	/**
	 * The post's excerpt.
	 *
	 * @since 3.5.0 - WP Version
	 * @var string
	 */
	public $post_excerpt = '';

	/**
	 * The post's status.
	 *
	 * @since 3.5.0 - WP Version
	 * @var string
	 */
	public $post_status = 'publish';

	/**
	 * Whether comments are allowed.
	 *
	 * @since 3.5.0 - WP Version
	 * @var string
	 */
	public $comment_status = 'open';

	/**
	 * Whether pings are allowed.
	 *
	 * @since 3.5.0 - WP Version
	 * @var string
	 */
	public $ping_status = 'open';

	/**
	 * The post's password in plain text.
	 *
	 * @since 3.5.0 - WP Version
	 * @var string
	 */
	public $post_password = '';

	/**
	 * The post's slug.
	 *
	 * @since 3.5.0 - WP Version
	 * @var string
	 */
	public $post_name = '';

	/**
	 * URLs queued to be pinged.
	 *
	 * @since 3.5.0 - WP Version
	 * @var string
	 */
	public $to_ping = '';

	/**
	 * URLs that have been pinged.
	 *
	 * @since 3.5.0 - WP Version
	 * @var string
	 */
	public $pinged = '';

	/**
	 * The post's local modified time.
	 *
	 * @since 3.5.0 - WP Version
	 * @var string
	 */
	public $post_modified = '0000-00-00 00:00:00';

	/**
	 * The post's GMT modified time.
	 *
	 * @since 3.5.0 - WP Version
	 * @var string
	 */
	public $post_modified_gmt = '0000-00-00 00:00:00';

	/**
	 * A utility DB field for post content.
	 *
	 * @since 3.5.0 - WP Version
	 * @var string
	 */
	public $post_content_filtered = '';

	/**
	 * ID of a post's parent post.
	 *
	 * @since 3.5.0 - WP Version
	 * @var int
	 */
	public $post_parent = 0;

	/**
	 * The unique identifier for a post, not necessarily a URL, used as the feed GUID.
	 *
	 * @since 3.5.0 - WP Version
	 * @var string
	 */
	public $guid = '';

	/**
	 * A field used for ordering posts.
	 *
	 * @since 3.5.0 - WP Version
	 * @var int
	 */
	public $menu_order = 0;

	/**
	 * The post's type, like post or page.
	 *
	 * @since 3.5.0 - WP Version
	 * @var string
	 */
	public $post_type = 'post';

	/**
	 * An attachment's mime type.
	 *
	 * @since 3.5.0 - WP Version
	 * @var string
	 */
	public $post_mime_type = '';

	/**
	 * Cached comment count.
	 *
	 * A numeric string, for compatibility reasons.
	 *
	 * @since 3.5.0 - WP Version
	 * @var string
	 */
	public $comment_count = 0;

	/**
	 * Stores the post object's sanitization level.
	 *
	 * Does not correspond to a DB field.
	 *
	 * @since 3.5.0 - WP Version
	 * @var string
	 */
	public $filter;

	/**
	 * The refundability of the download.
	 *
	 * @since 3.0
	 * @var string
	 */
	public $refundability = '';

	/**
	 * Get things going
	 *
	 * @since 2.2
	 */
	public function __construct( $_id = false ) {
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

		if ( ! is_object( $download ) ) {
			return false;
		}

		if ( ! $download instanceof WP_Post ) {
			return false;
		}

		if ( 'download' !== $download->post_type ) {
			return false;
		}

		foreach ( $download as $key => $value ) {
			$this->{$key} = $value;
		}

		return true;
	}

	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 *
	 * @since 2.2
	 */
	public function __get( $key = '' ) {
		if ( method_exists( $this, "get_{$key}" ) ) {
			return call_user_func( array( $this, "get_{$key}" ) );
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

		if ( true === $this->has_variable_prices() ) {
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
		return array_filter( (array) apply_filters( 'edd_get_variable_prices', $this->prices, $this->ID ) );
	}

	/**
	 * Get the default Price ID for variable priced products.
	 *
	 * Since it is possible for the value to not be set on older products, we'll set it to the first price in the array
	 * if one is not set, as that has been the default behavior since default prices were introduced.
	 *
	 * Storing it as the first if found, is just more consistent and intentional.
	 *
	 * @since 3.1.2
	 *
	 * @return int|null The default price ID, or null if the product does not have variable prices.
	 */
	public function get_default_price_id() {
		if ( ! $this->has_variable_prices() ) {
			return null;
		}

		$default_price_id = get_post_meta( $this->ID, '_edd_default_price_id', true );

		// If no default price ID is set, or the default price ID is not in the prices array, set the first price as the default.
		$prices = $this->get_prices();
		if ( is_array( $prices ) && ( ! is_numeric( $default_price_id ) || ! array_key_exists( (int) $default_price_id, $prices ) ) ) {
			$default_price_id = key( $prices );

			// Set the default price ID
			update_post_meta( $this->ID, '_edd_default_price_id', $default_price_id );
		}

		return absint( apply_filters( 'edd_variable_default_price_id', $default_price_id, $this->ID ) );
	}

	/**
	 * Determine if single price mode is enabled or disabled
	 *
	 * @since 2.2
	 * @return bool True if download is in single price mode, false otherwise
	 */
	public function is_single_price_mode() {
		$ret = $this->has_variable_prices() && get_post_meta( $this->ID, '_edd_price_options_mode', true );

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
		if ( ! isset( $this->files ) ) {

			$this->files = array();

			// Bundled products are not allowed to have files
			if ( $this->is_bundled_download() ) {
				return $this->files;
			}

			$download_files = get_post_meta( $this->ID, 'edd_download_files', true );

			if ( ! empty( $download_files ) ) {
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

		if ( ! isset( $this->file_download_limit ) ) {

			// Check the global limit first. The default is 0.
			$limit = edd_get_option( 'file_download_limit', 0 );
			$meta  = get_post_meta( $this->ID, '_edd_download_limit', true );

			// The download specific limit will override the global limit.
			if ( ! empty( $meta ) ) {
				$limit = $meta;
			}

			$this->file_download_limit = absint( $limit );
		}

		return apply_filters( 'edd_file_download_limit', $this->file_download_limit, $this->ID );
	}

	/**
	 * Retrieve the refund window
	 *
	 * @since 3.0
	 * @return int Number of days
	 */
	public function get_refund_window() {

		if ( ! isset( $this->refund_window ) ) {
			$window = get_post_meta( $this->ID, '_edd_refund_window', true );
			$global = edd_get_option( 'refund_window', 0 ); // needs to be 0 here

			// Download specific window
			if ( is_numeric( $window ) ) {
				$retval = absint( $window );

			// Use global
			} elseif ( '' === $window ) {
				$retval = '';

			// Global limit
			} elseif ( ! empty( $global ) ) {
				$retval = absint( $global );

			// Default
			} else {
				$retval = 0;
			}

			$this->refund_window = $retval;
		}

		return $this->refund_window; // No filter
	}

	/**
	 * Retrieve whether the product is refundable.
	 *
	 * @since 3.0
	 *
	 * @return string `refundable` or `nonrefundable`
	 */
	public function get_refundability() {

		if ( ! isset( $this->refundability ) ) {
			$default    = 'refundable';
			$refundable = get_post_meta( $this->ID, '_edd_refundability', true );
			$global     = edd_get_option( 'refundability', $default );

			// Download specific window
			if ( ! empty( $refundable ) ) {
				$retval = $refundable;

			// Use global
			} elseif ( ! empty( $global ) ) {
				$retval = $global;

			// Default
			} else {
				$retval = $default;
			}

			$this->refundability = $retval;
		}

		return $this->refundability; // No filter
	}

	/**
	 * Retrieve the price option that has access to the specified file
	 *
	 * @since 2.2
	 * @return int|string
	 */
	public function get_file_price_condition( $file_key = 0 ) {
		$files     = $this->get_files();
		$condition = isset( $files[ $file_key ]['condition'] )
			? $files[ $file_key ]['condition']
			: 'all';

		return apply_filters( 'edd_get_file_price_condition', $condition, $this->ID, $files );
	}

	/**
	 * Retrieve the download type, default or bundle
	 *
	 * @since 2.2
	 * @return string Type of download, either 'default' or 'bundle'
	 */
	public function get_type() {
		if ( ! isset( $this->type ) ) {
			$this->type = get_post_meta( $this->ID, '_edd_product_type', true );

			if ( empty( $this->type ) ) {
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

		if ( ! isset( $this->bundled_downloads ) ) {
			$this->bundled_downloads = (array) get_post_meta( $this->ID, '_edd_bundled_products', true );
		}

		return (array) apply_filters( 'edd_get_bundled_products', array_filter( $this->bundled_downloads ), $this->ID );
	}

	/**
	 * Retrieve the Download IDs that are bundled with this Download based on the variable pricing ID passed
	 *
	 * @since 2.7
	 * @param int $price_id Variable pricing ID
	 * @return array List of bundled downloads
	 */
	public function get_variable_priced_bundled_downloads( $price_id = null ) {
		if ( null === $price_id ) {
			return $this->get_bundled_downloads();
		}

		$downloads         = array();
		$bundled_downloads = $this->get_bundled_downloads();
		$price_assignments = $this->get_bundle_pricing_variations();

		if ( ! $price_assignments ) {
			return $bundled_downloads;
		}

		$price_assignments = $price_assignments[0];

		foreach ( $price_assignments as $key => $value ) {
			if ( isset( $bundled_downloads[ $key ] ) && ( $value == $price_id || $value == 'all' ) ) {
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

		if ( ! isset( $this->notes ) ) {
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

		if ( ! isset( $this->sku ) ) {

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

		if ( ! isset( $this->button_behavior ) ) {

			if ( ! edd_shop_supports_buy_now() ) {
				$button_behavior = 'add_to_cart';
			} else {
				$button_behavior = get_post_meta( $this->ID, '_edd_button_behavior', true );
				if ( empty( $button_behavior ) || ( 'direct' === $button_behavior && ! $this->supports_buy_now() ) ) {
					$button_behavior = 'add_to_cart';
				}
			}

			$this->button_behavior = $button_behavior;
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

		if ( ! isset( $this->sales ) ) {

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

		_edd_deprecated_function( __METHOD__, '3.0', 'EDD_Download::recalculate_net_sales_earnings()' );
		edd_recalculate_download_sales_earnings( $this->ID );

		$this->get_sales();
		do_action( 'edd_download_increase_sales', $this->ID, $this->sales, $this );

		return $this->sales;
	}

	/**
	 * Decrement the sale count by one
	 *
	 * @since 2.2
	 * @param int $quantity The quantity to decrease by
	 * @return int New number of total sales
	 */
	public function decrease_sales( $quantity = 1 ) {

		_edd_deprecated_function( __METHOD__, '3.0', 'EDD_Download::recalculate_net_sales_earnings()' );
		$this->recalculate_net_sales_earnings();

		$this->get_sales();
		do_action( 'edd_download_decrease_sales', $this->ID, $this->sales, $this );

		return $this->sales;
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

		_edd_deprecated_function( __METHOD__, '3.0', 'edd_recalculate_download_sales_earnings()' );
		edd_recalculate_download_sales_earnings( $this->ID );

		$this->get_earnings();
		do_action( 'edd_download_increase_earnings', $this->ID, $this->earnings, $this );

		return $this->earnings;
	}

	/**
	 * Decrease the earnings by the given amount
	 *
	 * @since 2.2
	 * @param int|float $amount Number to decrease earning with
	 * @return float New number of total earnings
	 */
	public function decrease_earnings( $amount ) {

		_edd_deprecated_function( __METHOD__, '3.0', 'EDD_Download::recalculate_net_sales_earnings()' );

		$this->recalculate_net_sales_earnings();
		$this->get_earnings();

		do_action( 'edd_download_decrease_earnings', $this->ID, $this->earnings, $this );

		return $this->earnings;
	}

	/**
	 * Updates the gross sales and earnings for a download.
	 *
	 * @since 3.0
	 * @return void
	 */
	public function recalculate_gross_sales_earnings() {
		$download_model = new Download( $this->ID );

		// This currently uses the post meta functions as we do not yet guarantee that the meta exists.
		update_post_meta( $this->ID, '_edd_download_gross_sales', $download_model->get_gross_sales() );
		update_post_meta( $this->ID, '_edd_download_gross_earnings', floatval( $download_model->get_gross_earnings() ) );
	}

	/**
	 * Recalculates the net sales and earnings for a download.
	 *
	 * @since 3.0
	 * @return void
	 */
	public function recalculate_net_sales_earnings() {
		$download_model = new Download( $this->ID );

		$this->update_meta( '_edd_download_sales', intval( $download_model->get_net_sales() ) );
		$this->update_meta( '_edd_download_earnings', floatval( $download_model->get_net_earnings() ) );
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

		} elseif ( ! $variable_pricing ) {

			$price = get_post_meta( $this->ID, 'edd_price', true );
		}

		if ( isset( $price ) && (float) $price == 0 ) {
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

		if ( empty( $meta_key ) || ( ! is_numeric( $meta_value ) && empty( $meta_value ) ) ) {
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
	 * @return bool If the current user can purchase the download ID
	 */
	public function can_purchase() {
		$can_purchase = true;

		if ( 'publish' !== $this->post_status && ! current_user_can( 'edit_post', $this->ID ) ) {
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

	/**
	 * Determine if the download can support the Buy Now feature.
	 *
	 * @since 3.2.2
	 * @param int|null $price_id The price ID to check for.
	 *
	 * @return bool True if the download can support Buy Now, false otherwise.
	 */
	public function supports_buy_now( $price_id = null ) {
		// We have a few addons we have to check for, that would prevent Buy Now from working.
		$recurring_active      = function_exists( 'edd_recurring' );
		$free_downloads_active = function_exists( 'edd_free_downloads_use_modal' );

		// If Recurring and Free Downloads are not present, we can return true.
		if ( false === $recurring_active && false === $free_downloads_active ) {
			return true;
		}

		// Free downloads does not support Buy Now.
		if ( $free_downloads_active && ! $this->has_variable_prices() ) {
			$price = get_post_meta( $this->ID, 'edd_price', true );
			// If the download is free, we can return false. This check bypasses the is_free() method, to omit the filter.
			if ( empty( $price ) && edd_free_downloads_use_modal( $this->ID ) ) {
				return false;
			}
		}

		// Subscription products cannot support Buy Now.
		if ( $recurring_active ) {
			if ( $this->has_variable_prices() ) {
				// Parse if we have a price ID passed in.
				$price_id = is_numeric( $price_id ) ? intval( $price_id ) : null;
				// If no Price ID was passed in, and the product has variable prices, return false if any of the prices are recurring.
				if ( null === $price_id ) {
					foreach ( $this->get_prices() as $key => $price ) {
						if ( edd_recurring()->is_price_recurring( $this->ID, $key ) ) {
							return false;
						}
					}
				}

				$is_recurring = edd_recurring()->is_price_recurring( $this->ID, $price_id );
			} else {
				$is_recurring = edd_recurring()->is_recurring( $this->ID );
			}

			if ( $is_recurring ) {
				return false;
			}
		}

		return true;
	}
}
