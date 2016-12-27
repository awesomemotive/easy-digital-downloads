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
	 * @access protected
	 * @var int
	 */
	protected $ID = 0;

	/**
	 * Discount Name.
	 *
	 * @since 2.7
	 * @access protected
	 * @var string
	 */
	protected $name = null;

	/**
	 * Discount Code.
	 *
	 * @since 2.7
	 * @access protected
	 * @var string
	 */
	protected $code = null;

	/**
	 * Discount Status (Active or Inactive).
	 *
	 * @since 2.7
	 * @access protected
	 * @var string
	 */
	protected $status = null;

	/**
	 * Discount Type (Percentage or Flat Amount).
	 *
	 * @since 2.7
	 * @access protected
	 * @var string
	 */
	protected $type = null;

	/**
	 * Discount Amount.
	 *
	 * @since 2.7
	 * @access protected
	 * @var mixed float|int
	 */
	protected $amount = null;

	/**
	 * Download Requirements.
	 *
	 * @since 2.7
	 * @access protected
	 * @var array
	 */
	protected $download_requirements = null;

	/**
	 * Excluded Downloads.
	 *
	 * @since 2.7
	 * @access protected
	 * @var array
	 */
	protected $excluded_downloads = null;

	/**
	 * Start Date.
	 *
	 * @since 2.7
	 * @access protected
	 * @var string
	 */
	protected $start_date = null;

	/**
	 * End Date.
	 *
	 * @since 2.7
	 * @access protected
	 * @var string
	 */
	protected $end_date = null;

	/**
	 * Maximum Uses.
	 *
	 * @since 2.7
	 * @access protected
	 * @var int
	 */
	protected $max_uses = null;

	/**
	 * Minimum Amount.
	 *
	 * @since 2.7
	 * @access protected
	 * @var mixed int|float
	 */
	protected $min_amount = null;

	/**
	 * Is Single Use?
	 *
	 * @since 2.7
	 * @access protected
	 * @var bool
	 */
	protected $is_single_use = null;

	/**
	 * Is Not Global?
	 *
	 * @since 2.7
	 * @access protected
	 * @var bool
	 */
	protected $is_not_global = null;

	/**
	 * Declare the default properties in WP_Post as we can't extend it
	 */
	protected $post_author = 0;
	protected $post_date = '0000-00-00 00:00:00';
	protected $post_date_gmt = '0000-00-00 00:00:00';
	protected $post_content = '';
	protected $post_title = '';
	protected $post_excerpt = '';
	protected $post_status = 'publish';
	protected $comment_status = 'open';
	protected $ping_status = 'open';
	protected $post_password = '';
	protected $post_name = '';
	protected $to_ping = '';
	protected $pinged = '';
	protected $post_modified = '0000-00-00 00:00:00';
	protected $post_modified_gmt = '0000-00-00 00:00:00';
	protected $post_content_filtered = '';
	protected $post_parent = 0;
	protected $guid = '';
	protected $menu_order = 0;
	protected $post_mime_type = '';
	protected $comment_count = 0;
	protected $filter;

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
	 * Magic __isset method to allow empty checks on protected elements
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @param string $key The attribute to get
	 * @return boolean If the item is set or not
	 */
	public function __isset( $key ) {
		if ( property_exists( $this, $key) ) {
			return false === empty( $this->$key );
		} else {
			return null;
		}
	}

	/**
	 * Converts the instance of the EDD_Discount object into an array for special cases.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return array EDD_Discount object as an array.
	 */
	public function array_convert() {
		return get_object_vars( $this );
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
		if ( null == $this->name ) {
			$this->name = get_the_title( $this->ID );
		}

		return $this->name;
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
		if ( null == $this->code ) {
			$this->code = get_post_meta( $this->ID, '_edd_discount_code', true );
		}

		/**
		 * Filters the discount code.
		 *
		 * @since 2.7
		 *
		 * @param string $code Discount code.
		 * @param int    $ID   Discount ID.
		 */
		return apply_filters( 'edd_get_discount_code', $this->code, $this->ID );
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
		if ( null == $this->status ) {
			$this->status = get_post_meta( $this->ID, '_edd_discount_status', true );
		}

		/**
		 * Filters the discount status.
		 *
		 * @since 2.7
		 *
		 * @param string $code Discount status (active or inactive).
		 * @param int    $ID   Discount ID.
		 */
		return apply_filters( 'edd_get_discount_status', $this->status, $this->ID );
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
		if ( null == $this->type ) {
			$this->type = strtolower( get_post_meta( $this->ID, '_edd_discount_type', true ) );
		}

		/**
		 * Filters the discount type.
		 *
		 * @since 2.7
		 *
		 * @param string $code Discount type (percent or flat amount).
		 * @param int    $ID   Discount ID.
		 */
		return apply_filters( 'edd_get_discount_type', $this->type, $this->ID );
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
		if ( null == $this->amount ) {
			$this->amount = get_post_meta( $this->ID, '_edd_discount_amount', true );
		}

		/**
		 * Filters the discount amount.
		 *
		 * @since 2.7
		 *
		 * @param float $amount Discount amount.
		 * @param int    $ID    Discount ID.
		 */
		return (float) apply_filters( 'edd_get_discount_amount', $this->amount, $this->ID );
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
		if ( null == $this->download_requirements ) {
			$this->download_requirements = get_post_meta( $this->ID, '_edd_discount_product_reqs', true );
		}

		if ( empty( $this->download_requirements ) || ! is_array( $this->download_requirements ) ) {
			$this->download_requirements = array();
		}

		/**
		 * Filters the download requirements.
		 *
		 * @since 2.7
		 *
		 * @param array $download_requirements IDs of required downloads.
		 * @param int   $ID                    Discount ID.
		 */
		return (array) apply_filters( 'edd_get_discount_product_reqs', $this->download_requirements, $this->ID );
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
		if ( null == $this->excluded_downloads ) {
			$this->excluded_downloads = get_post_meta( $this->ID, '_edd_discount_excluded_products', true );
		}

		if ( empty( $this->excluded_downloads ) || ! is_array( $this->excluded_downloads ) ) {
			$this->excluded_downloads = array();
		}

		/**
		 * Filters the excluded downloads.
		 *
		 * @since 2.7
		 *
		 * @param array $excluded_downloads IDs of excluded downloads.
		 * @param int   $ID                 Discount ID.
		 */
		return (array) apply_filters( 'edd_get_discount_excluded_products', $this->excluded_downloads, $this->ID );
	}

	/**
	 * Retrieve the start date.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return string Start date.
	 */
	public function get_start_date() {
		if ( null == $this->start_date ) {
			$this->start_date = get_post_meta( $this->ID, '_edd_discount_start', true );
		}

		/**
		 * Filters the start date.
		 *
		 * @since 2.7
		 *
		 * @param string $start_date Discount start date.
		 * @param int    $ID         Discount ID.
		 */
		return apply_filters( 'edd_get_discount_start_date', $this->start_date, $this->ID );
	}

	/**
	 * Retrieve the end date.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return string End date.
	 */
	public function get_end_date() {
		if ( null == $this->end_date ) {
			$this->end_date = get_post_meta( $this->ID, '_edd_discount_expiration', true );
		}

		/**
		 * Filters the end date.
		 *
		 * @since 2.7
		 *
		 * @param array $end_date Discount end (expiration) date.
		 * @param int   $ID       Discount ID.
		 */
		return apply_filters( 'edd_get_discount_expiration', $this->end_date, $this->ID );
	}

	/**
	 * Retrieve the maximum uses for the discount code.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return int Maximum uses.
	 */
	public function get_max_uses() {
		if ( null == $this->max_uses ) {
			$this->max_uses = get_post_meta( $this->ID, '_edd_discount_max_uses', true );
		}

		/**
		 * Filters the maximum uses.
		 *
		 * @since 2.7
		 *
		 * @param int $max_uses Maximum uses.
		 * @param int $ID       Discount ID.
		 */
		return (int) apply_filters( 'edd_get_discount_max_uses', $this->max_uses, $this->ID );
	}

	/**
	 * Retrieve the minimum spend required for the discount to be satisfied.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return mixed float Minimum spend.
	 */
	public function get_min_amount() {
		if ( null == $this->min_amount ) {
			$this->min_amount = get_post_meta( $this->ID, '_edd_discount_min_price', true );
		}

		/**
		 * Filters the minimum amount.
		 *
		 * @since 2.7
		 *
		 * @param float $min_amount Minimum amount.
		 * @param int   $ID         Discount ID.
		 */
		return (float) apply_filters( 'edd_get_discount_min_price', $this->min_amount, $this->ID );
	}

	/**
	 * Retrieve the usage limit per limit (if the discount can only be used once per customer).
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return bool Once use per customer?
	 */
	public function get_is_single_use() {
		if ( null == $this->is_single_use ) {
			$this->is_single_use = get_post_meta( $this->ID, '_edd_discount_is_single_use', true );
		}

		/**
		 * Filters the single use meta value.
		 *
		 * @since 2.7
		 *
		 * @param bool $is_single_use Is the discount only allowed to be used once per customer.
		 * @param int  $ID            Discount ID.
		 */
		return (bool) apply_filters( 'edd_is_discount_single_use', $this->is_single_use, $this->ID );
	}

	/**
	 * Retrieve the property determining if a discount is not global.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return bool Whether or not the discount code is global.
	 */
	public function get_is_not_global() {
		if ( null == $this->is_not_global ) {
			$this->is_not_global = get_post_meta( $this->ID, '_edd_discount_is_not_global', true );
		}

		/**
		 * Filters if the discount is global or not.
		 *
		 * @since 2.7
		 *
		 * @param bool $is_not_global Is the discount global or not.
		 * @param int  $ID            Discount ID.
		 */
		return (bool) apply_filters( 'edd_discount_is_not_global', $this->is_not_global, $this->ID );
	}

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
	 * @param array $args Discount details.
	 * @return mixed bool|int false if data isn't passed and class not instantiated for creation, or post ID for the new discount.
	 */
	public function add() {  }
}