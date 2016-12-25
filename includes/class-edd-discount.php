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
}