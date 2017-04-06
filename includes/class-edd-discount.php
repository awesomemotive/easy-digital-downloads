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
	protected $product_reqs = null;

	/**
	 * Excluded Downloads.
	 *
	 * @since 2.7
	 * @access protected
	 * @var array
	 */
	protected $excluded_products = null;

	/**
	 * Start Date.
	 *
	 * @since 2.7
	 * @access protected
	 * @var string
	 */
	protected $start = null;

	/**
	 * End Date.
	 *
	 * @since 2.7
	 * @access protected
	 * @var string
	 */
	protected $expiration = null;

	/**
	 * Uses.
	 *
	 * @since 2.7
	 * @access protected
	 * @var int
	 */
	protected $uses = null;

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
	protected $min_price = null;

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
	 * Product Condition
	 *
	 * @since 2.7
	 * @access protected
	 * @var string
	 */
	protected $product_condition = null;

	/**
	 * Array of items that have changed since the last save() was run
	 * This is for internal use, to allow fewer update_payment_meta calls to be run
	 *
	 * @since 2.7
	 * @access private
	 * @var array
	 */
	private $pending;

	/**
	 * Declare the default properties in WP_Post as we can't extend it.
	 *
	 * @since 2.7
	 * @access protected
	 * @var mixed
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
	protected $post_type;

	/**
	 * Constructor.
	 *
	 * @since 2.7
	 * @access protected
	 *
	 * @param mixed int|string $_id_or_code_or_name Discount id/code/name.
	 * @param bool             $by_code             Whether identifier passed was a discount code.
	 * @param bool             $by_name             Whether identifier passed was a discount name.
	 */
	public function __construct( $_id_or_code_or_name = false, $by_code = false, $by_name = false ) {
		if ( empty( $_id_or_code_or_name ) ) {
			return false;
		}

		if ( $by_code ) {
			$discount = $this->find_by_code( $_id_or_code_or_name );
		} elseif ( $by_name ) {
			$discount = $this->find_by_name( $_id_or_code_or_name );
		} else {
			$_id_or_code_or_name = absint( $_id_or_code_or_name );
			$discount = WP_Post::get_instance( $_id_or_code_or_name );
		}

		if ( $discount ) {
			$this->setup_discount( $discount );
		} else {
			return false;
		}
	}

	/**
	 * Magic __get method to dispatch a call to retrieve a protected property.
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
		} else if ( property_exists( $this, $key ) ) {
			return $this->{$key};
		} else {
			return new WP_Error( 'edd-discount-invalid-property', sprintf( __( 'Can\'t get property %s', 'easy-digital-downloads' ), $key ) );
		}
	}

	/**
	 * Magic __set method to dispatch a call to update a protected property.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @see set()
	 *
	 * @param string $key   Property name.
	 * @param mixed  $value Property value.
	 */
	public function __set( $key, $value ) {
		$key = sanitize_key( $key );

		// Only real properties can be saved.
		$keys = array_keys( get_class_vars( get_called_class() ) );

		if ( ! in_array( $key, $keys ) ) {
			return false;
		}

		$this->pending[ $key ] = $value;

		// Dispatch to setter method if value needs to be sanitized
		if ( method_exists( $this, 'set_' . $key ) ) {
			return call_user_func( array( $this, 'set_' . $key ), $key, $value );
		} else {
			$this->{$key} = $value;
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
		if ( property_exists( $this, $key ) ) {
			return false === empty( $this->{$key} );
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
	 * Find a discount in the database with the code supplied.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @param string $code Discount code.
	 * @return object WP_Post instance of the discount.
	 */
	private function find_by_code( $code = '' ) {
		if ( empty( $code ) || ! is_string( $code ) ) {
			return false;
		}

		$discounts = edd_get_discounts(
			array(
				'meta_key'       => '_edd_discount_code',
				'meta_value'     => $code,
				'posts_per_page' => 1,
				'post_status'    => 'any',
				'fields'         => 'ids'
			)
		);

		if ( ! is_array( $discounts ) || array() === $discounts ) {
			return false;
		}

		if ( $discounts ) {
			$discount = $discounts[0];
		}

		return WP_Post::get_instance( $discount );
	}

	/**
	 * Find a discount in the database with the name supplied.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @param string $code Discount name.
	 * @return object WP_Post instance of the discount.
	 */
	private function find_by_name( $name = '' ) {
		if ( empty( $name ) || ! is_string( $name ) ) {
			return false;
		}

		$discounts = edd_get_discounts(
			array(
				'post_type'      => 'edd_discount',
				'name'           => $name,
				'posts_per_page' => 1,
				'post_status'    => 'any',
				'fields'         => 'ids'
			)
		);

		if ( ! is_array( $discounts ) || array() === $discounts ) {
			return false;
		}

		if ( $discounts ) {
			$discount = $discounts[0];
		}

		return WP_Post::get_instance( $discount );
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
		$this->pending = array();

		if ( null == $discount ) {
			return false;
		}

		if ( ! is_object( $discount ) ) {
			return false;
		}

		if ( is_wp_error( $discount ) ) {
			return false;
		}

		if ( ! is_a( $discount, 'WP_Post' ) ) {
			return false;
		}

		if ( 'edd_discount' !== $discount->post_type ) {
			return false;
		}

		/**
		 * Fires before the instance of the EDD_Discount object is set up.
		 *
		 * @since 2.7
		 *
		 * @param object EDD_Discount      EDD_Discount instance of the discount object.
		 * @param object WP_Post $discount WP_Post instance of the discount object.
		 */
		do_action( 'edd_pre_setup_discount', $this, $discount );

		/**
		 * Setup all object variables
		 */
		$this->ID                = absint( $discount->ID );
		$this->name              = $this->setup_name();
		$this->code              = $this->setup_code();
		$this->status            = $this->setup_status();
		$this->type              = $this->setup_type();
		$this->amount            = $this->setup_amount();
		$this->product_reqs      = $this->setup_product_requirements();
		$this->excluded_products = $this->setup_excluded_products();
		$this->start             = $this->setup_start();
		$this->expiration        = $this->setup_expiration();
		$this->uses              = $this->setup_uses();
		$this->max_uses          = $this->setup_max_uses();
		$this->min_price         = $this->setup_min_price();
		$this->is_single_use     = $this->setup_is_single_use();
		$this->is_not_global     = $this->setup_is_not_global();
		$this->product_condition = $this->setup_product_condition();

		/**
		 * Setup discount object vars with WP_Post vars
		 */
		foreach ( get_object_vars( $discount ) as $key => $value ) {
			$this->{$key} = $value;
		}

		/**
		 * Fires after the instance of the EDD_Discount object is set up. Allows extensions to add items to this object via hook.
		 *
		 * @since 2.7
		 *
		 * @param object EDD_Discount      EDD_Discount instance of the discount object.
		 * @param object WP_Post $discount WP_Post instance of the discount object.
		 */
		do_action( 'edd_setup_discount', $this, $discount );

		return true;
	}

	/**
	 * Setup Functions
	 */

	/**
	 * Setup the name of the discount.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @return string Name of the discount.
	 */
	private function setup_name() {
		$title = get_the_title( $this->ID );
		return $title;
	}

	/**
	 * Setup the discount code.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @return string Discount code.
	 */
	private function setup_code() {
		$code = $this->get_meta( 'code', true );
		return $code;
	}

	/**
	 * Setup the discount status.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @return string Discount status.
	 */
	private function setup_status() {
		$status = $this->get_meta( 'status', true );
		return $status;
	}

	/**
	 * Setup the discount type.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @return string Discount type.
	 */
	private function setup_type() {
		$type = $this->get_meta( 'type', true );
		return $type;
	}

	/**
	 * Setup the discount amount.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @return string Discount amount.
	 */
	private function setup_amount() {
		$amount = $this->get_meta( 'amount', true );
		return $amount;
	}

	/**
	 * Setup the product requirements.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @return array Download requirements.
	 */
	private function setup_product_requirements() {
		$requirements = $this->get_meta( 'product_reqs', true );
		return (array) $requirements;
	}

	/**
	 * Setup the excluded products.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @return array Excluded products.
	 */
	private function setup_excluded_products() {
		$excluded = $this->get_meta( 'excluded_products', true );
		return (array) $excluded;
	}

	/**
	 * Setup the start date.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @return string Discount start date.
	 */
	private function setup_start() {
		$start = $this->get_meta( 'start', true );
		return $start;
	}

	/**
	 * Setup the expiration date.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @return array Discount expiration date.
	 */
	private function setup_expiration() {
		$expration = $this->get_meta( 'expiration', true );
		return $expration;
	}

	/**
	 * Setup the uses.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @return int Discount uses.
	 */
	private function setup_uses() {
		$uses = $this->get_meta( 'uses', true );
		return $uses;
	}

	/**
	 * Setup the max uses.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @return int Maximum uses.
	 */
	private function setup_max_uses() {
		$max_uses = $this->get_meta( 'max_uses', true );
		return $max_uses;
	}

	/**
	 * Setup the min price.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @return int Minimum price.
	 */
	private function setup_min_price() {
		$max_uses = $this->get_meta( 'min_price', true );
		return $max_uses;
	}

	/**
	 * Setup if the discount is single use or not.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @return bool Is single use.
	 */
	private function setup_is_single_use() {
		$is_single_use = $this->get_meta( 'is_single_use', true );
		return (bool) $is_single_use;
	}

	/**
	 * Setup if the discount is not global.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @return bool Is not global.
	 */
	private function setup_is_not_global() {
		$is_not_global = $this->get_meta( 'is_not_global', true );
		return (bool) $is_not_global;
	}

	/**
	 * Setup if the discount is not global.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @return bool Is not global.
	 */
	private function setup_product_condition() {
		$condition = $this->get_meta( 'product_condition', true );
		return $condition;
	}

	/**
	 * Helper method to retrieve meta data associated with the discount.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @param string $key    Meta key.
	 * @param bool   $single Return single item or array.
	 */
	public function get_meta( $key = '', $single = true ) {
		$meta = get_post_meta( $this->ID, '_edd_discount_' . $key, $single );
		return $meta;
	}

	/**
	 * Helper method to update post meta associated with the discount.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @param string $key        Meta key.
	 * @param string $value      Meta value.
	 * @param string $prev_value Previous meta value.
	 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
	 */
	public function update_meta( $key = '', $value = '', $prev_value = '' ) {
		if ( empty( $key ) || '' == $key ) {
			return false;
		}

		$key = '_edd_discount_' . $key;

		$value = apply_filters( 'edd_update_discount_meta_' . $key, $value, $this->ID );
		return update_post_meta( $this->ID, $key, $value, $prev_value );
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
	 * @return string Name of the discount.
	 */
	public function get_name() {
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
	public function get_product_reqs() {
		if ( empty( $this->product_reqs ) || ! is_array( $this->product_reqs ) ) {
			$this->product_reqs = array();
		}

		/**
		 * Filters the download requirements.
		 *
		 * @since 2.7
		 *
		 * @param array $product_reqs IDs of required products.
		 * @param int   $ID           Discount ID.
		 */
		return (array) apply_filters( 'edd_get_discount_product_reqs', $this->product_reqs, $this->ID );
	}

	/**
	 * Retrieve the downloads that are excluded from having this discount code applied.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return array IDs of excluded downloads.
	 */
	public function get_excluded_products() {
		if ( empty( $this->excluded_products ) || ! is_array( $this->excluded_products ) ) {
			$this->excluded_products = array();
		}

		/**
		 * Filters the excluded downloads.
		 *
		 * @since 2.7
		 *
		 * @param array $excluded_products IDs of excluded products.
		 * @param int   $ID                Discount ID.
		 */
		return (array) apply_filters( 'edd_get_discount_excluded_products', $this->excluded_products, $this->ID );
	}

	/**
	 * Retrieve the start date.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return string Start date.
	 */
	public function get_start() {
		/**
		 * Filters the start date.
		 *
		 * @since 2.7
		 *
		 * @param string $start Discount start date.
		 * @param int    $ID    Discount ID.
		 */
		return apply_filters( 'edd_get_discount_start', $this->start, $this->ID );
	}

	/**
	 * Retrieve the end date.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return string End date.
	 */
	public function get_expiration() {
		/**
		 * Filters the end date.
		 *
		 * @since 2.7
		 *
		 * @param string $expiration Discount expiration date.
		 * @param int   $ID          Discount ID.
		 */
		return apply_filters( 'edd_get_discount_expiration', $this->expiration, $this->ID );
	}

	/**
	 * Retrieve the uses for the discount code.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return int Uses.
	 */
	public function get_uses() {
		/**
		 * Filters the maximum uses.
		 *
		 * @since 2.7
		 *
		 * @param int $max_uses Maximum uses.
		 * @param int $ID       Discount ID.
		 */
		return (int) apply_filters( 'edd_get_discount_uses', $this->uses, $this->ID );
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
	public function get_min_price() {
		/**
		 * Filters the minimum price.
		 *
		 * @since 2.7
		 *
		 * @param float $min_price Minimum price.
		 * @param int   $ID        Discount ID.
		 */
		return (float) apply_filters( 'edd_get_discount_min_price', $this->min_price, $this->ID );
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
	 * Retrieve the product condition.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return string Product condition
	 */
	public function get_product_condition() {
		/**
		 * Filters the product condition.
		 *
		 * @since 2.7
		 *
		 * @param string $product_condition Product condition.
		 * @param int    $ID                Discount ID.
		 */
		return apply_filters( 'edd_discount_product_condition', $this->product_condition, $this->ID );
	}

	/**
	 * Check if a discount exists.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return bool Discount exists.
	 */
	public function exists() {
		if ( ! $this->ID > 0 ) {
			return false;
		}

		return true;
	}

	/**
	 * Build the base of the discount.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @return int|bool Discount ID or false on error.
	 */
	private function insert_discount() {
		$discount_data = array(
			'code'              => isset( $this->code )              ? $this->code              : '',
			'name'              => isset( $this->name )              ? $this->name              : '',
			'status'            => isset( $this->status )            ? $this->status            : 'active',
			'uses'              => isset( $this->uses )              ? $this->uses              : '',
			'max_uses'          => isset( $this->max_uses )          ? $this->max_uses          : '',
			'amount'            => isset( $this->amount )            ? $this->amount            : '',
			'start'             => isset( $this->start )             ? $this->start             : '',
			'expiration'        => isset( $this->expiration )        ? $this->expiration        : '',
			'type'              => isset( $this->type )              ? $this->type              : '',
			'min_price'         => isset( $this->min_price )         ? $this->min_price        : '',
			'product_reqs'      => isset( $this->product_reqs )      ? $this->product_reqs      : array(),
			'product_condition' => isset( $this->product_condition ) ? $this->product_condition : '',
			'excluded_products' => isset( $this->excluded_products ) ? $this->excluded_products : array(),
			'is_not_global'     => isset( $this->is_not_global )     ? $this->is_not_global     : false,
			'is_single_use'     => isset( $this->is_single_use )     ? $this->is_single_use     : false,
		);

		$start_timestamp = strtotime( $discount_data['start'] );

		if ( ! empty( $discount_data['start'] ) ) {
			$discount_data['start'] = date( 'm/d/Y H:i:s', $start_timestamp );
		}

		if ( ! empty( $discount_data['expiration'] ) ) {
			$discount_data['expiration'] = date( 'm/d/Y H:i:s', strtotime( date( 'm/d/Y', strtotime( $discount_data['expiration'] ) ) . ' 23:59:59' ) );
			$end_timestamp = strtotime( $discount_data['expiration'] );

			if ( ! empty( $discount_data['start'] ) && $start_timestamp > $end_timestamp ) {
				// Set the expiration date to the start date if start is later than expiration
				$discount_data['expiration'] = $discount_data['start'];
			}
		}

		if ( ! empty( $discount_data['excluded_products'] ) ) {
			foreach ( $discount_data['excluded_products'] as $key => $product ) {
				if ( 0 === intval( $product ) ) {
					unset( $discount_data['excluded_products'][ $key ] );
				}
			}
		}

		$args = apply_filters( 'edd_insert_discount_args', array(
			'post_title'    => $this->name,
			'post_status'   => $discount_data['status'],
			'post_type'     => 'edd_discount',
			'post_date'     => ! empty( $this->date ) ? $this->date : null,
			'post_date_gmt' => ! empty( $this->date ) ? get_gmt_from_date( $this->date ) : null
		), $discount_data );

		// Create a blank edd_discount post
		$discount_id = wp_insert_post( $args );

		if ( ! empty( $discount_id ) ) {

			$this->ID  = $discount_id;

			foreach ( $discount_data as $key => $value ) {

				if( ! empty( $value ) ) {

					$this->update_meta( $key, $value );

				}

			}

		}

		return $this->ID;
	}

	/**
	 * Once object variables has been set, an update is needed to persist them to the database.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return bool True if the save was successful, false if it failed or wasn't needed.
	 */
	public function save() {
		$saved = false;

		if ( empty( $this->ID ) ) {
			$discount_id = $this->insert_discount();

			if ( false === $discount_id ) {
				$saved = false;
			} else {
				$this->ID = $discount_id;
			}
		}

		/**
		 * Save all the object variables that have been updated to the databse.
		 */
		if ( ! empty( $this->pending ) ) {
			foreach ( $this->pending as $key => $value ) {
				$this->update_meta( $key, $value );

				if ( 'status' == $key ) {
					$this->update_status( $value );
				}

				if ( 'name' == $key ) {
					wp_update_post( array(
						'ID'         => $this->ID,
						'post_title' => $value
					) );
				}
			}

			$saved = true;
		}

		if ( true == $saved ) {
			$this->setup_discount( WP_Post::get_instance( $this->ID ) );

			/**
			 * Fires after each meta update allowing developers to hook their own items saved in $pending.
			 *
			 * @since 2.7
			 *
			 * @param object       Instance of EDD_Discount object.
			 * @param string $key  Meta key.
			 */
			do_action( 'edd_discount_save', $this->ID, $this );
		}

		return $saved;
	}

	/**
	 * Create a new discount. If the discount already exists in the database, update it.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @param array $args Discount details.
	 * @return mixed bool|int false if data isn't passed and class not instantiated for creation, or post ID for the new discount.
	 */
	public function add( $args ) {
		$meta = $this->build_meta( $args );

		if ( ! empty( $this->ID ) && $this->exists() ) {
			return $this->update( $args );
		} else {
			/**
			 * Add a new discount to the database.
			 */

			/**
			 * Filters the metadata before being inserted into the database.
			 *
			 * @since 2.7
			 *
			 * @param array $meta Discount meta.
			 * @param int   $ID   Discount ID.
			 */
			$meta = apply_filters( 'edd_insert_discount', $meta );

			/**
			 * Fires before the discount has been added to the database.
			 *
			 * @since 2.7
			 *
			 * @param array $meta Discount meta.
			 */
			do_action( 'edd_pre_insert_discount', $meta );

			$this->ID = wp_insert_post( array(
				'post_type'   => 'edd_discount',
				'post_title'  => $meta['name'],
				'post_status' => 'active'
			) );

			foreach ( $meta as $key => $value ) {
				$this->update_meta( $key, $value );
			}

			/**
			 * Fires after the discount code is inserted.
			 *
			 * @param array $meta {
			 *     The discount details.
			 *
			 *     @type string $code              The discount code.
			 *     @type string $name              The name of the discount.
			 *     @type string $status            The discount status. Defaults to active.
			 *     @type int    $uses              The current number of uses.
			 *     @type int    $max_uses          The max number of uses.
			 *     @type string $start             The start date.
			 *     @type int    $min_price         The minimum price required to use the discount code.
			 *     @type array  $product_reqs      The product IDs required to use the discount code.
			 *     @type string $product_condition The conditions in which a product(s) must meet to use the discount code.
			 *     @type array  $excluded_products Product IDs excluded from this discount code.
			 *     @type bool   $is_not_global     If the discount code is not globally applied to all products. Defaults to false.
			 *     @type bool   $is_single_use     If the code cannot be used more than once per customer. Defaults to false.
			 * }
			 * @param int $ID The ID of the discount that was inserted.
			 */
			do_action( 'edd_post_insert_discount', $meta, $this->ID );

			$this->setup_discount( WP_Post::get_instance( $this->ID ) );

			// Discount code created
			return $this->ID;
		}
	}

	/**
	 * Update an existing discount in the database.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @param array $args Discount details.
	 * @return mixed bool|int false if data isn't passed and class not instantiated for creation, or post ID for the new discount.
	 */
	public function update( $args ) {
		$meta = $this->build_meta( $args );

		/**
		 * Filter the data being updated
		 *
		 * @since 2.7
		 *
		 * @param array $meta Discount meta.
		 * @param int   $ID   Discount ID.
		 */
		$meta = apply_filters( 'edd_update_discount', $meta, $this->ID );

		/**
		 * Fires before the discount has been updated in the database.
		 *
		 * @since 2.7
		 *
		 * @param array $meta Discount meta.
		 * @param int   $ID   Discount ID.
		 */
		do_action( 'edd_pre_update_discount', $meta, $this->ID );

		wp_update_post( array(
			'ID'          => $this->ID,
			'post_title'  => $meta['name'],
			'post_status' => $meta['status']
		) );

		foreach ( $meta as $key => $value ) {
			$this->update_meta( $key, $value );
		}

		$this->setup_discount( WP_Post::get_instance( $this->ID ) );

		/**
		 * Fires after the discount has been updated in the database.
		 *
		 * @since 2.7
		 *
		 * @param array $meta Discount meta.
		 * @param int   $ID   Discount ID.
		 */
		do_action( 'edd_post_update_discount', $meta, $this->ID );

		return $this->ID;
	}

	/**
	 * Build Discount Meta Array.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @param array $args Discount meta.
	 * @return array Filtered and sanitized discount args.
	 */
	private function build_meta( $args = array() ) {
		if ( ! is_array( $args ) || array() === $args ) {
			return false;
		}

		$meta = array(
			'code'              => isset( $args['code'] )             ? $args['code']              : '',
			'name'              => isset( $args['name'] )             ? $args['name']              : '',
			'status'            => isset( $args['status'] )           ? $args['status']            : 'active',
			'uses'              => isset( $args['uses'] )             ? $args['uses']              : '',
			'max_uses'          => isset( $args['max'] )              ? $args['max']               : '',
			'amount'            => isset( $args['amount'] )           ? $args['amount']            : '',
			'start'             => isset( $args['start'] )            ? $args['start']             : '',
			'expiration'        => isset( $args['expiration'] )       ? $args['expiration']        : '',
			'type'              => isset( $args['type'] )             ? $args['type']              : '',
			'min_price'         => isset( $args['min_price'] )        ? $args['min_price']         : '',
			'product_reqs'      => isset( $args['products'] )         ? $args['products']          : array(),
			'product_condition' => isset( $args['product_condition'] )? $args['product_condition'] : '',
			'excluded_products' => isset( $args['excluded-products'] )? $args['excluded-products'] : array(),
			'is_not_global'     => isset( $args['not_global'] )       ? $args['not_global']        : false,
			'is_single_use'     => isset( $args['use_once'] )         ? $args['use_once']          : false,
		);

		$start_timestamp = strtotime( $meta['start'] );

		if ( ! empty( $meta['start'] ) ) {
			$meta['start']      = date( 'm/d/Y H:i:s', $start_timestamp );
		}

		if ( ! empty( $meta['expiration'] ) ) {
			$meta['expiration'] = date( 'm/d/Y H:i:s', strtotime( date( 'm/d/Y', strtotime( $meta['expiration'] ) ) . ' 23:59:59' ) );
			$end_timestamp      = strtotime( $meta['expiration'] );

			if ( ! empty( $meta['start'] ) && $start_timestamp > $end_timestamp ) {
				// Set the expiration date to the start date if start is later than expiration
				$meta['expiration'] = $meta['start'];
			}
		}

		if ( ! empty( $meta['excluded_products'] ) ) {
			foreach ( $meta['excluded_products'] as $key => $product ) {
				if ( 0 === intval( $product ) ) {
					unset( $meta['excluded_products'][ $key ] );
				}
			}
		}

		return $meta;
	}

	/**
	 * Update the status of the discount.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @param string $new_status New status (default: active)
	 * @return bool If the status been updated or not.
	 */
	public function update_status( $new_status = 'active' ) {
		/**
		 * Fires before the status of the discount is updated.
		 *
		 * @since 2.7
		 *
		 * @param int    $ID          Discount ID.
		 * @param string $new_status  New status.
		 * @param string $post_status Post status.
		 */
		do_action( 'edd_pre_update_discount_status', $this->ID, $new_status, $this->post_status );

		$id = wp_update_post(
			array(
				'ID'          => $this->ID,
				'post_status' => $new_status
			)
		);

		/**
		 * Fires after the status of the discount is updated.
		 *
		 * @since 2.7
		 *
		 * @param int    $ID          Discount ID.
		 * @param string $new_status  New status.
		 * @param string $post_status Post status.
		 */
		do_action( 'edd_post_update_discount_status', $this->ID, $new_status, $this->post_status );

		if ( $id == $this->ID ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the discount has started.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @param bool $set_error Whether an error message be set in session.
	 * @return bool Is discount started?
	 */
	public function is_started( $set_error = true ) {
		$return = false;

		if ( $this->start ) {
			$start_date = strtotime( $this->start );

			if ( $start_date < current_time( 'timestamp' ) ) {
				// Discount has pased the start date
				$return = true;
			} elseif( $set_error ) {
				edd_set_error( 'edd-discount-error', _x( 'This discount is invalid.', 'error shown when attempting to use a discount before its start date', 'easy-digital-downloads' ) );
			}
		} else {
			// No start date for this discount, so has to be true
			$return = true;
		}

		/**
		 * Filters if the discount has started or not.
		 *
		 * @since 2.7
		 *
		 * @param bool $return Has the discount started or not.
		 * @param int  $ID     Discount ID.
		 */
		return apply_filters( 'edd_is_discount_started', $return, $this->ID );
	}

	/**
	 * Check if the discount has expired.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @param bool $update Update the discount to expired if an one is found but has an active status
	 * @return bool Has the discount expired?
	 */
	public function is_expired( $update = true ) {
		$return = false;

		if ( empty( $this->expiration ) ) {
			return $return;
		}

		$expiration = strtotime( $this->expiration );

		if ( $expiration < current_time( 'timestamp' ) ) {
			if ( $update ) {
				$this->update_status( 'inactive' );
				$this->update_meta( 'status', 'expired' );
			}
			$return = true;
		}

		/**
		 * Filters if the discount has expired or not.
		 *
		 * @since 2.7
		 *
		 * @param bool $return Has the discount expired or not.
		 * @param int  $ID     Discount ID.
		 */
		return apply_filters( 'edd_is_discount_expired', $return, $this->ID );
	}

	/**
	 * Check if the discount has maxed out.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @param bool $set_error Whether an error message be set in session.
	 * @return bool Is discount maxed out?
	 */
	public function is_maxed_out( $set_error = true ) {
		$return = false;

		if ( $this->uses >= $this->max_uses && ! empty( $this->max_uses ) ) {
			if ( $set_error ) {
				edd_set_error( 'edd-discount-error', __( 'This discount has reached its maximum usage.', 'easy-digital-downloads' ) );
			}

			$return = true;
		}

		/**
		 * Filters if the discount is maxed out or not.
		 *
		 * @since 2.7
		 *
		 * @param bool $return Is the discount maxed out or not.
		 * @param int  $ID     Discount ID.
		 */
		return apply_filters( 'edd_is_discount_maxed_out', $return, $this->ID );
	}

	/**
	 * Check if the minimum cart amount is satisfied for the discount to hold.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @param bool $set_error Whether an error message be set in session.
	 * @return bool Is the minimum cart amount met?
	 */
	public function is_min_price_met( $set_error = true ) {
		$return = false;

		$cart_amount = edd_get_cart_discountable_subtotal( $this->ID );

		if ( (float) $cart_amount >= (float) $this->min_price ) {
			$return = true;
		} elseif( $set_error ) {
			edd_set_error( 'edd-discount-error', sprintf( __( 'Minimum order of %s not met.', 'easy-digital-downloads' ), edd_currency_filter( edd_format_amount( $this->min_price ) ) ) );
		}

		/**
		 * Filters if the minimum cart amount has been met to satisify the discount.
		 *
		 * @since 2.7
		 *
		 * @param bool $return Is the minimum cart amount met or not.
		 * @param int  $ID     Discount ID.
		 */
		return apply_filters( 'edd_is_discount_min_met', $return, $this->ID );
	}

	/**
	 * Is the discount single use or not?
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return bool Is the discount single use or not?
	 */
	public function is_single_use() {
		/**
		 * Filters if the discount is single use or not.
		 *
		 * @since 2.7
		 *
		 * @param bool $single_use Is the discount is single use or not.
		 * @param int  $ID         Discount ID.
		 */
		return (bool) apply_filters( 'edd_is_discount_single_use', $this->is_single_use, $this->ID );
	}

	/**
	 * Are the product requirements met for the discount to hold.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @param bool $set_error Whether an error message be set in session.
	 * @return bool Are required products in the cart?
	 */
	public function is_product_requirements_met( $set_error = true ) {
		$product_reqs = $this->product_reqs;
		$excluded_ps  = $this->excluded_products;
		$cart_items   = edd_get_cart_contents();
		$cart_ids     = $cart_items ? wp_list_pluck( $cart_items, 'id' ) : null;
		$return       = false;

		if ( empty( $product_reqs ) && empty( $excluded_ps ) ) {
			$return = true;
		}

		/**
		 * Normalize our data for product requirements, exclusions and cart data.
		 */

		// First absint the items, then sort, and reset the array keys
		$product_reqs = array_map( 'absint', $product_reqs );
		asort( $product_reqs );
		$product_reqs = array_filter( array_values( $product_reqs ) );

		$excluded_ps  = array_map( 'absint', $excluded_ps );
		asort( $excluded_ps );
		$excluded_ps  = array_filter( array_values( $excluded_ps ) );

		$cart_ids     = array_map( 'absint', $cart_ids );
		asort( $cart_ids );
		$cart_ids     = array_values( $cart_ids );

		// Ensure we have requirements before proceeding
		if ( ! $return && ! empty( $product_reqs ) ) {

			switch( $this->product_condition ) {

				case 'all' :

					// Default back to true
					$return = true;

					foreach ( $product_reqs as $download_id ) {

						if( empty( $download_id ) ) {
							continue;
						}

						if ( ! edd_item_in_cart( $download_id ) ) {

							if ( $set_error ) {
								edd_set_error( 'edd-discount-error', __( 'The product requirements for this discount are not met.', 'easy-digital-downloads' ) );
							}

							$return = false;

							break;

						}

					}

					break;

				default :

					foreach ( $product_reqs as $download_id ) {

						if( empty( $download_id ) ) {
							continue;
						}

						if ( edd_item_in_cart( $download_id ) ) {
							$return = true;
							break;
						}

					}

					if ( ! $return && $set_error ) {
						edd_set_error( 'edd-discount-error', __( 'The product requirements for this discount are not met.', 'easy-digital-downloads' ) );
					}

					break;

			}

		} else {

			$return = true;

		}

		if ( ! empty( $excluded_ps ) ) {
			if ( $cart_ids == $excluded_ps ) {
				if ( $set_error ) {
					edd_set_error( 'edd-discount-error', __( 'This discount is not valid for the cart contents.', 'easy-digital-downloads' ) );
				}

				$return = false;
			}
		}

		/**
		 * Filters whether the product requirements are met for the discount to hold.
		 *
		 * @since 2.7
		 *
		 * @param bool   $return            Are the product requirements met or not.
		 * @param int    $ID                Discount ID.
		 * @param string $product_condition Product condition.
		 */
		return (bool) apply_filters( 'edd_is_discount_products_req_met', $return, $this->ID, $this->product_condition );
	}

	/**
	 * Has the discount code been used.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @param string $user User info.
	 * @param bool $set_error Whether an error message be set in session.
	 */
	public function is_used( $user = '', $set_error = true ) {
		$return = false;

		if ( $this->is_single_use ) {
			$payments = array();

			if ( EDD()->customers->installed() ) {
				$by_user_id = is_email( $user ) ? false : true;
				$customer = new EDD_Customer( $user, $by_user_id );

				$payments = explode( ',', $customer->payment_ids );
			} else {
				$user_found = false;

				if ( is_email( $user ) ) {
					$user_found = true; // All we need is the email
					$key        = '_edd_payment_user_email';
					$value      = $user;
				} else {
					$user_data = get_user_by( 'login', $user );

					if ( $user_data ) {
						$user_found = true;
						$key        = '_edd_payment_user_id';
						$value      = $user_data->ID;
					}
				}

				if ( $user_found ) {
					$query_args = array(
						'post_type'       => 'edd_payment',
						'meta_query'      => array(
							array(
								'key'     => $key,
								'value'   => $value,
								'compare' => '='
							)
						),
						'fields'          => 'ids'
					);

					$payments = get_posts( $query_args ); // Get all payments with matching email
				}
			}

			if ( $payments ) {
				foreach ( $payments as $payment ) {
					$payment = new EDD_Payment( $payment );

					if ( empty( $payment->discounts ) ) {
						continue;
					}

					if ( in_array( $payment->status, array( 'abandoned', 'failed' ) ) ) {
						continue;
					}

					$discounts = explode( ',', $payment->discounts );

					if ( is_array( $discounts ) ) {
						$discounts = array_map( 'strtoupper', $discounts );
						$key       = array_search( strtoupper( $this->code ), $discounts );
						if ( false !== $key ) {
							if ( $set_error ) {
								edd_set_error( 'edd-discount-error', __( 'This discount has already been redeemed.', 'easy-digital-downloads' ) );
							}

							$return = true;
							break;
						}
					}
				}
			}
		}

		/**
		 * Filters if the discount is used or not.
		 *
		 * @since 2.7
		 *
		 * @param bool   $return If the discount is used or not.
		 * @param int    $ID     Discount ID.
		 * @param string $user   User info.
		 */
		return apply_filters( 'edd_is_discount_used', $return, $this->ID, $user );
	}

	/**
	 * Checks whether a discount holds at the time of purchase.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @param string $user      User info.
	 * @param bool   $set_error Whether an error message be set in session.
	 * @return bool Is the discount valid or not?
	 */
	public function is_valid( $user = '', $set_error = true ) {
		$return = false;
		$user = trim( $user );

		if ( edd_get_cart_contents() && $this->ID ) {
			if (
				$this->is_active( true, $set_error ) &&
				$this->is_started( $set_error ) &&
				! $this->is_maxed_out( $set_error ) &&
				! $this->is_used( $user, $set_error ) &&
				$this->is_min_price_met( $set_error ) &&
				$this->is_product_requirements_met( $set_error )
			) {
				$return = true;
			}
		} elseif( $set_error ) {
			edd_set_error( 'edd-discount-error', _x( 'This discount is invalid.', 'error for when a discount is invalid based on its configuration' , 'easy-digital-downloads' ) );
		}

		/**
		 * Filters whether the discount is valid or not.
		 *
		 * @since 2.7
		 *
		 * @param bool   $return If the discount is used or not.
		 * @param int    $ID     Discount ID.
		 * @param string $code   Discount code.
		 * @param string $user   User info.
		 */
		return apply_filters( 'edd_is_discount_valid', $return, $this->ID, $this->code, $user );
	}

	/**
	 * Checks if a discount code is active.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @param bool $update    Update the discount to expired if an one is found but has an active status.
	 * @param bool $set_error Whether an error message be set in session.
	 * @return bool If the discount is active or not.
	 */
	public function is_active( $update = true, $set_error = true ) {
		$return = false;

		if ( $this->exists() ) {
			if ( $this->is_expired( $update ) ) {
				if ( defined( 'DOING_AJAX' ) && $set_error ) {
					edd_set_error( 'edd-discount-error', __( 'This discount is expired.', 'easy-digital-downloads' ) );
				}
			} elseif ( $this->post_status == 'active' ) {
				$return = true;
			} elseif( defined( 'DOING_AJAX' ) && $set_error ) {
				edd_set_error( 'edd-discount-error', __( 'This discount is not active.', 'easy-digital-downloads' ) );
			}
		}

		/**
		 * Filters if the discount is active or not.
		 *
		 * @since 2.7
		 *
		 * @param bool $return Is the discount active or not.
		 * @param int  $ID     Discount ID.
		 */
		return apply_filters( 'edd_is_discount_active', $return, $this->ID );
	}

	/**
	 * Get Discounted Amount.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @param string|int $base_price Price before discount.
	 * @return float $discounted_price Amount after discount.
	 */
	public function get_discounted_amount( $base_price ) {
		// Start off setting the amount as the base price.
		$amount = $base_price;

		if ( 'flat' == $this->type ) {
			$amount = $base_price - $this->amount;

			if ( $amount < 0 ) {
				$amount = 0;
			}
		} else {
			// Percentage discount
			$amount = $base_price - ( $base_price * ( $this->amount / 100 ) );
		}

		/**
		 * Filter the discounted amount calculated.
		 *
		 * @since 2.7
		 * @access public
		 *
		 * @param float $amount Calculated discounted amount.
		 */
		return apply_filters( 'edd_discounted_amount', $amount );
	}

	/**
	 * Increment the usage of the discount.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return int New discount usage.
	 */
	public function increase_usage() {
		if ( $this->uses ) {
			$this->uses++;
		} else {
			$this->uses = 1;
		}

		$this->update_meta( 'uses', $this->uses );

		if ( $this->max_uses == $this->uses ) {
			$this->update_status( 'inactive' );
			$this->update_meta( 'status', 'inactive' );
		}

		/**
		 * Fires after the usage count has been increased.
		 *
		 * @since 2.7
		 *
		 * @param int    $uses Discount usage.
		 * @param int    $ID   Discount ID.
		 * @param string $code Discount code.
		 */
		do_action( 'edd_discount_increase_use_count', $this->uses, $this->ID, $this->code );

		return $this->uses;
	}

	/**
	 * Decrement the usage of the discount.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return int New discount usage.
	 */
	public function decrease_usage() {
		if ( $this->uses ) {
			$this->uses--;
		}

		if ( $this->uses < 0 ) {
			$uses = 0;
		}

		$this->update_meta( 'uses', $this->uses );

		if ( $this->max_uses > $this->uses ) {
			$this->update_status( 'active' );
			$this->update_meta( 'status', 'active' );
		}

		/**
		 * Fires after the usage count has been decreased.
		 *
		 * @since 2.7
		 *
		 * @param int    $uses Discount usage.
		 * @param int    $ID   Discount ID.
		 * @param string $code Discount code.
		 */
		do_action( 'edd_discount_decrease_use_count', $this->uses, $this->ID, $this->code );

		return $this->uses;
	}

	/**
	 * Edit Discount Link.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return string Link to the `Edit Discount` page.
	 */
	public function edit_url() {
		return esc_url( add_query_arg( array( 'edd-action' => 'edit_discount', 'discount' => $this->ID ), admin_url( 'edit.php?post_type=download&page=edd-discounts' ) ) );
	}
}
