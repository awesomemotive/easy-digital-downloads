<?php
/**
 * Discount Object
 *
 * @package     EDD
 * @subpackage  Discounts
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.7
 */

use EDD\Database\Rows\Adjustment;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Discount Class
 *
 * @since 2.7
 * @since 3.0 Extends EDD\Database\Rows\Adjustment instead of EDD_DB_Discount
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $status
 * @property string $amount_type
 * @property float $amount
 * @property array $product_reqs
 * @property string $scope
 * @property array $excluded_products
 * @property string $product_condition
 * @property string $date_created
 * @property string $date_modified
 * @property string $start_date
 * @property string $end_date
 * @property int $use_count
 * @property int $max_uses
 * @property float $min_charge_amount
 * @property bool $once_per_customer
 */
class EDD_Discount extends Adjustment {

	/**
	 * Flat discount.
	 *
	 * @since 3.0
	 * @var string
	 */
	const FLAT = 'flat';

	/**
	 * Percent discount.
	 *
	 * @since 3.0
	 * @var string
	 */
	const PERCENT = 'percent';

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
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $amount_type = null;

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
	 * @var string
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
	 * Modified Date.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $date_modified = null;

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
	protected $min_charge_amount = null;

	/**
	 * Is Single Use per customer?
	 *
	 * @since 2.7
	 * @access protected
	 * @var bool
	 */
	protected $once_per_customer = null;

	/**
	 * Categories.
	 *
	 * @since 3.2.0
	 * @access protected
	 * @var array
	 */
	protected $categories;

	/**
	 * Term Condition.
	 *
	 * @since 3.2.0
	 * @access protected
	 * @var string
	 */
	protected $term_condition;

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

		// Bail if no id or code
		if ( empty( $_id_or_code_or_name ) ) {
			return false;
		}

		// Already an object
		if ( is_object( $_id_or_code_or_name ) ) {
			$discount = $_id_or_code_or_name;

		// Code
		} elseif ( $by_code ) {
			$discount = $this->find_by_code( $_id_or_code_or_name );

		// Name
		} elseif ( $by_name ) {
			$discount = $this->find_by_name( $_id_or_code_or_name );

		// Default to ID
		} else {
			$discount = edd_get_discount( absint( $_id_or_code_or_name ) );
		}

		// Setup or bail
		if ( ! empty( $discount ) ) {
			$this->setup_discount( $discount );
		} else {
			return false;
		}
	}

	/**
	 * Magic __get method to dispatch a call to retrieve a protected property.
	 *
	 * @since 2.7
	 *
	 * @param mixed $key
	 * @return mixed
	 */
	public function __get( $key = '' ) {
		$key = sanitize_key( $key );

		// Back compat for ID
		if ( 'discount_id' === $key || 'ID' === $key ) {
			return (int) $this->id;

		// Method
		} elseif ( method_exists( $this, "get_{$key}" ) ) {
			return call_user_func( array( $this, "get_{$key}" ) );

		// Property
		} elseif ( property_exists( $this, $key ) ) {
			return $this->{$key};

		// Other...
		} else {

			// Account for old property keys from pre 3.0
			switch ( $key ) {
				case 'post_author':
					break;

				case 'post_date':
				case 'post_date_gmt':
					return $this->date_created;

				case 'post_modified':
				case 'post_modified_gmt':
					return $this->date_modified;

				case 'post_content':
				case 'post_title':
					return $this->name;

				case 'post_excerpt':
				case 'post_status':
					return $this->status;

				case 'comment_status':
				case 'ping_status':
				case 'post_password':
				case 'post_name':
				case 'to_ping':
				case 'pinged':
				case 'post_modified':
				case 'post_modified_gmt':
				case 'post_content_filtered':
				case 'post_parent':
				case 'guid':
				case 'menu_order':
				case 'post_mime_type':
				case 'comment_count':
				case 'filter':
					return '';

				case 'post_type':
					return 'edd_discount';

				case 'expiration':
					return $this->get_expiration();

				case 'start':
					return $this->start_date;

				case 'min_price':
					return $this->min_charge_amount;

				case 'use_once':
				case 'is_single_use':
				case 'once_per_customer':
					return $this->get_is_single_use();

				case 'uses':
					return $this->use_count;

				case 'not_global':
				case 'is_not_global':
					return 'global' === $this->scope ? false : true;
			}

			return new WP_Error( 'edd-discount-invalid-property', sprintf( __( 'Can\'t get property %s', 'easy-digital-downloads' ), $key ) );
		}
	}

	/**
	 * Magic __set method to dispatch a call to update a protected property.
	 *
	 * @since 2.7
	 *
	 * @see set()
	 *
	 * @param string $key   Property name.
	 * @param mixed  $value Property value.
	 *
	 * @return mixed Value of setter being dispatched to.
	 */
	public function __set( $key, $value ) {
		$key = sanitize_key( $key );

		// Only real properties can be saved.
		$keys     = array_keys( get_class_vars( get_called_class() ) );
		$old_keys = array(
			'is_single_use',
			'uses',
			'expiration',
			'start',
			'min_price',
			'use_once',
			'is_not_global',
		);

		if ( ! in_array( $key, $keys, true ) && ! in_array( $key, $old_keys, true ) ) {
			return false;
		}

		// Dispatch to setter method if value needs to be sanitized
		if ( method_exists( $this, 'set_' . $key ) ) {
			return call_user_func( array( $this, 'set_' . $key ), $key, $value );
		} elseif ( in_array( $key, $old_keys, true ) ) {
			switch ( $key ) {
				case 'expiration':
					$this->end_date = $value;
					break;
				case 'start':
					$this->start_date = $value;
					break;
				case 'min_price':
					$this->min_charge_amount = $value;
					break;
				case 'use_once':
				case 'is_single_use':
					$this->once_per_customer = $value;
					break;
				case 'uses':
					$this->use_count = $value;
					break;
				case 'not_global':
				case 'is_not_global':
					$this->scope = $value ? 'not_global' : 'global';
					break;
			}
		} else {
			$this->{$key} = $value;
		}
	}

	/**
	 * Handle method dispatch dynamically.
	 *
	 * @param string $method Method name.
	 * @param array  $args   Arguments to be passed to method.
	 *
	 * @return mixed
	 */
	public function __call( $method, $args ) {
		$property = strtolower( str_replace( array( 'setup_', 'get_' ), '', $method ) );
		if ( ! method_exists( $this, $method ) && property_exists( $this, $property ) ) {
			return $this->{$property};
		}
	}

	/**
	 * Magic __toString method.
	 *
	 * @since 3.0
	 */
	public function __toString() {
		return $this->code;
	}

	/**
	 * Converts the instance of the EDD_Discount object into an array for special cases.
	 *
	 * @since 2.7
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
		return edd_get_discount_by( 'code', $code );
	}

	/**
	 * Find a discount in the database with the name supplied.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @param string $name Discount name.
	 * @return object WP_Post instance of the discount.
	 */
	private function find_by_name( $name = '' ) {
		return edd_get_discount_by( 'name', $name );
	}

	/**
	 * Setup object vars with discount WP_Post object.
	 *
	 * @since 2.7
	 * @access private
	 *
	 * @param object $discount WP_Post instance of the discount.
	 * @return bool Object initialization successful or not.
	 */
	private function setup_discount( $discount = null ) {
		if ( is_null( $discount ) ) {
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
				case 'start_date':
				case 'end_date':
					if ( '0000-00-00 00:00:00' === $value || is_null( $value ) ) {
						$this->{$key} = false;
						break;
					}
				case 'notes':
					if ( ! empty( $value ) ) {
						$this->{$key} = $value;
					}
					break;
				case 'id':
					$this->{$key} = (int) $value;
					break;
				case 'min_charge_amount':
					$this->min_charge_amount = $value;
					break;
				default:
					if ( is_string( $value ) ) {
						@json_decode( $value );
						if ( json_last_error() !== JSON_ERROR_NONE ) {
							$this->{$key} = json_decode( $value );
						}
					}

					$this->{$key} = $value;
					break;
			}
		}

		/**
		 * Some object vars need to be setup manually as the values need to be
		 * pulled in from the `edd_adjustmentmeta` table.
		 */
		$this->excluded_products = (array) edd_get_adjustment_meta( $this->id, 'excluded_product',    false );
		$this->product_reqs      = (array) edd_get_adjustment_meta( $this->id, 'product_requirement', false );
		$this->product_condition = (string) edd_get_adjustment_meta( $this->id, 'product_condition', true );

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
	 * Helper method to retrieve meta data associated with the discount.
	 *
	 * @since 2.7
	 *
	 * @param string $key    Meta key.
	 * @param bool   $single Return single item or array.
	 *
	 * @return mixed
	 */
	public function get_meta( $key = '', $single = true ) {
		return edd_get_adjustment_meta( $this->id, $key, $single );
	}

	/**
	 * Helper method to update meta data associated with the discount.
	 *
	 * @since 2.7
	 *
	 * @param string $key        Meta key to update.
	 * @param string $value      New meta value to set.
	 * @param string $prev_value Optional. Previous meta value.
	 *
	 * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
	 */
	public function update_meta( $key, $value = '', $prev_value = '' ) {
		$filter_key = '_edd_discount_' . $key;

		/**
		 * Filters the meta value being updated.
		 * The key is prefixed with `_edd_discount_` for 2.9 backwards compatibility.
		 *
		 * @param mixed $value Value being set.
		 * @param int   $id    Discount ID.
		 */
		$value = apply_filters( 'edd_update_discount_meta_' . $filter_key, $value, $this->id );

		return edd_update_adjustment_meta( $this->id, $key, $value, $prev_value );
	}

	/**
	 * Retrieve the code used to apply the discount.
	 *
	 * @since 2.7
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
	 * Retrieves the status label of the discount.
	 *
	 * This method exists as a helper, until legitimate Status classes can be
	 * registered that will contain an array of status-specific labels.
	 *
	 * @since 2.9
	 *
	 * @return string Status label for the current discount.
	 */
	public function get_status_label() {

		// Default label
		$label = ucwords( $this->status );

		// Specific labels
		switch ( $this->status ) {
			case '':
				$label = __( 'None',     'easy-digital-downloads' );
				break;
			case 'draft':
				$label = __( 'Draft',    'easy-digital-downloads' );
				break;
			case 'expired':
				$label = __( 'Expired',  'easy-digital-downloads' );
				break;
			case 'inactive':
				$label = __( 'Inactive', 'easy-digital-downloads' );
				break;
			case 'archived':
				$label = __( 'Archived', 'easy-digital-downloads' );
				break;
			case 'active':
				$label = __( 'Active',   'easy-digital-downloads' );
				break;
			case 'inherit':
				if ( ! empty( $this->parent ) ) {
					$parent = edd_get_discount( $this->parent );
					$label  = $parent->get_status_label();
					break;
				}
		}

		/**
		 * Filters the discount status.
		 *
		 * @since 2.9
		 *
		 * @param string $label  Discount status label.
		 * @param string $status Discount status (active or inactive).
		 * @param int    $id     Discount ID.
		 */
		return apply_filters( 'edd_get_discount_status_label', $label, $this->status, $this->id );
	}

	/**
	 * Retrieve the type of discount.
	 *
	 * @since 2.7
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
		return apply_filters( 'edd_get_discount_type', $this->amount_type, $this->id );
	}

	/**
	 * Retrieve the discount amount.
	 *
	 * @since 2.7
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
		return (float) apply_filters( 'edd_get_discount_min_price', $this->min_charge_amount, $this->id );
	}

	/**
	 * Retrieve the usage limit per limit (if the discount can only be used once per customer).
	 *
	 * @since 2.7
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
	 *
	 * @param array $args Discount details.
	 * @return mixed bool|int false if data isn't passed and class not instantiated for creation, or post ID for the new discount.
	 */
	public function add( $args = array() ) {

		// If no code is provided, return early with false
		if ( empty( $args['code'] ) ) {
			return false;
		}

		if ( ! empty( $this->id ) && $this->exists() ) {
			return $this->update( $args );

		} else {
			$args = self::convert_legacy_args( $args );

			if ( ! empty( $args['start_date'] ) ) {
				$args['start_date'] = date( 'Y-m-d H:i:s', strtotime( $args['start_date'], current_time( 'timestamp' ) ) );
			}

			if ( ! empty( $args['end_date'] ) ) {
				$args['end_date'] = date( 'Y-m-d H:i:s', strtotime( $args['end_date'], current_time( 'timestamp' ) ) );

				if ( strtotime( $args['end_date'], current_time( 'timestamp' ) ) < current_time( 'timestamp' ) ) {
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
			 * @since 2.7
			 *
			 * @param array $args Discount args.
			 */
			$args = apply_filters( 'edd_insert_discount', $args );

			/**
			 * Filters the args before being inserted into the database (kept for backwards compatibility purposes)
			 *
			 * @since 2.7
			 * @since 3.0 Updated parameters to pass $args twice for backwards compatibility.
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
				$this->{$key} = $value;
			}

			// We have to ensure an ID is not passed to edd_add_discount()
			unset( $args['id'] );

			$id = edd_add_discount( $args );

			// The DB class 'add' implies an update if the discount being asked to be created already exists
			if ( ! empty( $id ) ) {

				// We need to update the ID of the instance of the object in order to add meta
				$this->id = $id;

				if ( isset( $args['excluded_products'] ) ) {
					if ( is_array( $args['excluded_products'] ) ) {
						foreach ( $args['excluded_products'] as $product ) {
							edd_add_adjustment_meta( $this->id, 'excluded_product', absint( $product ) );
						}
					}
				}

				if ( isset( $args['product_reqs'] ) ) {
					if ( is_array( $args['product_reqs'] ) ) {
						foreach ( $args['product_reqs'] as $product ) {
							edd_add_adjustment_meta( $this->id, 'product_requirement', absint( $product ) );
						}
					}
				}
			}

			/**
			 * Fires after the discount code is inserted.
			 *
			 * @since 2.7
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
	 *
	 * @param array $args Discount details.
	 * @return bool True if update is successful, false otherwise.
	 */
	public function update( $args = array() ) {
		$args = self::convert_legacy_args( $args );
		$ret  = false;

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

		// Get current time once to avoid inconsistencies
		$current_time = current_time( 'timestamp' );

		if ( ! empty( $args['start_date'] ) && ! empty( $args['end_date'] ) ) {
			$start_timestamp = strtotime( $args['start_date'], $current_time );
			$end_timestamp   = strtotime( $args['end_date'],   $current_time );

			// Set the expiration date to the start date if start is later than expiration
			if ( $start_timestamp > $end_timestamp ) {
				$args['end_date'] = $args['start_date'];
			}
		}

		// Start date
		if ( ! empty( $args['start_date'] ) ) {
			$args['start_date'] = date( 'Y-m-d H:i:s', strtotime( $args['start_date'], $current_time ) );
		}

		// End date
		if ( ! empty( $args['end_date'] ) ) {
			$args['end_date'] = date( 'Y-m-d H:i:s', strtotime( $args['end_date'], $current_time ) );
		}

		if ( isset( $args['excluded_products'] ) ) {
			// Reset meta
			edd_delete_adjustment_meta( $this->id, 'excluded_product' );

			if ( is_array( $args['excluded_products'] ) ) {
				// Now add each newly excluded product
				foreach ( $args['excluded_products'] as $product ) {
					edd_add_adjustment_meta( $this->id, 'excluded_product', absint( $product ) );
				}
			}
		}

		if ( isset( $args['product_reqs'] ) ) {
			// Reset meta
			edd_delete_adjustment_meta( $this->id, 'product_requirement' );

			if ( is_array( $args['product_reqs'] ) ) {
				// Now add each newly required product
				foreach ( $args['product_reqs'] as $product ) {
					edd_add_adjustment_meta( $this->id, 'product_requirement', absint( $product ) );
				}
			}
		}

		// Switch `type` to `amount_type`
		if ( ! isset( $args['amount_type'] ) && ! empty( $args['type'] ) && 'discount' !== $args['type'] ) {
			$args['amount_type'] = $args['type'];
		}

		// Force `type` to `discount`
		$args['type'] = 'discount';

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
		if ( edd_update_discount( $this->id, $args ) ) {
			$discount = edd_get_discount( $this->id );
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
	 *
	 * @param bool $set_error Whether an error message be set in session.
	 * @return bool Is discount started?
	 */
	public function is_started( $set_error = true ) {
		$return = false;

		if ( $this->start_date ) {
			$start_date = strtotime( $this->start_date );

			if ( $start_date < time() ) {
				// Discount has pased the start date
				$return = true;
			} elseif ( $set_error ) {
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
		 * @param bool $set_error Whether an error message be set in session.
		 */
		return apply_filters( 'edd_is_discount_started', $return, $this->id, $set_error );
	}

	/**
	 * Check if the discount has expired.
	 *
	 * @since 2.7
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

		if ( $end_date < time() ) {
			if ( $update ) {
				$this->update_status( 'expired' );
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
	 *
	 * @param bool $set_error Whether an error message be set in session.
	 * @return bool Is discount maxed out?
	 */
	public function is_maxed_out( $set_error = true ) {
		$return = false;

		if ( ! empty( $this->max_uses ) && $this->get_uses() >= $this->max_uses ) {
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
		 * @param bool $set_error Whether an error message be set in session.
		 */
		return apply_filters( 'edd_is_discount_maxed_out', $return, $this->id, $set_error );
	}

	/**
	 * Check if the minimum cart amount is satisfied for the discount to hold.
	 *
	 * @since 2.7
	 *
	 * @param bool $set_error Whether an error message be set in session.
	 * @return bool Is the minimum cart amount met?
	 */
	public function is_min_price_met( $set_error = true ) {
		$return = false;

		$cart_amount = edd_get_cart_discountable_subtotal( $this->id );

		if ( (float) $cart_amount >= (float) $this->min_charge_amount ) {
			$return = true;
		} elseif ( $set_error ) {
			edd_set_error( 'edd-discount-error', sprintf( __( 'Minimum order of %s not met.', 'easy-digital-downloads' ), edd_currency_filter( edd_format_amount( $this->min_charge_amount ) ) ) );
		}

		/**
		 * Filters if the minimum cart amount has been met to satisfy the discount.
		 *
		 * @since 2.7
		 *
		 * @param bool $return Is the minimum cart amount met or not.
		 * @param int  $ID     Discount ID.
		 * @param bool $set_error Whether an error message be set in session.
		 */
		return apply_filters( 'edd_is_discount_min_met', $return, $this->id, $set_error );
	}

	/**
	 * Is the discount single use or not?
	 *
	 * @since 2.7
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
	 *
	 * @param bool        $set_error Whether an error message be set in session.
	 * @param false|array $cart_ids  Cart item IDs or a specific array of download IDs to check (added in 3.2.0).
	 * @return bool Are required products in the cart?
	 */
	public function is_product_requirements_met( $set_error = true, $cart_ids = false ) {
		$is_met       = true;
		$product_reqs = $this->get_product_reqs();
		// Filter out any empty values.
		$product_reqs = array_map( 'strval', array_filter( array_values( $product_reqs ) ) );

		if ( empty( $cart_ids ) ) {
			$cart_items = edd_get_cart_contents();
			// Combine cart item IDs with their price IDs and convert to strings as we are going to deal with string values.
			$cart_ids = array_map(
				function ( $cart_item ) {
					$price_id = isset( $cart_item['options']['price_id'] ) && is_numeric( $cart_item['options']['price_id'] ) ? $cart_item['options']['price_id'] : null;

					return ! is_null( $price_id ) ? $cart_item['id'] . '_' . $price_id : strval( $cart_item['id'] );
				},
				$cart_items
			);
		}

		// Ensure we have requirements before proceeding.
		if ( ! empty( $product_reqs ) ) {
			// Set up an array of requirements with all values set to false.
			$requirements = array_fill_keys( $product_reqs, false );
			foreach ( $cart_ids as $cart_id ) {
				$key             = $cart_id;
				$requirement_met = array_key_exists( $cart_id, $requirements );

				// If requirement is not met and the cart item is a variable product, check if the parent product is in the requirements.
				if ( ! $requirement_met && preg_match( '/^(\d+)_(\d+)$/', $cart_id ) ) {
					// Using absint to strip out anything after the _ in price id, so we can have the parent ID.
					$key             = (string) absint( $cart_id );
					$requirement_met = array_key_exists( $key, $requirements );
				}

				if ( $requirement_met ) {
					$requirements[ $key ] = true;
				}
			}

			if ( 'all' === $this->get_product_condition() ) {
				$is_met = ! in_array( false, $requirements, true );
			} else {
				$is_met = in_array( true, $requirements, true );
			}

			if ( ! $is_met && $set_error ) {
				edd_set_error( 'edd-discount-error', __( 'The product requirements for this discount are not met.', 'easy-digital-downloads' ) );
			}
		}

		$excluded_ps = $this->get_excluded_products();
		$excluded_ps = array_map( 'absint', $excluded_ps );
		asort( $excluded_ps );
		$excluded_ps = array_filter( array_values( $excluded_ps ) );

		if ( ! empty( $excluded_ps ) ) {
			// Cast the card IDs back to integers.
			$cart_ids = array_unique( array_map( 'absint', $cart_ids ) );
			if ( count( array_intersect( $cart_ids, $excluded_ps ) ) === count( $cart_ids ) ) {
				$is_met = false;

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
		 * @param bool   $is_met            Are the product requirements met or not.
		 * @param int    $ID                Discount ID.
		 * @param string $product_condition Product condition.
		 * @param bool $set_error Whether an error message be set in session.
		 */
		return (bool) apply_filters( 'edd_is_discount_products_req_met', $is_met, $this->id, $this->product_condition, $set_error );
	}

	/**
	 * Is the discount valid for the selected categories?
	 *
	 * @since 3.2.0
	 * @param bool        $set_error Whether an error message be set in session.
	 * @param false|array $cart_ids  Cart item IDs or a specific array of download IDs to check.
	 * @return bool
	 */
	public function is_valid_for_categories( $set_error = true, $cart_ids = false ) {
		$categories = $this->get_categories();
		// If no categories are set, then the condition is met.
		if ( empty( $categories ) ) {
			return true;
		}

		// Assume the condition is not met.
		$categories     = array_map( 'intval', $categories );
		$term_condition = $this->get_term_condition();
		if ( false === $cart_ids ) {
			$cart_ids = $this->get_cart_ids();
		}

		// If any of the cart items have a category that matches the discount and the condition is include, return true.
		if ( empty( $term_condition ) ) {
			if ( $this->item_has_terms( $cart_ids, $categories ) ) {
				return true;
			}
		}

		if ( 'exclude' === $term_condition ) {
			// Check each item in the cart to see if it has a category that matches the discount. Return true as soon as one is found.
			foreach ( $cart_ids as $download_id ) {
				if ( ! $this->item_has_terms( $download_id, $categories ) ) {
					return true;
				}
			}
		}

		if ( $set_error ) {
			edd_set_error( 'edd-discount-error', __( 'This discount is not valid for the cart contents.', 'easy-digital-downloads' ) );
		}

		return false;
	}

	/**
	 * Gets the categories for the discount.
	 *
	 * @since 3.2.0
	 * @return array
	 */
	public function get_categories() {
		return array_filter( (array) edd_get_adjustment_meta( $this->id, 'categories', true ) );
	}

	/**
	 * Gets the term condition for the discount.
	 *
	 * @since 3.2.0
	 * @return string
	 */
	public function get_term_condition() {
		return (string) edd_get_adjustment_meta( $this->id, 'term_condition', true );
	}

	/**
	 * Has the discount code been used.
	 *
	 * @since 2.7
	 * @since 3.0 Refactored to use new query methods.
	 * @since 3.2 Refactored to use the order adjustments table, and always run the filter.
	 *
	 * @param string $user      User info.
	 * @param bool   $set_error Whether an error message be set in session.
	 *
	 * @return bool Whether the discount has been used or not.
	 */
	public function is_used( $user = '', $set_error = true ) {
		$discount_used = false;

		if ( $this->is_single_use ) {
			$by_user_id = ! is_email( $user );
			$customer   = new EDD_Customer( $user, $by_user_id );

			if ( ! empty( $customer ) ) {
				$order_ids         = $customer->get_order_ids( edd_get_complete_order_statuses() );
				$order_adjustments = edd_get_order_adjustments(
					array(
						'type'          => 'discount',
						'type_id'       => $this->id,
						'object_id__in' => $order_ids,
						'object_type'   => 'order',
					)
				);

				$discount_used = ! empty( $order_adjustments );
			}
		}

		/**
		 * Filters if the discount is used or not.
		 *
		 * @since 2.7
		 * @since 3.2 This filter is now always run prior to returning if it has been used. Previously if the discount was found to be used,
		 * the method returned early and this filter would have never been run.
		 *
		 * @param bool   $discount_used If the discount is used or not.
		 * @param int    $ID     Discount ID.
		 * @param string $user   User info.
		 * @param bool $set_error Whether an error message be set in session.
		 */
		$discount_used = apply_filters( 'edd_is_discount_used', $discount_used, $this->id, $user, $set_error );

		if ( true === $discount_used && $set_error ) {
			edd_set_error( 'edd-discount-error', __( 'This discount has already been redeemed.', 'easy-digital-downloads' ) );
		}

		return $discount_used;
	}

	/**
	 * Checks whether a discount holds at the time of purchase.
	 *
	 * @since 2.7
	 *
	 * @param string $user      User info.
	 * @param bool   $set_error Whether an error message be set in session.
	 * @return bool Is the discount valid or not?
	 */
	public function is_valid( $user = '', $set_error = true ) {
		$return = false;
		$user   = trim( $user );

		if ( edd_get_cart_contents() && $this->id ) {
			if (
				! $this->is_archived( $set_error ) &&
				$this->is_started( $set_error ) &&
				$this->is_active( true, $set_error ) &&
				! $this->is_maxed_out( $set_error ) &&
				! $this->is_used( $user, $set_error ) &&
				$this->is_product_requirements_met( $set_error ) &&
				$this->is_min_price_met( $set_error ) &&
				$this->is_valid_for_categories( $set_error )
			) {
				$return = true;
			}
		} elseif ( $set_error ) {
			edd_set_error( 'edd-discount-error', _x( 'This discount is invalid.', 'error for when a discount is invalid based on its configuration', 'easy-digital-downloads' ) );
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
		 * @param bool $set_error Whether an error message be set in session.
		 */
		return apply_filters( 'edd_is_discount_valid', $return, $this->id, $this->code, $user, $set_error );
	}

	/**
	 * Checks if a discount code is archived.
	 *
	 * @since 3.2.0
	 *
	 * @param bool $set_error Whether an error message be set in session.
	 * @return bool If the discount is archived or not.
	 */
	public function is_archived( $set_error = true ) {
		$is_archived = false;

		if ( 'archived' === $this->status ) {
			$is_archived = true;
			if ( $set_error ) {
				edd_set_error( 'edd-discount-error', _x( 'This discount is invalid.', 'error for when a discount is invalid based on its configuration', 'easy-digital-downloads' ) );
			}
		}

		return $is_archived;
	}

	/**
	 * Checks if a discount code is active.
	 *
	 * @since 2.7
	 * @since 3.2 Also verifies that a discount hasn't started before returning `active`.
	 *
	 * @param bool $update    Update the discount to expired if an one is found but has an active status.
	 * @param bool $set_error Whether an error message be set in session.
	 * @return bool If the discount is active or not.
	 */
	public function is_active( $update = true, $set_error = true ) {
		$return = false;

		if ( $this->exists() && 'archived' !== $this->status ) {

			if ( $this->is_expired( $update ) ) {
				if ( edd_doing_ajax() && $set_error ) {
					edd_set_error( 'edd-discount-error', __( 'This discount is expired.', 'easy-digital-downloads' ) );
				}
			} elseif ( ! $this->is_started( $set_error ) ) {
				$return = false;
			} elseif ( 'active' === $this->status ) {
				$return = true;
			} elseif ( edd_doing_ajax() && $set_error ) {
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
		 * @param bool $set_error Whether an error message be set in session.
		 */
		return apply_filters( 'edd_is_discount_active', $return, $this->id, $set_error );
	}

	/**
	 * Get Discounted Amount.
	 *
	 * @since 2.7
	 *
	 * @param string|int $base_price Price before discount.
	 * @return float $discounted_price Amount after discount.
	 */
	public function get_discounted_amount( $base_price ) {
		$base_price = floatval( $base_price );

		if ( 'flat' === $this->amount_type ) {
			$amount = $base_price - floatval( $this->amount );

			if ( $amount < 0 ) {
				$amount = 0;
			}
		} else {
			// Percentage discount
			$amount = $base_price - ( $base_price * ( floatval( $this->amount ) / 100 ) );
		}

		/**
		 * Filter the discounted amount calculated.
		 *
		 * @since 2.7
		 * @access public
		 *
		 * @param float $amount Calculated discounted amount.
		 * @param EDD_Discount $this Discount object.
		 */
		return apply_filters( 'edd_discounted_amount', $amount, $this );
	}

	/**
	 * Increment the usage of the discount.
	 *
	 * @since 2.7
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

		$this->max_uses = absint( $this->max_uses );

		if ( 0 !== $this->max_uses && $this->max_uses <= $this->use_count ) {
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

		if ( 0 !== $this->max_uses && $this->max_uses > $this->use_count ) {
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
	 *
	 * @return string Link to the `Edit Discount` page.
	 */
	public function edit_url() {
		return esc_url(
			edd_get_admin_url(
				array(
					'page'       => 'edd-discounts',
					'edd-action' => 'edit_discount',
					'discount'   => absint( $this->id ),
				)
			)
		);
	}

	/**
	 * Sanitize the data for update/create
	 *
	 * @since  3.0
	 * @param  array $data The data to sanitize
	 * @return array       The sanitized data, based off column defaults
	 */
	private function sanitize_columns( $data ) {
		$default_values = array();

		foreach ( $data as $key => $type ) {

			// Only sanitize data that we were provided
			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}

			switch ( $type ) {

				case '%s':
					if ( 'email' === $key ) {
						$data[ $key ] = sanitize_email( $data[ $key ] );
					} elseif ( 'notes' === $key ) {
						$data[ $key ] = strip_tags( $data[ $key ] );
					} else {
						if ( is_array( $data[ $key ] ) ) {
							$data[ $key ] = json_encode( $data[ $key ] );
						} else {
							$data[ $key ] = sanitize_text_field( $data[ $key ] );
						}
					}
					break;

				case '%d':
					if ( ! is_numeric( $data[ $key ] ) || absint( $data[ $key ] ) !== (int) $data[ $key ] ) {
						$data[ $key ] = $default_values[ $key ];
					} else {
						$data[ $key ] = absint( $data[ $key ] );
					}
					break;

				case '%f':
					// Convert what was given to a float
					$value = floatval( $data[ $key ] );

					if ( ! is_float( $value ) ) {
						$data[ $key ] = $default_values[ $key ];
					} else {
						$data[ $key ] = $value;
					}
					break;

				default:
					$data[ $key ] = ! is_array( $data[ $key ] )
						? sanitize_text_field( $data[ $key ] )
						: maybe_serialize( array_map( 'sanitize_text_field', $data[ $key ] ) );
					break;
			}
		}

		return $data;
	}

	/**
	 * Converts pre-3.0 arguments to the 3.0+ version.
	 *
	 * @since 3.0
	 * @static
	 *
	 * @param $args array Arguments to be converted.
	 * @return array The converted arguments.
	 */
	public static function convert_legacy_args( $args = array() ) {

		// Loop through arguments provided and adjust old key names for the new schema introduced in 3.0
		$old = array(
			'uses'              => 'use_count',
			'max'               => 'max_uses',
			'start'             => 'start_date',
			'expiration'        => 'end_date',
			'min_price'         => 'min_charge_amount',
			'products'          => 'product_reqs',
			'excluded-products' => 'excluded_products',
			'not_global'        => 'scope',
			'is_not_global'     => 'scope',
			'use_once'          => 'once_per_customer',
			'is_single_use'     => 'once_per_customer',
		);

		foreach ( $old as $old_key => $new_key ) {
			if ( isset( $args[ $old_key ] ) ) {
				if ( in_array( $old_key, array( 'not_global', 'is_not_global' ), true ) && ! array_key_exists( 'scope', $args ) ) {
					$args[ $new_key ] = ! empty( $args[ $old_key ] )
						? 'not_global'
						: 'global';
				} else {
					$args[ $new_key ] = $args[ $old_key ];
				}
			}
			unset( $args[ $old_key ] );
		}

		// Default status needs to be active for regression purposes.
		// See https://github.com/easydigitaldownloads/easy-digital-downloads/issues/6806
		if ( ! isset( $args['status'] ) ) {
			$args['status'] = 'active';
		}

		return $args;
	}

	/**
	 * Gets the IDs of all of the items in the cart.
	 *
	 * @since 3.2.0
	 * @return array
	 */
	private function get_cart_ids() {
		$cart_items = edd_get_cart_contents();
		if ( empty( $cart_items ) ) {
			return array();
		}

		$cart_ids = wp_list_pluck( $cart_items, 'id' );
		$cart_ids = array_filter( array_map( 'absint', $cart_ids ) );
		asort( $cart_ids );

		return array_values( $cart_ids );
	}

	/**
	 * Checks if a cart item/download has any of the specified categories.
	 *
	 * @since 3.2.0
	 * @param int|int[] $download_ids Download ID(s) to check.
	 * @param array     $categories   Category IDs to check.
	 * @return bool
	 */
	private function item_has_terms( $download_ids, $categories ) {
		$cart_item_categories = wp_get_object_terms( $download_ids, 'download_category', array( 'fields' => 'ids' ) );
		if ( empty( $cart_item_categories ) || is_wp_error( $cart_item_categories ) ) {
			return false;
		}

		$cart_item_categories = array_map( 'intval', $cart_item_categories );
		if ( count( array_intersect( $cart_item_categories, $categories ) ) > 0 ) {
			return true;
		}

		// Check for parent categories.
		foreach ( $cart_item_categories as $term_id ) {
			$parent_category_ids = array_map( 'intval', get_ancestors( $term_id, 'download_category', 'taxonomy' ) );

			if ( count( array_intersect( $parent_category_ids, $categories ) ) > 0 ) {
				return true;
			}
		}

		return false;
	}
}
