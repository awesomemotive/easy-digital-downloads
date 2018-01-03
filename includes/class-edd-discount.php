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
	protected $id = 0;

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
	protected $product_reqs = array();

	/**
	 * Scope of the discount.
	 *
	 * global     - Applies to all products in the cart, save for those explicitly excluded through excluded_products
	 * not_global - Applies only to the products set in product_reqs
	 *
	 * This used to be called "is_not_global" but was changed to "scope" in 3.0.
	 *
	 * @since 3.0
	 * @access protected
	 * @var bool
	 */
	protected $scope = null;

	/**
	 * Excluded Downloads.
	 *
	 * @since 2.7
	 * @access protected
	 * @var array
	 */
	protected $excluded_products = array();

	/**
	 * Product Condition
	 *
	 * @since 2.7
	 * @access protected
	 * @var string
	 */
	protected $product_condition = null;

	/**
	 * Created Date.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $date_created = null;

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
	protected $use_count = null;

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
	protected $min_cart_price = null;

	/**
	 * Is Single Use per customer?
	 *
	 * @since 2.7
	 * @access protected
	 * @var bool
	 */
	protected $once_per_customer = null;

	/**
	 * The Database Abstraction
	 *
	 * @since  3.0
	 */
	protected $db;

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
		$this->db = new EDD_DB_Discounts();

		if ( empty( $_id_or_code_or_name ) ) {
			return false;
		}

		if( is_a( $_id_or_code_or_name, 'EDD_Discount' ) ) {
			$discount = $_id_or_code_or_name;
		} else if ( $by_code ) {
			$discount = $this->find_by_code( $_id_or_code_or_name );
		} else if ( $by_name ) {
			$discount = $this->find_by_name( $_id_or_code_or_name );
		} else {
			$_id_or_code_or_name = intval( $_id_or_code_or_name );
			$discount = $this->db->get( $_id_or_code_or_name );
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

		if( 'discount_id' === $key || 'ID' == $key ) {
			return (int) $this->id;
		} else if ( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
		} else if ( property_exists( $this, $key ) ) {
			return $this->{$key};
		} else {

			switch( $key ) {

				// Account for old property keys from pre 3.0
				case 'post_author' :
					break;
				case 'post_date' :
				case 'post_date_gmt' :

					return $this->date_created;
					break;

				case 'post_content' :
				case 'post_title' :

					return $this->name;
					break;

				case 'post_excerpt' :
				case 'post_status' :
					return $this->status;
					break;

				case 'comment_status' :
				case 'ping_status' :
				case 'post_password' :
				case 'post_name' :
				case 'to_ping' :
				case 'pinged' :
				case 'post_modified' :
				case 'post_modified_gmt' :
				case 'post_content_filtered' :
				case 'post_parent' :
				case 'guid' :
				case 'menu_order' :
				case 'post_mime_type' :
				case 'comment_count' :
				case 'filter' :
				case 'post_type' :

					return '';
					break;

				case 'expiration' :

					return $this->end_date;
					break;

				case 'start' :

					return $this->start_date;
					break;

				case 'min_price' :

					return $this->min_cart_price;
					break;

				case 'use_once' :
				case 'is_single_use' :
				case 'once_per_customer' :

					return $this->get_is_single_use();
					break;

				case 'uses' :

					return $this->use_count;
					break;

				case 'is_not_global' :

					return $this->scope === 'global' ? false : true;
					break;

			}

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
		$old_keys = array(
			'is_single_use',
			'uses',
			'expiration',
			'start',
			'min_price',
			'use_once',
			'is_single_use',
			'is_not_global',
		);

		if ( ! in_array( $key, $keys ) && ! in_array( $key, $old_keys ) ) {
			return false;
		}

		// Dispatch to setter method if value needs to be sanitized
		if ( method_exists( $this, 'set_' . $key ) ) {

			return call_user_func( array( $this, 'set_' . $key ), $key, $value );

		} elseif( in_array( $key, $old_keys ) ) {

			switch( $key ) {

				case 'expiration' :

						$this->end_date = $value;
						break;

					case 'start' :

						$this->start_date = $value;
						break;

					case 'min_price' :

						$this->min_cart_price = $value;
						break;

					case 'use_once' :
					case 'is_single_use' :

						$this->once_per_customer = $value;
						break;

					case 'uses' :

						$this->use_count = $value;
						break;

					case 'is_not_global' :

						$this->scope = $value ? 'not_global' : 'global';
						break;
				}
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

	public function __call( $method, $args ) {
		$property = str_replace( 'setup_', '', $method );
		if( ! method_exists( $this, $method ) && property_exists( $this, $property ) ) {
			return $this->$property;
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

		return $this->db->get_by( 'code', $code );

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

		return $this->db->get_by( 'name', $name );

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

		/**
		 * Fires before the instance of the EDD_Discount object is set up.
		 *
		 * @since 2.7
		 *
		 * @param object EDD_Discount      EDD_Discount instance of the discount object.
		 * @param object WP_Post $discount WP_Post instance of the discount object.
		 */
		do_action( 'edd_pre_setup_discount', $this, $discount );

		$vars = get_object_vars( $discount );

		foreach ( $vars as $key => $value ) {

			switch ( $key ) {

				case 'start_date' :
				case 'end_date' :

					if ( '0000-00-00 00:00:00' == $value ) {
						$this->$key = false;
						break;
					}

				case 'notes' :

					if ( ! empty( $value ) ) {
						$this->$key = $value;
					}
					break;

				case 'id' :

					$this->$key = (int) $value;
					break;

				default:

					if( is_string( $value ) ) {
						@json_decode( $value );
						if( json_last_error() != JSON_ERROR_NONE ) {
							$this->$key = json_decode( $value );
						}
					}

					$this->$key = $value;
					break;

			}

		}

		/**
		 * Some object vars need to be setup manually as the values need to be pulled in from the `edd_discountmeta` table.
		 */
		$this->excluded_products = (array) $this->get_meta( 'excluded_product', false );
		$this->product_reqs = (array) $this->get_meta( 'product_requirement', false );

		/**
		 * Fires after the instance of the EDD_Discount object is set up. Allows extensions to add items to this object via hook.
		 *
		 * @since 2.7
		 *
		 * @param object EDD_Discount      EDD_Discount instance of the discount object.
		 * @param object WP_Post $discount WP_Post instance of the discount object.
		 */
		do_action( 'edd_setup_discount', $this, $discount );

		if ( ! empty( $this->id ) ) {
			return true;
		}

		return false;
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
		return apply_filters( 'edd_get_discount_code', $this->code, $this->id );
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
		return apply_filters( 'edd_get_discount_status', $this->status, $this->id );
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
		return apply_filters( 'edd_get_discount_type', $this->type, $this->id );
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
		return (float) apply_filters( 'edd_get_discount_amount', $this->amount, $this->id );
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

		/**
		 * Filters the download requirements.
		 *
		 * @since 2.7
		 *
		 * @param array $product_reqs IDs of required products.
		 * @param int   $ID           Discount ID.
		 */
		return (array) apply_filters( 'edd_get_discount_product_reqs', $this->product_reqs, $this->id );
	}

	/**
	 * Retrieve the discount scope.
	 *
	 * This used to be called "is_not_global". That filter is still here for backwards compatibility. 
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @return string The scope, i.e. "global".
	 */
	public function get_scope() {
		$legacy_value = apply_filters( 'edd_discount_is_not_global', null, $this->id );

		if ( ! is_null( $legacy_value ) ) {
			$this->scope = $legacy_value ? 'global' : 'not_global';
		}

		return apply_filters( 'edd_get_discount_scope', $this->scope, $this->id );
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
		return apply_filters( 'edd_discount_product_condition', $this->product_condition, $this->id );
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
		/**
		 * Filters the excluded downloads.
		 *
		 * @since 2.7
		 *
		 * @param array $excluded_products IDs of excluded products.
		 * @param int   $ID                Discount ID.
		 */
		return (array) apply_filters( 'edd_get_discount_excluded_products', $this->excluded_products, $this->id );
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
		/**
		 * Filters the start date.
		 *
		 * @since 2.7
		 *
		 * @param string $start Discount start date.
		 * @param int    $ID    Discount ID.
		 */
		return apply_filters( 'edd_get_discount_start', $this->start_date, $this->id );
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
		return apply_filters( 'edd_get_discount_expiration', $this->end_date, $this->id );
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
		return (int) apply_filters( 'edd_get_discount_uses', $this->use_count, $this->id );
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
		return (int) apply_filters( 'edd_get_discount_max_uses', $this->max_uses, $this->id );
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
		return (float) apply_filters( 'edd_get_discount_min_price', $this->min_cart_price, $this->id );
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
		return $this->get_once_per_customer();
	}

	/**
	 * Retrieve the usage limit per limit (if the discount can only be used once per customer).
	 *
	 * @since 3.0
	 * @access public
	 *
	 * @return bool Once use per customer?
	 */
	public function get_once_per_customer() {
		/**
		 * Filters the single use meta value.
		 *
		 * @since 2.7
		 *
		 * @param bool $is_single_use Is the discount only allowed to be used once per customer.
		 * @param int  $ID            Discount ID.
		 */
		return (bool) apply_filters( 'edd_is_discount_single_use', $this->once_per_customer, $this->id );
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
		if ( ! $this->id > 0 ) {
			return false;
		}

		return true;
	}

	/**
	 * Once object variables has been set, an update is needed to persist them to the database.
	 *
	 * This is now simply a wrapper to the add() method which handles creating new discounts and updating existing ones.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @return bool True if the save was successful, false if it failed or wasn't needed.
	 */
	public function save() {
		$args  = get_object_vars( $this );
		$saved = $this->add( $args );

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
		// If no code is provided, return early with false
		if ( empty( $args['code'] ) ) {
			return false;
		}

		if ( ! empty( $this->id ) && $this->exists() ) {

			return $this->update( $args );

		} else {
			$args = $this->convert_legacy_args( $args );

			if ( ! empty( $args['start_date'] ) ) {
				$args['start_date'] = date( 'Y-m-d H:i:s', strtotime( $args['start_date'], current_time( 'timestamp' ) ) );
			}

			if ( ! empty( $args['end_date'] ) ) {
				$args['end_date'] = date( 'Y-m-d H:i:s', strtotime( $args['end_date'], current_time( 'timestamp' ) ) );

				if ( strtotime( $args['end_date'], current_time( 'timestamp' ) )  < current_time( 'timestamp' ) ) {
					$args['status'] = 'expired';
				}
			}

			if ( ! empty( $args['start_date'] ) && ! empty( $args['end_date'] ) ) {
				$start_timestamp = strtotime( $args['start_date'], current_time( 'timestamp' ) );
				$end_timestamp   = strtotime( $args['end_date'], current_time( 'timestamp' ) );

				if ( $start_timestamp > $end_timestamp ) {
					// Set the expiration date to the start date if start is later than expiration
					$args['end_date'] = $args['start_date'];
				}
			}

			// Assume discount status is "active" if it has not been set
			if ( ! isset( $args['status'] ) ) {
				$args['status'] = 'active';
			}

			/**
			 * Add a new discount to the database.
			 */

			/**
			 * Filters the args before being inserted into the database.
			 *
			 * @since 3.0
			 *
			 * @param array $args Discount args.
			 */
			$args = apply_filters( 'edd_insert_discount', $args );

			/**
			 * Filters the args before being inserted into the database (kept for backwards compatibility purposes)
			 *
			 * @since 2.7
			 *
			 * @param array $args Discount args.
			 */
			$args = apply_filters( 'edd_insert_discount_args', $args, $args );

			$args = $this->sanitize_columns( $args );

			/**
			 * Fires before the discount has been added to the database.
			 *
			 * @since 2.7
			 *
			 * @param array $args Discount args.
			 */
			do_action( 'edd_pre_insert_discount', $args );

			foreach ( $args as $key => $value ) {
				$this->$key = $value;
			}

			// The DB class 'add' implies an update if the discount being asked to be created already exists
			if ( $id = $this->db->insert( $args ) ) {
				// We need to update the ID of the instance of the object in order to add meta
				$this->id = $id;

				if ( isset( $args['excluded_products'] ) ) {
					if ( is_array( $args['excluded_products'] ) ) {
						foreach ( $args['excluded_products'] as $product ) {
							$this->add_meta( 'excluded_product', absint( $product ) );
						}
					}
				}

				if ( isset( $args['product_reqs'] ) ) {
					if ( is_array( $args['product_reqs'] ) ) {
						foreach ( $args['product_reqs'] as $product ) {
							$this->add_meta( 'product_requirement', absint( $product ) );
						}
					}
				}

				// We've successfully added/updated the discount, reset the class vars with the new data
				$discount = $this->find_by_code( $args['code'] );

				// Setup the discount data with the values from DB
				$this->setup_discount( $discount );
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
			do_action( 'edd_post_insert_discount', $args, $this->id );

			// Discount code created
			return $id;
		}
	}

	/**
	 * Update an existing discount in the database.
	 *
	 * @since 2.7
	 * @access public
	 *
	 * @param array $args Discount details.
	 * @return bool True if update is successful, false otherwise.
	 */
	public function update( $args ) {

		$ret = false;

		$args = $this->convert_legacy_args( $args );

		/**
		 * Filter the data being updated
		 *
		 * @since 2.7
		 *
		 * @param array $args Discount args.
		 * @param int   $ID   Discount ID.
		 */
		$args = apply_filters( 'edd_update_discount', $args, $this->id );

		$args = $this->sanitize_columns( $args );

		if ( ! empty( $args['start_date'] ) && ! empty( $args['end_date'] ) ) {
			$start_timestamp = strtotime( $args['start_date'], current_time( 'timestamp' ) );
			$end_timestamp   = strtotime( $args['end_date'], current_time( 'timestamp' ) );

			if ( $start_timestamp > $end_timestamp ) {
				// Set the expiration date to the start date if start is later than expiration
				$args['end_date'] = $args['start_date'];
			}
		}

		if ( ! empty( $args['start_date'] ) ) {
			$args['start_date'] = date( 'Y-m-d H:i:s', strtotime( $args['start_date'], current_time( 'timestamp' ) ) );
		}

		if ( ! empty( $args['end_date'] ) ) {
			$args['end_date'] = date( 'Y-m-d H:i:s', strtotime( $args['end_date'], current_time( 'timestamp' ) ) );
		}

		if ( isset( $args['excluded_products'] ) ) {

			if ( is_array( $args['excluded_products'] ) ) {

				// Reset meta
				$this->delete_meta( 'excluded_product' );

				// Now add each newly excluded product
				foreach( $args['excluded_products'] as $product ) {
					$this->add_meta( 'excluded_product', absint( $product ) );
				}
				
			} else {

				$this->delete_meta( 'excluded_product' );

			}

		}

		if ( isset( $args['product_reqs'] ) ) {

			if ( is_array( $args['product_reqs'] ) ) {

				// Reset meta
				$this->delete_meta( 'product_requirement' );

				// Now add each newly required product
				foreach( $args['product_reqs'] as $product ) {
					$this->add_meta( 'product_requirement', absint( $product ) );
				}

			} else {

				$this->delete_meta( 'product_requirement' );

			}

		}

		/**
		 * Fires before the discount has been updated in the database.
		 *
		 * @since 2.7
		 *
		 * @param array $args Discount args.
		 * @param int   $ID   Discount ID.
		 */
		do_action( 'edd_pre_update_discount', $args, $this->id );

		// If we are using the discounts DB
		if ( count( array_intersect_key( $args, $this->db->get_columns() ) ) > 0 ) {
			if ( $this->db->update( $this->id, $args ) ) {
				$discount = $this->db->get( $this->id );
				$this->setup_discount( $discount );

				$ret = true;
			}
		} elseif ( 0 === count( array_intersect_key( $args, $this->db->get_columns() ) ) && count( array_intersect_key( $args, EDD()->discount_meta->get_columns() ) ) > 0 ) {
			$discount = $this->db->get( $this->id );
			$this->setup_discount( $discount );

			$ret = true;
		}

		/**
		 * Fires after the discount has been updated in the database.
		 *
		 * @since 2.7
		 *
		 * @param array $args Discount args.
		 * @param int   $ID   Discount ID.
		 */
		do_action( 'edd_post_update_discount', $args, $this->id );

		return $ret;
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
		do_action( 'edd_pre_update_discount_status', $this->id, $new_status, $this->status );

		$ret = $this->update( array( 'status' => $new_status ) );

		/**
		 * Fires after the status of the discount is updated.
		 *
		 * @since 2.7
		 *
		 * @param int    $ID          Discount ID.
		 * @param string $new_status  New status.
		 * @param string $status Post status.
		 */
		do_action( 'edd_post_update_discount_status', $this->id, $new_status, $this->status );

		return (bool) $ret;
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
		return apply_filters( 'edd_is_discount_started', $return, $this->id );
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

		if ( empty( $this->end_date ) || '0000-00-00 00:00:00' === $this->end_date ) {
			return $return;
		}

		$end_date = strtotime( $this->end_date );

		if ( $end_date < current_time( 'timestamp' ) ) {
			if ( $update ) {
				$this->update_status( 'inactive' );
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
		return apply_filters( 'edd_is_discount_expired', $return, $this->id );
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
		return apply_filters( 'edd_is_discount_maxed_out', $return, $this->id );
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

		$cart_amount = edd_get_cart_discountable_subtotal( $this->id );

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
		return apply_filters( 'edd_is_discount_min_met', $return, $this->id );
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
		return (bool) apply_filters( 'edd_is_discount_single_use', $this->once_per_customer, $this->id );
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
		$product_reqs = $this->get_product_reqs();
		$excluded_ps  = $this->get_excluded_products();
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
			switch ( $this->product_condition ) {
				case 'all' :

					// Default back to true
					$return = true;

					foreach ( $product_reqs as $download_id ) {

						if ( empty( $download_id ) ) {
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

						if ( empty( $download_id ) ) {
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
			if ( count( array_intersect( $cart_ids, $excluded_ps ) ) == count( $cart_ids ) ) {
				$return = false;

				if ( $set_error ) {
					edd_set_error( 'edd-discount-error', __( 'This discount is not valid for the cart contents.', 'easy-digital-downloads' ) );
				}
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
		return (bool) apply_filters( 'edd_is_discount_products_req_met', $return, $this->id, $this->product_condition );
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

					if ( in_array( $payment->status, array( 'abandoned', 'failed', 'pending' ) ) ) {
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
		return apply_filters( 'edd_is_discount_used', $return, $this->id, $user );
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

		if ( edd_get_cart_contents() && $this->id ) {
			if (
				$this->is_active( true, $set_error ) &&
				$this->is_started( $set_error ) &&
				! $this->is_maxed_out( $set_error ) &&
				! $this->is_used( $user, $set_error ) &&
				$this->is_product_requirements_met( $set_error ) &&
				$this->is_min_price_met( $set_error )
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
		return apply_filters( 'edd_is_discount_valid', $return, $this->id, $this->code, $user );
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
			} elseif ( $this->status == 'active' ) {
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
		return apply_filters( 'edd_is_discount_active', $return, $this->id );
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
		if ( $this->get_uses() ) {
			$this->use_count++;
		} else {
			$this->use_count = 1;
		}

		$args = array( 'use_count' => $this->use_count );

		if ( $this->max_uses <= $this->use_count ) {
			$args['status'] = 'inactive';
		}

		$this->update( $args );

		/**
		 * Fires after the usage count has been increased.
		 *
		 * @since 2.7
		 *
		 * @param int    $use_count Discount usage.
		 * @param int    $ID        Discount ID.
		 * @param string $code      Discount code.
		 */
		do_action( 'edd_discount_increase_use_count', $this->use_count, $this->id, $this->code );

		return (int) $this->use_count;
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
		if ( $this->get_uses() ) {
			$this->use_count--;
		}

		if ( $this->use_count < 0 ) {
			$this->use_count = 0;
		}

		$args = array( 'use_count' => $this->use_count );

		if ( $this->max_uses > $this->use_count ) {
			$args['status'] = 'active';
		}

		$this->update( $args );

		/**
		 * Fires after the usage count has been decreased.
		 *
		 * @since 2.7
		 *
		 * @param int    $use_count Discount usage.
		 * @param int    $ID        Discount ID.
		 * @param string $code      Discount code.
		 */
		do_action( 'edd_discount_decrease_use_count', $this->use_count, $this->id, $this->code );

		return (int) $this->use_count;
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
		return esc_url( add_query_arg( array( 'edd-action' => 'edit_discount', 'discount' => $this->id ), admin_url( 'edit.php?post_type=download&page=edd-discounts' ) ) );
	}

	/**
	 * Retrieve discount meta field for a discount.
	 *
	 * @param   string $meta_key      The meta key to retrieve.
	 * @param   bool   $single        Whether to return a single value.
	 * @return  mixed                 Will be an array if $single is false. Will be value of meta data field if $single is true.
	 *
	 * @access  public
	 * @since   3.0
	 */
	public function get_meta( $meta_key = '', $single = true ) {
		return EDD()->discount_meta->get_meta( $this->id, $meta_key, $single );
	}

	/**
	 * Add meta data field to a discount.
	 *
	 * @param   string $meta_key      Metadata name.
	 * @param   mixed  $meta_value    Metadata value.
	 * @param   bool   $unique        Optional, default is false. Whether the same key should not be added.
	 * @return  bool                  False for failure. True for success.
	 *
	 * @access  public
	 * @since   3.0
	 */
	public function add_meta( $meta_key = '', $meta_value, $unique = false ) {
		return EDD()->discount_meta->add_meta( $this->id, $meta_key, $meta_value, $unique );
	}

	/**
	 * Update discount meta field based on discount ID.
	 *
	 * @param   string $meta_key      Metadata key.
	 * @param   mixed  $meta_value    Metadata value.
	 * @param   mixed  $prev_value    Optional. Previous value to check before removing.
	 * @return  bool                  False on failure, true if success.
	 *
	 * @access  public
	 * @since   3.0
	 */
	public function update_meta( $meta_key = '', $meta_value, $prev_value = '' ) {
		return EDD()->discount_meta->update_meta( $this->id, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Remove metadata matching criteria from a discount.
	 *
	 * @param   string $meta_key      Metadata name.
	 * @param   mixed  $meta_value    Optional. Metadata value.
	 * @return  bool                  False for failure. True for success.
	 *
	 * @access  public
	 * @since   3.0
	 */
	public function delete_meta( $meta_key = '', $meta_value = '' ) {
		return EDD()->discount_meta->delete_meta( $this->id, $meta_key, $meta_value );
	}

	/**
	 * Sanitize the data for update/create
	 *
	 * @since  3.0
	 * @param  array $data The data to sanitize
	 * @return array       The sanitized data, based off column defaults
	 */
	private function sanitize_columns( $data ) {

		$columns        = $this->db->get_columns();
		$default_values = $this->db->get_column_defaults();

		foreach ( $columns as $key => $type ) {

			// Only sanitize data that we were provided
			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}

			switch( $type ) {

				case '%s':
					if ( 'email' == $key ) {
						$data[$key] = sanitize_email( $data[$key] );
					} elseif ( 'notes' == $key ) {
						$data[$key] = strip_tags( $data[$key] );
					} else {

						if( is_array( $data[$key] ) ) {

							$data[$key] = json_encode( $data[$key] );

						} else {

							$data[$key] = sanitize_text_field( $data[$key] );
						}

					}
					break;

				case '%d':
					if ( ! is_numeric( $data[$key] ) || (int) $data[$key] !== absint( $data[$key] ) ) {
						$data[$key] = $default_values[$key];
					} else {
						$data[$key] = absint( $data[$key] );
					}
					break;

				case '%f':
					// Convert what was given to a float
					$value = floatval( $data[$key] );

					if ( ! is_float( $value ) ) {
						$data[$key] = $default_values[$key];
					} else {
						$data[$key] = $value;
					}
					break;

				default:
					$data[$key] = sanitize_text_field( $data[$key] );
					break;

			}

		}

		return $data;
	}

	/**
	 * Migrates a legacy discount (pre 3.0) to the new DB structure.
	 *
	 * @access public
	 * @since  3.0
	 *
	 * @param $old_id int The old post ID to migrate to the new schema.
	 * @return bool True if successful, false if already migrated or migration failed.
	 */
	public function migrate( $old_id = 0 ) {
		if ( $this->is_migrated() ) {
			return false;
		}

		$old_discount = get_post( $old_id );

		if ( 'edd_discount' !== $old_discount->post_type ) {
			return false;
		}

		$args = array();
		$meta = get_post_custom( $old_discount->ID );

		foreach ( $meta as $key => $value ) {
			if ( false === strpos( $key, '_edd_discount' ) ) {
				continue;
			}

			$value = maybe_unserialize( $value[0] );

			$args[ str_replace( '_edd_discount_', '', $key ) ] = $value;
		}

		// If the discount name was not stored in post_meta, use value from the WP_Post object
		if ( ! isset( $args['name'] ) ) {
			$args['name'] = $old_discount->post_title;
		}

		$discount = new EDD_Discount();
		$discount->add( $args );
		$discount->add_meta( 'legacy_id', $old_discount->ID );

		do_action( 'edd_migrate_discount_record', $old_discount->ID, $discount );

		return $discount->id;
	}

	/**
	 * Converts pre-3.0 arguments to the 3.0+ version.
	 *
	 * @param $args array Arguments to be converted..
	 * @since 3.0
	 * @return array      The converted arguments.
	 */
	private function convert_legacy_args( $args = array() ) {
		// Loop through arguments provided and adjust old key names for the new schema introduced in 3.0
		$old = array(
			'uses'               => 'use_count',
			'max'                => 'max_uses',
			'start'              => 'start_date',
			'expiration'         => 'end_date',
			'min_price'          => 'min_cart_price',
			'excluded-products'  => 'excluded_products',
			'is_not_global'      => 'scope',
			'is_single_use'      => 'once_per_customer',
		);

		foreach ( $old as $old_key => $new_key ) {
			if ( isset( $args[ $old_key ] ) ) {

				if ( $old_key == 'is_not_global' ) {

					$args[ $new_key ] = $args[ $old_key ] ? 'not_global' : 'global';

				} else {

					$args[ $new_key ] = $args[ $old_key ];

				}

				unset( $args[ $old_key ] );

			}
		}

		return $args;
	}

	/**
	 * Determines if a discount has been migrated from the old schema.
	 *
	 * @since 3.0
	 * @return bool True if it has been migrated, false otherwise.
	 */
	public function is_migrated() {
		if ( $this->get_meta( 'legacy_id', true ) ) {
			return true;
		}

		return false;
	}

}
