<?php
/**
 * Discount Object
 *
 * @package     EDD
 * @subpackage  Classes/Discount
 * @copyright   Copyright (c) 2016, Sunny Ratilal
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Discount Class
 *
 * @since 2.7
 */
class EDD_Discount {
	/**
	 * Discount ID.
	 *
	 * @since 2.7
	 * @access public
	 * @var int
	 */
	public $ID = 0;

	/**
	 * Discount Name.
	 *
	 * @since 2.7
	 * @access public
	 * @var string
	 */
	public $name;

	/**
	 * Discount Code.
	 *
	 * @since 2.7
	 * @access public
	 * @var string
	 */
	public $code;

	/**
	 * Discount Status (Active or Inactive).
	 *
	 * @since 2.7
	 * @access public
	 * @var string
	 */
	public $status;

	/**
	 * Discount Type (Percentage or Flat Amount).
	 *
	 * @since 2.7
	 * @access public
	 * @var string
	 */
	public $type;

	/**
	 * Discount Amount.
	 *
	 * @since 2.7
	 * @access public
	 * @var mixed float|int
	 */
	public $amount = 0;

	/**
	 * Download Requirements.
	 *
	 * @since 2.7
	 * @access public
	 * @var array
	 */
	public $download_requirements;

	/**
	 * Excluded Downloads.
	 *
	 * @since 2.7
	 * @access public
	 * @var array
	 */
	public $excluded_downloads;

	/**
	 * Start Date.
	 *
	 * @since 2.7
	 * @access public
	 * @var string
	 */
	public $start_date;

	/**
	 * End Date.
	 *
	 * @since 2.7
	 * @access public
	 * @var string
	 */
	public $end_date;

	/**
	 * Maximum Uses.
	 *
	 * @since 2.7
	 * @access public
	 * @var int
	 */
	public $max_uses;

	/**
	 * Minimum Amount.
	 *
	 * @since 2.7
	 * @access public
	 * @var mixed int|float
	 */
	public $min_amount;

	/**
	 * Is Single Use?
	 *
	 * @since 2.7
	 * @access public
	 * @var bool
	 */
	public $is_single_use;

	/**
	 * Declare the default properties in WP_Post as we can't extend it
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
	 * Constructor.
	 *
	 * @since 2.7
	 * @access protected
	 */
	public function __construct( $_id = false, $_args = array() ) {
		$discount = WP_Post::get_instance( $_id );
		return $this->setup_discount( $discount );
	}

	/**
	 * Magic __get method to dispatch a call to retrieve a private property
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @param mixed $key
	 * @return mixed
	 */
	public function __get( $key ) {
		$key = sanitize_key( $key );

		if ( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		} else {
			return new WP_Error( 'edd-discount-invalid-property', sprintf( __( 'Can\'t get property %s', 'easy-digital-downloads' ), $key ) );
		}
	}

	/**
	 * Setup object vars with discount WP_Post object.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @param object $discount WP_Post instance of the discount.
	 * @return bool Object var initialisation successful or not.
	 */
	private function setup_discount( $discount = null ) {
		if ( null == $discount ) {
			return false;
		}

		if ( ! is_object( $discount ) ) {
			return false;
		}

		if ( ! is_a( $discount, 'WP_Post' ) ) {
			return false;
		}

		if ( 'edd_discount' !== $discount->post_type ) {
			return false;
		}

		/**
		 * Setup discount object vars with WP_Post vars
		 */
		foreach ( $discount as $key => $value ) {
			$this->{$key} = $value;
		}

		return true;
	}

	/**
	 * Retrieve the ID of the WP_Post object.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return int Discount ID.
	 */
	public function get_ID() {
		return $this->ID;
	}

	/**
	 * Retrieve the name of the discount.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return string Name of the download.
	 */
	public function get_name() {
		return get_the_title( $this->ID );
	}

	/**
	 * Retrieve the code used to apply the discount.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return string Discount code.
	 */
	public function get_code() {
		$code = get_post_meta( $this->ID, '_edd_discount_code', true );

		/**
		 * Filters the discount code.
		 *
		 * @since 2.7
		 *
		 * @param string $code Discount code.
		 * @param int    $ID   Discount ID.
		 */
		return apply_filters( 'edd_get_discount_code', $code, $this->ID );
	}

	/**
	 * Retrieve the status of the discount
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return string Discount code status (active/inactive).
	 */
	public function get_status() {
		$status = get_post_meta( $this->ID, '_edd_discount_status', true );

		/**
		 * Filters the discount status.
		 *
		 * @since 2.7
		 *
		 * @param string $code Discount status (active or inactive).
		 * @param int    $ID   Discount ID.
		 */
		return apply_filters( 'edd_get_discount_status', $status, $this->ID );
	}

	/**
	 * Retrieve the type of discount.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return string Discount type (percent or flat amount).
	 */
	public function get_type() {
		$type = strtolower( get_post_meta( $this->ID, '_edd_discount_type', true ) );

		/**
		 * Filters the discount type.
		 *
		 * @since 2.7
		 *
		 * @param string $code Discount type (percent or flat amount).
		 * @param int    $ID   Discount ID.
		 */
		return apply_filters( 'edd_get_discount_type', $type, $this->ID );
	}

	/**
	 * Retrieve the discount amount.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return mixed float Discount amount.
	 */
	public function get_amount() {
		$amount = get_post_meta( $this->ID, '_edd_discount_amount', true );

		/**
		 * Filters the discount amount.
		 *
		 * @since 2.7
		 *
		 * @param float $amount Discount amount.
		 * @param int    $ID    Discount ID.
		 */
		return (float) apply_filters( 'edd_get_discount_amount', $amount, $this->ID );
	}

	/**
	 * Retrieve the discount requirements for the discount to be satisfied.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return array IDs of required downloads.
	 */
	public function get_download_requirements() {
		$download_requirements = get_post_meta( $this->ID, '_edd_discount_product_reqs', true );

		if ( empty( $download_requirements ) || ! is_array( $download_requirements ) ) {
			$download_requirements = array();
		}

		/**
		 * Filters the download requirements.
		 *
		 * @since 2.7
		 *
		 * @param array $download_requirements IDs of required downloads.
		 * @param int   $ID                    Discount ID.
		 */
		return (array) apply_filters( 'edd_get_discount_product_reqs', $download_requirements, $this->ID );
	}

	/**
	 * Retrieve the downloads that are excluded from having this discount code applied.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return array IDs of excluded downloads.
	 */
	public function get_excluded_downloads() {
		$excluded_downloads = get_post_meta( $this->ID, '_edd_discount_excluded_products', true );

		if ( empty( $excluded_downloads ) || ! is_array( $excluded_downloads ) ) {
			$excluded_downloads = array();
		}

		/**
		 * Filters the excluded downloads.
		 *
		 * @since 2.7
		 *
		 * @param array $excluded_downloads IDs of excluded downloads.
		 * @param int   $ID                 Discount ID.
		 */
		return (array) apply_filters( 'edd_get_discount_excluded_products', $excluded_downloads, $this->ID );
	}

	/**
	 * Retrieve the start date.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return string Start date
	 */
	public function get_start_date() { }

	/**
	 * Retrieve the end date.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return string End date
	 */
	public function get_end_date() { }

	/**
	 * Retrieve the maximum uses for the discount code.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return int Maximum uses
	 */
	public function get_max_uses() { }

	/**
	 * Retrieve the minimum spend required for the discount to be satisfied
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return mixed int|float Minimum spend.
	 */
	public function get_min_amount() { }

	/**
	 * Retrieve the usage limit per limit (if the discount can only be used once per customer)
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return bool Once use per customer?
	 */
	public function get_is_single_use() { }

	/**
	 * Helper function to get discounts by a meta key and value provided.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @param string $key   Value of the meta key to retrieve.
	 * @param string $value Meta value for the key passed.
	 * @return mixed array|bool
	 */
	public function get_by( $field = '', $value = '' ) {
		if ( empty( $field ) || empty( $value ) ) {
			return false;
		}

		if ( ! is_string( $field ) ) {
			return false;
		}

		switch ( strtolower( $field ) ) {
			case 'code':
				break;

			case 'id':
				break;

			case 'name':
				break;

			default:
				return false;
		}

		return false;
	}

	/**
	 * Create a new discount.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @param array $args Discount details
	 * @return mixed bool|int false if data isn't passed and class not instantiated for creation, or post ID for the new discount
	 */
	public function add() {  }
}