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
	 * Product Condition
	 *
	 * @since 2.7
	 * @access protected
	 * @var string
	 */
	protected $product_condition = null;

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

			if ( ! $discount ) {
				return false;
			}

			return $this->setup_discount( $discount );
		} elseif ( $by_name ) {
			$discount = $this->find_by_name( $_id_or_code_or_name );

			if ( ! $discount ) {
				return false;
			}

			return $this->setup_discount( $discount );
		} else {
			$_id_or_code_or_name = absint( $_id_or_code_or_name );

			$discount = WP_Post::get_instance( $_id_or_code_or_name );
			return $this->setup_discount( $discount );
		}

		return false;
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
		} else if ( property_exists( $this, $key ) ) {
			$value = $this->{$key};
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

		return WP_Post::get_instance( $discount->ID );
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
				'name'           => $value,
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

		return WP_Post::get_instance( $discount->ID );
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
		 * Setup discount object vars with WP_Post vars
		 */
		foreach ( $discount as $key => $value ) {
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
	 * Retrieve the uses for the discount code.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return int Uses.
	 */
	public function get_uses() {
		if ( null == $this->uses ) {
			$this->uses = get_post_meta( $this->ID, '_edd_discount_uses', true );
		}

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
	 * Retrieve the product condition.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return string Product condition
	 */
	public function get_product_condition() {
		if ( null == $this->product_condition ) {
			$this->product_condition = get_post_meta( $this->ID, '_edd_discount_product_condition', true );
		}

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
		if ( $this->ID > 0 ) {
			return true;
		} else {
			return false;
		}
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
				update_post_meta( $this->ID, '_edd_discount_' . $key, $value );
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
			update_post_meta( $this->ID, '_edd_discount_' . $key, $value );
		}

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
		if ( $this->exists() ) {
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

			wp_update_post(
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

		if ( $this->start_date ) {
			$start_date = strtotime( $this->start_date );

			if ( $start_date < current_time( 'timestamp' ) ) {
				// Discount has pased the start date
				$return = true;
			} elseif( $set_error ) {
				edd_set_error( 'edd-discount-error', __( 'This discount is not active yet.', 'easy-digital-downloads' ) );
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

		$expiration = strtotime( $this->end_date );
		if ( $expiration < current_time( 'timestamp' ) ) {
			if ( $update ) {
				$this->update_status( 'inactive' );
				update_post_meta( $this->ID, '_edd_discount_status', 'expired' );
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
	public function is_min_amount_met( $set_error = true ) {
		$return = false;

		$cart_amount = edd_get_cart_discountable_subtotal( $this->ID );

		if ( (float) $cart_amount >= (float) $this->min_amount ) {
			$return = true;
		} elseif( $set_error ) {
			edd_set_error( 'edd-discount-error', sprintf( __( 'Minimum order of %s not met.', 'easy-digital-downloads' ), edd_currency_filter( edd_format_amount( $min ) ) ) );
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
	public function is_download_requirements_met( $set_error = true ) {
		$product_reqs = $this->download_requirements;
		$excluded_ps  = $this->excluded_downloads;
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
		$product_reqs = array_values( $product_reqs );

		$excluded_ps  = array_map( 'absint', $excluded_ps );
		asort( $excluded_ps );
		$excluded_ps  = array_values( $excluded_ps );

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
						if ( in_array( strtolower( $this->code ), $discounts ) ) {
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
				$this->is_min_amount_met( $set_error ) &&
				$this->is_download_requirements_met( $set_error )
			) {
				$return = true;
			}
		} elseif( $set_error ) {
			edd_set_error( 'edd-discount-error', __( 'This discount is invalid.', 'easy-digital-downloads' ) );
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
			if ( $this->is_expired( $update ) && defined( 'DOING_AJAX' ) && $set_error ) {
				edd_set_error( 'edd-discount-error', __( 'This discount is expired.', 'easy-digital-downloads' ) );
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

		update_post_meta( $this->ID, '_edd_discount_uses', $this->uses );

		if ( $this->max_uses == $this->uses ) {
			$this->update_status( 'inactive' );
			update_post_meta( $this->ID, '_edd_discount_status', 'inactive' );
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

		update_post_meta( $this->ID, '_edd_discount_uses', $this->uses );

		if ( $this->max_uses > $this->uses ) {
			$this->update_status( 'active' );
			update_post_meta( $this->ID, '_edd_discount_status', 'active' );
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
}