<?php
/**
 * Payments Query
 *
 * @package     EDD
 * @subpackage  Classes/Stats
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
*/


/**
 * EDD_Payments_Query Class
 *
 * This class is for retrieving payments data
 *
 * Payments can be retrieved for date ranges and pre-defined periods
 *
 * @since 1.8
 */
class EDD_Payments_Query extends EDD_Stats {

	/**
	 * The args to pass to the edd_get_payments() query
	 *
	 * @var array
	 * @access public
	 * @since 1.8
	 */
	public $args = array();

	/**
	 * The payments found based on the criteria set
	 *
	 * @var array
	 * @access public
	 * @since 1.8
	 */
	public $payments = array();

	/**
	 * Default query arguments.
	 *
	 * Not all of these are valid arguments that can be passed to WP_Query. The ones that are not, are modified before
	 * the query is run to convert them to the proper syntax.
	 *
	 * @access public
	 * @since 1.8
	 * @param $args array The array of arguments that can be passed in and used for setting up this payment query.
	 */
	public function __construct( $args = array() ) {
		$defaults = array(
			'output'          => 'payments', // Use 'posts' to get standard post objects
			'post_type'       => array( 'edd_payment' ),
			'start_date'      => false,
			'end_date'        => false,
			'number'          => 20,
			'page'            => null,
			'orderby'         => 'ID',
			'order'           => 'DESC',
			'user'            => null,
			'status'          => 'any',
			'meta_key'        => null,
			'year'            => null,
			'month'           => null,
			'day'             => null,
			's'               => null,
			'search_in_notes' => false,
			'children'        => false,
			'fields'          => null,
			'download'        => null
		);

		$this->args = wp_parse_args( $args, $defaults );

		$this->init();
	}

	/**
	 * Set a query variable.
	 *
	 * @access public
	 * @since 1.8
	 */
	public function __set( $query_var, $value ) {
		if ( in_array( $query_var, array( 'meta_query', 'tax_query' ) ) )
			$this->args[ $query_var ][] = $value;
		else
			$this->args[ $query_var ] = $value;
	}

	/**
	 * Unset a query variable.
	 *
	 * @access public
	 * @since 1.8
	 */
	public function __unset( $query_var ) {
		unset( $this->args[ $query_var ] );
	}

	/**
	 * Modify the query/query arguments before we retrieve payments.
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function init() {

		add_action( 'edd_pre_get_payments', array( $this, 'date_filter_pre' ) );
		add_action( 'edd_post_get_payments', array( $this, 'date_filter_post' ) );

		add_action( 'edd_pre_get_payments', array( $this, 'orderby' ) );
		add_action( 'edd_pre_get_payments', array( $this, 'status' ) );
		add_action( 'edd_pre_get_payments', array( $this, 'month' ) );
		add_action( 'edd_pre_get_payments', array( $this, 'per_page' ) );
		add_action( 'edd_pre_get_payments', array( $this, 'page' ) );
		add_action( 'edd_pre_get_payments', array( $this, 'user' ) );
		add_action( 'edd_pre_get_payments', array( $this, 'search' ) );
		add_action( 'edd_pre_get_payments', array( $this, 'mode' ) );
		add_action( 'edd_pre_get_payments', array( $this, 'children' ) );
		add_action( 'edd_pre_get_payments', array( $this, 'download' ) );
	}

	/**
	 * Retrieve payments.
	 *
	 * The query can be modified in two ways; either the action before the
	 * query is run, or the filter on the arguments (existing mainly for backwards
	 * compatibility).
	 *
	 * @access public
	 * @since 1.8
	 * @return object
	 */
	public function get_payments() {

		do_action( 'edd_pre_get_payments', $this );

		$query = new WP_Query( $this->args );

		if ( 'payments' != $this->args[ 'output' ] ) {
			return $query->posts;
		}

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$details = new stdClass;

				$payment_id            = get_post()->ID;

				$details->ID           = $payment_id;
				$details->date         = get_post()->post_date;
				$details->post_status  = get_post()->post_status;
				$details->total        = edd_get_payment_amount( $payment_id );
				$details->subtotal     = edd_get_payment_subtotal( $payment_id );
				$details->tax          = edd_get_payment_tax( $payment_id );
				$details->fees         = edd_get_payment_fees( $payment_id );
				$details->key          = edd_get_payment_key( $payment_id );
				$details->gateway      = edd_get_payment_gateway( $payment_id );
				$details->user_info    = edd_get_payment_meta_user_info( $payment_id );
				$details->cart_details = edd_get_payment_meta_cart_details( $payment_id, true );

				if( edd_get_option( 'enable_sequential' ) ) {
					$details->payment_number = edd_get_payment_number( $payment_id );
				}

				$this->payments[] = apply_filters( 'edd_payment', $details, $payment_id, $this );
			}

			wp_reset_postdata();
		}

		do_action( 'edd_post_get_payments', $this );

		return $this->payments;
	}

	/**
	 * If querying a specific date, add the proper filters.
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function date_filter_pre() {
		if( ! ( $this->args[ 'start_date' ] || $this->args[ 'end_date' ] ) ) {
			return;
		}

		$this->setup_dates( $this->args[ 'start_date' ], $this->args[ 'end_date' ] );

		add_filter( 'posts_where', array( $this, 'payments_where' ) );
	}

	/**
	 * If querying a specific date, remove filters after the query has been run
	 * to avoid affecting future queries.
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function date_filter_post() {
		if ( ! ( $this->args[ 'start_date' ] || $this->args[ 'end_date' ] ) ) {
			return;
		}

		remove_filter( 'posts_where', array( $this, 'payments_where' ) );
	}

	/**
	 * Post Status
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function status() {
		if ( ! isset ( $this->args[ 'status' ] ) ) {
			return;
		}

		$this->__set( 'post_status', $this->args[ 'status' ] );
		$this->__unset( 'status' );
	}

	/**
	 * Current Page
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function page() {
		if ( ! isset ( $this->args[ 'page' ] ) ) {
			return;
		}

		$this->__set( 'paged', $this->args[ 'page' ] );
		$this->__unset( 'page' );
	}

	/**
	 * Posts Per Page
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function per_page() {

		if( ! isset( $this->args[ 'number' ] ) ){
			return;
		}

		if ( $this->args[ 'number' ] == -1 ) {
			$this->__set( 'nopaging', true );
		}
		else{
			$this->__set( 'posts_per_page', $this->args[ 'number' ] );
		}

		$this->__unset( 'number' );
	}

	/**
	 * Current Month
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function month() {
		if ( ! isset ( $this->args[ 'month' ] ) ) {
			return;
		}

		$this->__set( 'monthnum', $this->args[ 'month' ] );
		$this->__unset( 'month' );
	}

	/**
	 * Order
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function orderby() {
		switch ( $this->args[ 'orderby' ] ) {
			case 'amount' :
				$this->__set( 'orderby', 'meta_value_num' );
				$this->__set( 'meta_key', '_edd_payment_total' );
			break;
			default :
				$this->__set( 'orderby', $this->args[ 'orderby' ] );
			break;
		}
	}

	/**
	 * Specific User
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function user() {
		if ( is_null( $this->args[ 'user' ] ) ) {
			return;
		}

		if ( is_numeric( $this->args[ 'user' ] ) ) {
			$user_key = '_edd_payment_user_id';
		} else {
			$user_key = '_edd_payment_user_email';
		}

		$this->__set( 'meta_query', array(
			'key'   => $user_key,
			'value' => $this->args[ 'user' ]
		) );
	}

	/**
	 * Search
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function search() {

		if( ! isset( $this->args[ 's' ] ) ) {
			return;
		}
		
		$search = trim( $this->args[ 's' ] );

		if( empty( $search ) ) {
			return;
		}

        $is_email = is_email( $search ) || strpos( $search, '@' ) !== false;
		$is_user  = strpos( $search, strtolower( 'user:' ) ) !== false;

		if ( ! empty( $this->args[ 'search_in_notes' ] ) ) {

			$notes = edd_get_payment_notes( 0, $search );

			if( ! empty( $notes ) ) {

				$payment_ids = wp_list_pluck( (array) $notes, 'comment_post_ID' );

				$this->__set( 'post__in', $payment_ids );
			}

			$this->__unset( 's' );

		} elseif ( $is_email || strlen( $search ) == 32 ) {

			$key = $is_email ? '_edd_payment_user_email' : '_edd_payment_purchase_key';
			$search_meta = array(
				'key'     => $key,
				'value'   => $search,
				'compare' => 'LIKE'
			);

			$this->__set( 'meta_query', $search_meta );
			$this->__unset( 's' );

		} elseif ( $is_user ) {

			$search_meta = array(
				'key'   => '_edd_payment_user_id',
				'value' => trim( str_replace( 'user:', '', strtolower( $search ) ) )
			);

			$this->__set( 'meta_query', $search_meta );

			if( edd_get_option( 'enable_sequential' ) ) {

				$search_meta = array(
					'key'     => '_edd_payment_number',
					'value'   => $search,
					'compare' => 'LIKE'
				);

				$this->__set( 'meta_query', $search_meta );

				$this->args['meta_query']['relation'] = 'OR';

			}

			$this->__unset( 's' );

		} elseif ( 
			edd_get_option( 'enable_sequential' ) && 
			(
				false !== strpos( $search, edd_get_option( 'sequential_prefix' ) ) ||
				false !== strpos( $search, edd_get_option( 'sequential_postfix' ) ) 
			)
		) {

			$search_meta = array(
				'key'     => '_edd_payment_number',
				'value'   => $search,
				'compare' => 'LIKE'
			);

			$this->__set( 'meta_query', $search_meta );
			$this->__unset( 's' );

		} elseif ( is_numeric( $search ) ) {

			$post = get_post( $search );

			if( is_object( $post ) && $post->post_type == 'edd_payment' ) {
				
				$arr   = array();
				$arr[] = $search;
				$this->__set( 'post__in', $arr );
				$this->__unset( 's' );
			}

		} elseif ( '#' == substr( $search, 0, 1 ) ) {

			$this->__set( 'download', str_replace( '#', '', $search ) );
			$this->__unset( 's' );

		} else {
			$this->__set( 's', $search );

		}

	}

	/**
	 * Payment Mode
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function mode() {
		if ( empty( $this->args[ 'mode' ] ) || $this->args[ 'mode' ] == 'all' ) {
			$this->__unset( 'mode' );
			return;
		}

		$this->__set( 'meta_query', array(
			'key'   => '_edd_payment_mode',
			'value' => $this->args[ 'mode' ]
		) );
	}

	/**
	 * Children
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function children() {
		if ( empty( $this->args[ 'children' ] ) ) {
			$this->__set( 'post_parent', 0 );
		}
		$this->__unset( 'children' );
	}

	/**
	 * Specific Download
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function download() {

		if ( empty( $this->args[ 'download' ] ) )
			return;

		global $edd_logs;

		$args = array(
			'post_parent'            => $this->args[ 'download' ],
			'log_type'               => 'sale',
			'post_status'            => array( 'publish' ),
			'nopaging'               => true,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'cache_results'          => false,
			'fields'                 => 'ids'
		);

		if ( is_array( $this->args[ 'download' ] ) ) {
			unset( $args[ 'post_parent' ] );
			$args[ 'post_parent__in' ] = $this->args[ 'download' ];
		}

		$sales = $edd_logs->get_connected_logs( $args );

		if ( ! empty( $sales ) ) {

			$payments = array();

			foreach ( $sales as $sale ) {
				$payments[] = get_post_meta( $sale, '_edd_log_payment_id', true );
			}

			$this->__set( 'post__in', $payments );

		} else {

			// Set post_parent to something crazy so it doesn't find anything
			$this->__set( 'post_parent', 999999999999999 );

		}

		$this->__unset( 'download' );

	}
}
