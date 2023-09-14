<?php
/**
 * Payments Query
 *
 * @package     EDD
 * @subpackage  Payments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Payments_Query Class.
 *
 * This class is for retrieving payments data.
 *
 * Payments can be retrieved for date ranges and pre-defined periods.
 *
 * @since 1.8
 * @since 3.0 Updated to use the new query classes and custom tables.
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
	 * Items returned from query.
	 *
	 * @since 3.0
	 * @var   array|null
	 */
	private $items = array();

	/**
	 * Default query arguments.
	 *
	 * Not all of these are valid arguments that can be passed to WP_Query. The ones that are not, are modified before
	 * the query is run to convert them to the proper syntax.
	 *
	 * @since 1.8
	 * @since 3.0 Updated to use the new query classes and custom tables.
	 *
	 * @param array $args The array of arguments that can be passed in and used for setting up this payment query.
	 */
	public function __construct( $args = array() ) {
		$defaults = array(
			'output'          => 'payments', // Use 'posts' to get standard post objects
			'post_type'       => array( 'edd_payment' ),
			'post_parent'     => null,
			'start_date'      => false,
			'end_date'        => false,
			'number'          => 20,
			'page'            => null,
			'orderby'         => 'ID',
			'order'           => 'DESC',
			'user'            => null,
			'customer'        => null,
			'status'          => edd_get_payment_status_keys(),
			'mode'            => null,
			'type'            => 'sale',
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
			'post__not_in'    => null,
			'compare'         => null,
			'country'         => null,
			'region'          => null,
		);

		$this->initial_args = $args;

		// We need to store an array of the args used to instantiate the class, so that we can use it in later hooks.
		$this->args = wp_parse_args( $args, $defaults );

		// In EDD 3.0 we switched from 'publish' to 'complete' for the final state of a completed payment, this accounts for that change.
		if ( is_array( $this->args['status'] ) && in_array( 'publish', $this->args['status'] ) ) {

			foreach ( $this->args['status'] as $key => $status ) {
				if ( $status === 'publish' ) {
					unset( $this->args['status'][ $key ] );
				}
			}

			$this->args['status'][] = 'complete';

		} else if ( 'publish' === $this->args['status'] ) {

			$this->args['status'] = 'complete';

		}
	}

	/**
	 * Set a query variable.
	 *
	 * @since 1.8
	 */
	public function __set( $query_var, $value ) {
		if ( in_array( $query_var, array( 'meta_query', 'tax_query' ), true ) ) {
			$this->args[ $query_var ][] = $value;
		} else {
			$this->args[ $query_var ] = $value;
		}
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
	 * Retrieve payments.
	 *
	 * The query can be modified in two ways; either the action before the
	 * query is run, or the filter on the arguments (existing mainly for backwards
	 * compatibility).
	 *
	 * @since 1.8
	 * @since 3.0 Updated to use the new query classes and custom tables.
	 *
	 * @return EDD_Payment[]|EDD\Orders\Order[]|int
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

		$should_output_wp_post_objects = false;
		$should_output_order_objects   = false;

		if ( 'posts' === $this->args['output'] ) {
			$should_output_wp_post_objects = true;
		} elseif ( 'orders' === $this->args['output'] ) {
			$should_output_order_objects = true;
		}

		$this->remap_args();

		// Check if $items is null after parsing the query.
		if ( null === $this->items ) {
			return array();
		}

		$this->items = edd_get_orders( $this->args );

		if ( ! empty( $this->args['count'] ) && is_numeric( $this->items ) ) {
			return intval( $this->items );
		}

		if ( $should_output_order_objects || ! empty( $this->args['fields'] ) ) {
			return $this->items;
		}

		if ( $should_output_wp_post_objects ) {
			$posts = array();

			foreach ( $this->items as $order ) {
				$p = new WP_Post( new stdClass() );

				$p->ID                = $order->id;
				$p->post_date         = EDD()->utils->date( $order->date_created, null, true )->toDateTimeString();
				$p->post_date_gmt     = $order->date_created;
				$p->post_status       = $order->status;
				$p->post_modified     = EDD()->utils->date( $order->date_modified, null, true )->toDateTimeString();
				$p->post_modified_gmt = $order->date_modified;
				$p->post_type         = 'edd_payment';

				$posts[] = $p;
			}

			return $posts;
		}

		foreach ( $this->items as $order ) {
			$payment = edd_get_payment( $order->id );

			if ( edd_get_option( 'enable_sequential' ) ) {
				// Backwards compatibility, needs to set `payment_number` attribute
				$payment->payment_number = $payment->number;
			}

			$this->payments[] = apply_filters( 'edd_payment', $payment, $order->id, $this );
		}

		do_action( 'edd_post_get_payments', $this );

		return $this->payments;
	}

	/**
	 * If querying a specific date, add the proper filters.
	 *
	 * @since 1.8
	 */
	public function date_filter_pre() {
		if ( ! ( $this->args['start_date'] || $this->args['end_date'] ) ) {
			return;
		}

		$this->setup_dates( $this->args['start_date'], $this->args['end_date'] );
	}

	/**
	 * Post Status
	 *
	 * @since 1.8
	 */
	public function status() {
		if ( ! isset( $this->args['status'] ) ) {
			return;
		}

		$this->__set( 'post_status', $this->args['status'] );
		$this->__unset( 'status' );
	}

	/**
	 * Current Page
	 *
	 * @since 1.8
	 */
	public function page() {
		if ( ! isset( $this->args['page'] ) ) {
			return;
		}

		$this->__set( 'paged', $this->args['page'] );
		$this->__unset( 'page' );
	}

	/**
	 * Posts Per Page
	 *
	 * @since 1.8
	 */
	public function per_page() {
		if ( ! isset( $this->args['number'] ) ) {
			return;
		}

		if ( - 1 === $this->args['number'] ) {
			$this->__set( 'nopaging', true );
		} else {
			$this->__set( 'posts_per_page', $this->args['number'] );
		}

		$this->__unset( 'number' );
	}

	/**
	 * Current Month
	 *
	 * @since 1.8
	 */
	public function month() {
		if ( ! isset( $this->args['month'] ) ) {
			return;
		}

		$this->__set( 'monthnum', $this->args['month'] );
		$this->__unset( 'month' );
	}

	/**
	 * Order by
	 *
	 * @since 1.8
	 */
	public function orderby() {
		switch ( $this->args['orderby'] ) {
			case 'amount':
				$this->__set( 'orderby', 'meta_value_num' );
				$this->__set( 'meta_key', '_edd_payment_total' );
				break;
			default:
				$this->__set( 'orderby', $this->args['orderby'] );
				break;
		}
	}

	/**
	 * Specific User
	 *
	 * @since 1.8
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
			'value' => $this->args['user'],
		) );
	}

	/**
	 * Specific customer id
	 *
	 * @since 2.6
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
	 * @since 2.8
	 */
	public function gateway() {
		if ( is_null( $this->args['gateway'] ) ) {
			return;
		}

		$this->__set( 'meta_query', array(
			'key'   => '_edd_payment_gateway',
			'value' => $this->args['gateway'],
		) );
	}

	/**
	 * Specific payments
	 *
	 * @since 2.8.7
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
	 */
	public function search() {
		if ( ! isset( $this->args['s'] ) ) {
			return;
		}

		$search = trim( $this->args['s'] );

		if ( empty( $search ) ) {
			return;
		}

		$is_email = is_email( $search ) || strpos( $search, '@' ) !== false;
		$is_user  = strpos( $search, strtolower( 'user:' ) ) !== false;


		if ( ! empty( $this->args['search_in_notes'] ) ) {
			$notes = edd_get_payment_notes( 0, $search );

			if ( ! empty( $notes ) ) {
				$payment_ids = wp_list_pluck( (array) $notes, 'object_id' );

				// Set post__in for backwards compatibility purposes.
				$this->__set( 'post__in', $payment_ids );
			}

			$this->__unset( 's' );
		} elseif ( $is_email || 32 === strlen( $search ) ) {
			$key = $is_email
				? 'email'
				: 'payment_key';

			if ( 'email' === $key ) {
				$this->__set( 'user', $search );
			} else {
				$this->__set( 'payment_key', $search );
			}

			$this->__unset( 's' );
		} elseif ( $is_user ) {
			$this->__set( 'user', trim( str_replace( 'user:', '', strtolower( $search ) ) ) );

			$this->__unset( 's' );
		} elseif ( edd_get_option( 'enable_sequential' ) && ( false !== strpos( $search, edd_get_option( 'sequential_prefix' ) ) || false !== strpos( $search, edd_get_option( 'sequential_postfix' ) ) ) ) {
			$this->__set( 'order_number', $search );
			$this->__unset( 's' );
		} elseif ( is_numeric( $search ) ) {
			$this->__set( 'post__in', array( $search ) );

			if ( edd_get_option( 'enable_sequential' ) ) {
				$this->__set( 'order_number', $search );
			}

			$this->__unset( 's' );
		} elseif ( '#' === substr( $search, 0, 1 ) ) {
			$search = str_replace( '#:', '', $search );
			$search = str_replace( '#', '', $search );

			$ids = edd_get_order_items( array(
				'fields'     => 'order_id',
				'product_id' => $search,
			) );

			$this->__set( 'post__in', array_values( $ids ) );

			$this->__unset( 's' );
		} elseif ( 0 === strpos( $search, 'discount:' ) ) {
			$search = trim( str_replace( 'discount:', '', $search ) );

			$ids = edd_get_order_adjustments( array(
				'fields'      => 'object_id',
				'type'        => 'discount',
				'description' => $search,
			) );

			$this->__set( 'post__in', array_values( $ids ) );
			$this->__unset( 's' );
		} else {
			$this->__set( 's', $search );
		}
	}

	/**
	 * Payment Mode
	 *
	 * @since 1.8
	 */
	public function mode() {
		if ( empty( $this->args['mode'] ) || 'all' === $this->args['mode'] ) {
			$this->__unset( 'mode' );

			return;
		}

		$this->__set( 'meta_query', array(
			'key'   => '_edd_payment_mode',
			'value' => $this->args['mode'],
		) );
	}

	/**
	 * Children
	 *
	 * @since 1.8
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
	 */
	public function download() {
		if ( empty( $this->args['download'] ) ) {
			return;
		}

		$order_ids = array();

		if ( is_array( $this->args['download'] ) ) {
			$order_items = edd_get_order_items( array(
				'product_id__in' => (array) $this->args['download'],
			) );

			foreach ( $order_items as $order_item ) {
				/** @var $order_item EDD\Orders\Order_Item */
				$order_ids[] = $order_item->order_id;
			}
		} else {
			$order_items = edd_get_order_items( array(
				'product_id' => $this->args['download'],
			) );

			foreach ( $order_items as $order_item ) {
				/** @var $order_item EDD\Orders\Order_Item */
				$order_ids[] = $order_item->order_id;
			}
		}

		$this->args['id__in'] = $order_ids;

		$this->__unset( 'download' );
	}

	/**
	 * As of EDD 3.0, we have introduced new query classes and custom tables so we need to remap the arguments so we can
	 * pass them to the new query classes.
	 *
	 * @since  3.0
	 * @access private
	 */
	private function remap_args() {
		global $wpdb;

		$arguments = array();

		// Check for post_parent
		if ( isset( $this->initial_args['post_parent'] ) ) {
			$arguments['parent'] = absint( $this->initial_args['post_parent'] );
		}

		// Meta key and value
		if ( isset( $this->initial_args['meta_query'] ) ) {
			$arguments['meta_query'] = $this->initial_args['meta_query'];
		} elseif ( isset( $this->initial_args['meta_key'] ) ) {
			$meta_query = array(
				'key' => $this->initial_args['meta_key']
			);

			if ( isset( $this->initial_args['meta_value'] ) ) {
				$meta_query['value'] = $this->initial_args['meta_value'];
			}

			$arguments['meta_query'] = array( $meta_query );
		}

		foreach ( array( 'year', 'month', 'week', 'day', 'hour', 'minute', 'second' ) as $date_interval ) {
			if ( isset( $this->initial_args[ $date_interval ] ) ) {
				$arguments['date_created_query'][ $date_interval ] = $this->initial_args[ $date_interval ];
			}
		}

		if ( $this->args['start_date'] ) {
			if ( is_numeric( $this->start_date ) ) {
				$this->start_date = \EDD\Utils\Date::createFromTimestamp( $this->start_date )->toDateTimeString();
			}

			$this->start_date = \EDD\Utils\Date::parse( $this->start_date, edd_get_timezone_id() )->setTimezone( 'UTC' )->timestamp;

			$arguments['date_created_query']['after'] = array(
				'year'  => date( 'Y', $this->start_date ),
				'month' => date( 'm', $this->start_date ),
				'day'   => date( 'd', $this->start_date ),
			);

			$arguments['date_created_query']['inclusive'] = true;
		}

		if ( $this->args['end_date'] ) {
			if ( is_numeric( $this->end_date ) ) {
				$this->end_date = \EDD\Utils\Date::createFromTimestamp( $this->end_date )->toDateTimeString();
			}

			$this->end_date = \EDD\Utils\Date::parse( $this->end_date, edd_get_timezone_id() )->setTimezone( 'UTC' )->timestamp;

			$arguments['date_created_query']['before'] = array(
				'year'  => date( 'Y', $this->end_date ),
				'month' => date( 'm', $this->end_date ),
				'day'   => date( 'd', $this->end_date ),
			);

			$arguments['date_created_query']['inclusive'] = true;
		}

		if ( isset( $this->initial_args['number'] ) ) {
			if ( -1 == $this->initial_args['number'] ) {
				_doing_it_wrong( __FUNCTION__, esc_html__( 'Do not use -1 to retrieve all results.', 'easy-digital-downloads' ), '3.0' );
				$this->args['nopaging'] = true;
			} else {
				$arguments['number'] = $this->initial_args['number'];
			}
		}

		$arguments['number'] = isset( $this->args['posts_per_page'] )
			? $this->args['posts_per_page']
			: 20;

		if ( isset( $this->args['nopaging'] ) && true === $this->args['nopaging'] ) {
			// Setting to a really large number because we don't actually have a way to get all results.
			$arguments['number'] = 9999999;
		}

		switch ( $this->args['orderby'] ) {
			case 'amount':
				$arguments['orderby'] = 'total';
				break;
			case 'ID':
			case 'title':
			case 'post_title':
			case 'author':
			case 'post_author':
			case 'type':
			case 'post_type':
				$arguments['orderby'] = 'id';
				break;
			case 'date':
			case 'post_date':
				$arguments['orderby'] = 'date_created';
				break;
			case 'modified':
			case 'post_modified':
				$arguments['orderby'] = 'date_modified';
				break;
			case 'parent':
			case 'post_parent':
				$arguments['orderby'] = 'parent';
				break;
			case 'post__in':
				$arguments['orderby'] = 'id__in';
				break;
			case 'post_parent__in':
				$arguments['orderby'] = 'parent__in';
				break;
			default:
				$arguments['orderby'] = $this->args['orderby'];
				break;
		}

		if ( ! is_null( $this->args['user'] ) ) {
			$argument_key = is_numeric( $this->args['user'] )
				? 'user_id'
				: 'email';

			$arguments[ $argument_key ] = $this->args['user'];
		}

		if ( ! is_null( $this->args['customer'] ) && is_numeric( $this->args['customer'] ) ) {
			$arguments['customer_id'] = (int) $this->args['customer'];
		}

		if ( ! is_null( $this->args['gateway'] ) ) {
			$arguments['gateway'] = $this->args['gateway'];
		}

		if ( ! is_null( $this->args['post__in'] ) ) {
			$arguments['id__in'] = $this->args['post__in'];
		}

		if ( ! is_null( $this->args['post__not_in'] ) ) {
			$arguments['id__not_in'] = $this->args['post__not_in'];
		}

		if ( ! empty( $this->args['mode'] ) && 'all' !== $this->args['mode'] ) {
			$arguments['mode'] = $this->args['mode'];
		}

		if ( ! empty( $this->args['type'] ) ) {
			$arguments['type'] = $this->args['type'];
		}

		if ( ! empty( $this->args['s'] ) ) {
			$arguments['search'] = $this->args['s'];
		}

		if ( ! empty( $this->args['post_parent'] ) ) {
			$this->args['parent'] = $this->args['post_parent'];
		}

		if ( ! empty( $this->args['offset'] ) ) {
			$arguments['offset'] = $this->args['offset'];
		} elseif ( isset( $this->args['paged'] ) && isset( $this->args['posts_per_page'] ) ) {
			$arguments['offset'] = ( $this->args['paged'] * $this->args['posts_per_page'] ) - $this->args['posts_per_page'];
		}

		if ( isset( $this->args['count'] ) ) {
			$arguments['count'] = (bool) $this->args['count'];
			unset( $arguments['number'] );
		}

		if ( isset( $this->args['groupby'] ) ) {
			$arguments['groupby'] = $this->args['groupby'];
		}

		if ( isset( $this->args['order'] ) ) {
			$arguments['order'] = $this->args['order'];
		}

		if ( isset( $this->args['compare'] ) && is_array( $this->args['compare'] ) ) {
			$arguments['compare'] = $this->args['compare'];
		}

		// Re-map post_status to status.
		if ( isset( $this->args['post_status'] ) ) {
			$arguments['status'] = $this->args['post_status'];
		}

		// If the status includes `any`, we should set the status to our whitelisted keys.
		if ( isset( $arguments['status'] ) && ( 'any' === $arguments['status'] || ( is_array( $arguments['status'] ) && in_array( 'any', $arguments['status'], true ) ) ) ) {
			$arguments['status'] = edd_get_payment_status_keys();
		}

		if ( isset( $arguments['meta_query'] ) && is_array( $arguments['meta_query'] ) ) {
			foreach ( $arguments['meta_query'] as $meta_index => $meta ) {
				if ( ! empty( $meta['key'] ) ) {
					switch ( $meta['key'] ) {
						case '_edd_payment_customer_id':
							$arguments['customer_id'] = absint( $meta['value'] );
							unset( $arguments['meta_query'][ $meta_index ] );
							break;

						case '_edd_payment_user_id':
							$arguments['user_id'] = absint( $meta['value'] );
							unset( $arguments['meta_query'][ $meta_index ] );
							break;

						case '_edd_payment_user_email':
							$arguments['email'] = sanitize_email( $meta['value'] );
							unset( $arguments['meta_query'][ $meta_index ] );
							break;

						case '_edd_payment_gateway':
							$arguments['gateway'] = sanitize_text_field( $meta['value'] );
							unset( $arguments['meta_query'][ $meta_index ] );
							break;

						case '_edd_payment_purchase_key' :
							$arguments['payment_key'] = sanitize_text_field( $meta['value'] );
							unset( $arguments['meta_query'][ $meta_index ] );
							break;
					}
				}
			}
		}

		if ( isset( $this->args['id__in'] ) ) {
			$arguments['id__in'] = $this->args['id__in'];
		}

		if ( isset( $arguments['status'] ) && is_array( $arguments['status'] ) ) {
			$arguments['status__in'] = $arguments['status'];
			unset( $arguments['status'] );
		}

		if ( isset( $this->args['country'] ) && ! empty( $this->args['country'] ) && 'all' !== $this->args['country'] ) {
			$country = $wpdb->prepare( 'AND edd_oa.country = %s', esc_sql( $this->args['country'] ) );
			$region  = ! empty( $this->args['region'] ) && 'all' !== $this->args['region']
				? $wpdb->prepare( 'AND edd_oa.region = %s', esc_sql( $this->args['region'] ) )
				: '';
			$join    = "INNER JOIN {$wpdb->edd_order_addresses} edd_oa ON edd_o.id = edd_oa.order_id";

			$date_query = '';

			if ( ! empty( $this->start_date ) || ! empty( $this->end_date ) ) {
				$date_query = ' AND ';

				if ( ! empty( $this->start_date ) ) {
					$date_query .= $wpdb->prepare( 'edd_o.date_created >= %s', $this->start_date );
				}

				// Join dates with `AND` if start and end date set.
				if ( ! empty( $this->start_date ) && ! empty( $this->end_date ) ) {
					$date_query .= ' AND ';
				}

				if ( ! empty( $this->end_date ) ) {
					$date_query .= $wpdb->prepare( 'edd_o.date_created <= %s', $this->end_date );
				}
			}

			$gateway = ! empty( $arguments['gateway'] )
				? $wpdb->prepare( 'AND edd_o.gateway = %s', esc_sql( $arguments['gateway'] ) )
				: '';

			$mode = ! empty( $arguments['mode'] )
				? $wpdb->prepare( 'AND edd_o.mode = %s', esc_sql( $arguments['mode'] ) )
				: '';

			$sql = "
				SELECT edd_o.id
				FROM {$wpdb->edd_orders} edd_o
				{$join}
				WHERE 1=1 {$country} {$region} {$mode} {$gateway} {$date_query}
			";

			$ids = $wpdb->get_col( $sql, 0 ); // WPCS: unprepared SQL ok.

			if ( ! empty( $ids ) ) {
				$ids                 = wp_parse_id_list( $ids );
				$arguments['id__in'] = isset( $arguments['id__in'] )
					? array_merge( $ids, $arguments['id__in'] )
					: $ids;
			} else {
				$this->items = null;
			}
		}

		if ( isset( $this->args['date_query'] ) ) {
			$arguments['date_query'] = $this->args['date_query'];
		}

		if ( isset( $this->args['date_created_query'] ) ) {
			$arguments['date_created_query'] = $this->args['date_created_query'];
		}

		if ( isset( $this->args['date_modified_query'] ) ) {
			$arguments['date_modified_query'] = $this->args['date_modified_query'];
		}

		if ( isset( $this->args['date_refundable_query'] ) ) {
			$arguments['date_refundable_query'] = $this->args['date_refundable_query'];
		}

		// Make sure `fields` is honored if set (eg. 'ids').
		if ( ! empty( $this->args['fields'] ) ) {
			$arguments['fields'] = $this->args['fields'];
		}

		$this->args = $arguments;
	}
}
