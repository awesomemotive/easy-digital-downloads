<?php
/**
 * Payments Query
 *
 * @package     EDD
 * @subpackage  Classes/Stats
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

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
	 * @since 1.8
	 */
	public $args = array();

	/**
	 * The args as they came into the class.
	 *
	 * @var array
	 * @since 2.7.2
	 */
	public $initial_args = array();

	/**
	 * The payments found based on the criteria set
	 *
	 * @var array
	 * @since 1.8
	 */
	public $payments = array();

	/**
	 * Holds a boolean to determine if there is an existing $wp_query global.
	 *
	 * @var bool
	 * @access private
	 * @since 2.8
	 */
	private $existing_query;

	/**
	 * If an existing global $post item exists before we start our query, maintain it for later 'reset'.
	 *
	 * @var WP_Post|null
	 * @access private
	 * @since 2.8
	 */
	private $existing_post;

	/**
	 * Default query arguments.
	 *
	 * Not all of these are valid arguments that can be passed to WP_Query. The ones that are not, are modified before
	 * the query is run to convert them to the proper syntax.
	 *
	 * @since 1.8
	 * @param array $args The array of arguments that can be passed in and used for setting up this payment query.
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
			'customer'        => null,
			'status'          => edd_get_payment_status_keys(),
			'meta_key'        => null,
			'year'            => null,
			'month'           => null,
			'day'             => null,
			's'               => null,
			'search_in_notes' => false,
			'children'        => false,
			'fields'          => null,
			'download'        => null,
			'gateway'         => null,
			'post__in'        => null,
		);

		// We need to store an array of the args used to instantiate the class, so that we can use it in later hooks.
		$this->args = $this->initial_args = wp_parse_args( $args, $defaults );

		$this->init();
	}

	/**
	 * Set a query variable.
	 *
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
	 * @since 1.8
	 */
	public function __unset( $query_var ) {
		unset( $this->args[ $query_var ] );
	}

	/**
	 * Nothing here at the moment.
	 *
	 * @since 1.8
	 * @return void
	 */
	public function init() {

		// Before we start setting up queries, let's store any existing queries that might be in globals.
		$this->existing_query = isset( $GLOBALS['wp_query'] ) && isset( $GLOBALS['wp_query']->post );
		$this->existing_post  = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;

	}

	/**
	 * Retrieve payments.
	 *
	 * The query can be modified in two ways; either the action before the
	 * query is run, or the filter on the arguments (existing mainly for backwards
	 * compatibility).
	 *
	 * @since 1.8
	 * @return EDD_Payment[]
	 */
	public function get_payments() {

		// Modify the query/query arguments before we retrieve payments.
		$this->date_filter_pre();
		$this->orderby();
		$this->status();
		$this->month();
		$this->per_page();
		$this->page();
		$this->user();
		$this->customer();
		$this->search();
		$this->gateway();
		$this->mode();
		$this->children();
		$this->download();
		$this->post__in();

		do_action( 'edd_pre_get_payments', $this );

		$query = new WP_Query( $this->args );

		$custom_output = array(
			'payments',
			'edd_payments',
		);

		if ( ! in_array( $this->args['output'], $custom_output ) ) {
			return $query->posts;
		}

		if ( $query->have_posts() ) {

			while ( $query->have_posts() ) {
				$query->the_post();

				$payment_id = get_post()->ID;
				$payment    = edd_get_payment( $payment_id );

				if ( edd_get_option( 'enable_sequential' ) ) {
					// Backwards Compatibility, needs to set `payment_number` attribute
					$payment->payment_number = $payment->number;
				}

				$this->payments[] = apply_filters( 'edd_payment', $payment, $payment_id, $this );
			}

		}

		add_action( 'edd_post_get_payments', array( $this, 'date_filter_post' ) );

		do_action( 'edd_post_get_payments', $this );

		$this->maybe_reset_globals();

		return $this->payments;
	}

	/**
	 * If querying a specific date, add the proper filters.
	 *
	 * @since 1.8
	 */
	public function date_filter_pre() {
		if( ! ( $this->args['start_date'] || $this->args['end_date'] ) ) {
			return;
		}

		$this->setup_dates( $this->args['start_date'], $this->args['end_date'] );

		add_filter( 'posts_where', array( $this, 'payments_where' ) );
	}

	/**
	 * If querying a specific date, remove filters after the query has been run
	 * to avoid affecting future queries.
	 *
	 * @since 1.8
	 * @return void
	 */
	public function date_filter_post() {
		if ( ! ( $this->args['start_date'] || $this->args['end_date'] ) ) {
			return;
		}

		remove_filter( 'posts_where', array( $this, 'payments_where' ) );
	}

	/**
	 * Post Status
	 *
	 * @since 1.8
	 * @return void
	 */
	public function status() {
		if ( ! isset ( $this->args['status'] ) ) {
			return;
		}

		$this->__set( 'post_status', $this->args['status'] );
		$this->__unset( 'status' );
	}

	/**
	 * Current Page
	 *
	 * @since 1.8
	 * @return void
	 */
	public function page() {
		if ( ! isset ( $this->args['page'] ) ) {
			return;
		}

		$this->__set( 'paged', $this->args['page'] );
		$this->__unset( 'page' );
	}

	/**
	 * Posts Per Page
	 *
	 * @since 1.8
	 * @return void
	 */
	public function per_page() {

		if( ! isset( $this->args['number'] ) ){
			return;
		}

		if ( $this->args['number'] == -1 ) {
			$this->__set( 'nopaging', true );
		}
		else{
			$this->__set( 'posts_per_page', $this->args['number'] );
		}

		$this->__unset( 'number' );
	}

	/**
	 * Current Month
	 *
	 * @since 1.8
	 * @return void
	 */
	public function month() {
		if ( ! isset ( $this->args['month'] ) ) {
			return;
		}

		$this->__set( 'monthnum', $this->args['month'] );
		$this->__unset( 'month' );
	}

	/**
	 * Order by
	 *
	 * @since 1.8
	 * @return void
	 */
	public function orderby() {
		switch ( $this->args['orderby'] ) {
			case 'amount' :
				$this->__set( 'orderby', 'meta_value_num' );
				$this->__set( 'meta_key', '_edd_payment_total' );
			break;
			default :
				$this->__set( 'orderby', $this->args['orderby'] );
			break;
		}
	}

	/**
	 * Specific User
	 *
	 * @since 1.8
	 * @return void
	 */
	public function user() {
		if ( is_null( $this->args['user'] ) ) {
			return;
		}

		if ( is_numeric( $this->args['user'] ) ) {
			$user_key = '_edd_payment_user_id';
		} else {
			$user_key = '_edd_payment_user_email';
		}

		$this->__set( 'meta_query', array(
			'key'   => $user_key,
			'value' => $this->args['user']
		) );
	}

	/**
	 * Specific customer id
	 *
	 * @since   2.6
	 * @return  void
	 */
	public function customer() {
		if ( is_null( $this->args['customer'] ) || ! is_numeric( $this->args['customer'] ) ) {
			return;
		}

		$this->__set( 'meta_query', array(
			'key'   => '_edd_payment_customer_id',
			'value' => (int) $this->args['customer'],
		) );
	}

	/**
	 * Specific gateway
	 *
	 * @since   2.8
	 * @return  void
	 */
	public function gateway() {
		if ( is_null( $this->args['gateway'] ) ) {
			return;
		}

		$this->__set( 'meta_query', array(
			'key'   => '_edd_payment_gateway',
			'value' => $this->args['gateway']
		) );
	}

	/**
	 * Specific payments
	 *
	 * @since   2.8.7
	 * @return  void
	 */
	public function post__in() {
		if ( is_null( $this->args['post__in'] ) ) {
			return;
		}

		$this->__set( 'post__in', $this->args['post__in'] );
	}

	/**
	 * Search
	 *
	 * @since 1.8
	 * @return void
	 */
	public function search() {

		if( ! isset( $this->args['s'] ) ) {
			return;
		}

		$search = trim( $this->args['s'] );

		if( empty( $search ) ) {
			return;
		}

		$is_email = is_email( $search ) || strpos( $search, '@' ) !== false;
		$is_user  = strpos( $search, strtolower( 'user:' ) ) !== false;

		if ( ! empty( $this->args['search_in_notes'] ) ) {

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

			if ( edd_get_option( 'enable_sequential' ) ) {

				$search_meta = array(
					'key'     => '_edd_payment_number',
					'value'   => $search,
					'compare' => 'LIKE'
				);

				$this->__set( 'meta_query', $search_meta );
				$this->__unset( 's' );

			}

		} elseif ( '#' == substr( $search, 0, 1 ) ) {

			$search = str_replace( '#:', '', $search );
			$search = str_replace( '#', '', $search );
			$this->__set( 'download', $search );
			$this->__unset( 's' );

		} elseif ( 0 === strpos( $search, 'discount:' ) ) {

			$search = trim( str_replace( 'discount:', '', $search ) );
			$search = 'discount.*' . $search;

			$search_meta = array(
				'key'     => '_edd_payment_meta',
				'value'   => $search,
				'compare' => 'REGEXP',
			);

			$this->__set( 'meta_query', $search_meta );
			$this->__unset( 's' );

		} else {
			$this->__set( 's', $search );
		}

	}

	/**
	 * Payment Mode
	 *
	 * @since 1.8
	 * @return void
	 */
	public function mode() {
		if ( empty( $this->args['mode'] ) || $this->args['mode'] == 'all' ) {
			$this->__unset( 'mode' );
			return;
		}

		$this->__set( 'meta_query', array(
			'key'   => '_edd_payment_mode',
			'value' => $this->args['mode']
		) );
	}

	/**
	 * Children
	 *
	 * @since 1.8
	 * @return void
	 */
	public function children() {
		if ( empty( $this->args['children'] ) ) {
			$this->__set( 'post_parent', 0 );
		}
		$this->__unset( 'children' );
	}

	/**
	 * Specific Download
	 *
	 * @since 1.8
	 * @return void
	 */
	public function download() {

		if ( empty( $this->args['download'] ) )
			return;

		global $edd_logs;

		$args = array(
			'post_parent'            => $this->args['download'],
			'log_type'               => 'sale',
			'post_status'            => array( 'publish' ),
			'nopaging'               => true,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'cache_results'          => false,
			'fields'                 => 'ids'
		);

		if ( is_array( $this->args['download'] ) ) {
			unset( $args['post_parent'] );
			$args['post_parent__in'] = $this->args['download'];
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

	/**
	 * Based off the current global variables for $wp_query and $post, we may need to reset some data or just restore it.
	 *
	 * @since 2.8
	 * @access private
	 * @return void
	 */
	private function maybe_reset_globals() {
		// Based off our pre-iteration, let's reset the globals.
		if ( $this->existing_query ) {
			wp_reset_postdata();
		} elseif ( $this->existing_post ) {
			$GLOBALS['post'] = $this->existing_post;
		} else {
			unset( $GLOBALS['post'] );
		}
	}
}
