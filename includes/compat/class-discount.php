<?php
/**
 * Backwards Compatibility Handler for Discounts.
 *
 * @package     EDD
 * @subpackage  Compat
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Compat;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Customer Class.
 *
 * @since 3.0
 */
class Discount extends Base {

	/**
	 * Holds the component for which we are handling back-compat. There is a chance that two methods have the same name
	 * and need to be dispatched to completely other methods. When a new instance of Back_Compat is created, a component
	 * can be passed to the constructor which will allow __call() to dispatch to the correct methods.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $component = 'discount';

	/**
	 * Backwards compatibility hooks for discounts.
	 *
	 * @since 3.0
	 * @access protected
	 */
	protected function hooks() {

		/** Actions **********************************************************/
		add_action( 'pre_get_posts',        array( $this, 'pre_get_posts'        ), 99, 1 );

		/** Filters **********************************************************/
		add_filter( 'query',                array( $this, 'wp_count_posts'       ), 10, 1 );
		add_filter( 'get_post_metadata',    array( $this, 'get_post_metadata'    ), 99, 4 );
		add_filter( 'update_post_metadata', array( $this, 'update_post_metadata' ), 99, 5 );
		add_filter( 'add_post_metadata',    array( $this, 'update_post_metadata' ), 99, 5 );
		add_filter( 'posts_results',        array( $this, 'posts_results'        ), 10, 2 );
		add_filter( 'posts_request',        array( $this, 'posts_request'        ), 10, 2 );
	}

	/**
	 * Add a message for anyone to trying to get payments via get_post/get_posts/WP_Query.
	 * Force filters to run for all queries that have `edd_discount` as the post type.
	 *
	 * This is here for backwards compatibility purposes with the migration to custom tables in EDD 3.0.
	 *
	 * @since 3.0
	 *
	 * @param \WP_Query $query
	 */
	public function pre_get_posts( $query ) {
		global $wpdb;

		// Bail if not a discount
		if ( 'edd_discount' !== $query->get( 'post_type' ) ) {
			return;
		}

		// Force filters to run
		$query->set( 'suppress_filters', false );

		// Setup doing-it-wrong message
		$message = sprintf(
			__( 'As of Easy Digital Downloads 3.0, discounts no longer exist in the %1$s table. They have been migrated to %2$s. Discounts should be accessed using %3$s, %4$s or instantiating a new instance of %5$s. See %6$s for more information.', 'easy-digital-downloads' ),
			'<code>' . $wpdb->posts . '</code>',
			'<code>' . edd_get_component_interface( 'adjustment', 'table' )->table_name . '</code>',
			'<code>edd_get_discounts()</code>',
			'<code>edd_get_discount()</code>',
			'<code>EDD_Discount</code>',
			'https://easydigitaldownloads.com/development/'
		);

		_doing_it_wrong( 'get_posts()/get_post()', $message, '3.0' );
	}

	/**
	 * Backwards compatibility layer for wp_count_posts().
	 *
	 * @since 3.0
	 *
	 * @param string $query SQL query.
	 * @return string $request Rewritten SQL query.
	 */
	public function wp_count_posts( $query ) {
		global $wpdb;

		$expected = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type = 'edd_discount' GROUP BY post_status";

		if ( $expected === $query ) {
			$query = "SELECT status AS post_status, COUNT( * ) AS num_posts FROM {$wpdb->edd_adjustments} WHERE type = 'discount' GROUP BY post_status";
		}

		return $query;
	}

	/**
	 * Fill the returned WP_Post objects with the data from the discounts table.
	 *
	 * @since 3.0
	 *
	 * @param array     $posts Posts returned from the SQL query.
	 * @param \WP_Query $query Instance of WP_Query.
	 *
	 * @return array New WP_Post objects.
	 */
	public function posts_results( $posts, $query ) {
		if ( 'posts_results' !== current_filter() ) {
			$message = __( 'This function is not meant to be called directly. It is only here for backwards compatibility purposes.', 'easy-digital-downloads' );
			_doing_it_wrong( __FUNCTION__, esc_html( $message ), 'EDD 3.0' );
		}

		if ( 'edd_discount' === $query->get( 'post_type' ) ) {
			$new_posts = array();

			foreach ( $posts as $post ) {
				$discount = edd_get_discount( $post->id );

				$object_vars = array(
					'ID'                => $discount->id,
					'post_title'        => $discount->name,
					'post_status'       => $discount->status,
					'post_type'         => 'edd_discount',
					'post_date'         => EDD()->utils->date( $discount->date_created, null, true )->toDateTimeString(),
					'post_date_gmt'     => $discount->date_created,
					'post_modified'     => EDD()->utils->date( $discount->date_modified, null, true )->toDateTimeString(),
					'post_modified_gmt' => $discount->date_created,
				);

				foreach ( $object_vars as $object_var => $value ) {
					$post->{$object_var} = $value;
				}

				$post = new \WP_Post( $post );

				$new_posts[] = $post;
			}

			return $new_posts;
		}

		return $posts;
	}

	/**
	 * Hijack the SQL query and rewrite it to fetch data from the discounts table.
	 *
	 * @since 3.0
	 *
	 * @param string    $request SQL query.
	 * @param \WP_Query $query   Instance of WP_Query.
	 *
	 * @return string $request Rewritten SQL query.
	 */
	public function posts_request( $request, $query ) {
		global $wpdb;

		if ( 'posts_request' !== current_filter() ) {
			$message = __( 'This function is not meant to be called directly. It is only here for backwards compatibility purposes.', 'easy-digital-downloads' );
			_doing_it_wrong( __FUNCTION__, esc_html( $message ), '3.0' );
		}

		if ( 'edd_discount' === $query->get( 'post_type' ) ) {
			$defaults = array(
				'number'  => 30,
				'status'  => array( 'active', 'inactive', 'expired' ),
				'order'   => 'DESC',
				'orderby' => 'date_created',
			);

			$args = array(
				'number' => $query->get( 'posts_per_page' ),
				'status' => $query->get( 'post_status', array( 'active', 'inactive' ) ),
			);

			$orderby = $query->get( 'orderby', false );
			if ( $orderby ) {
				switch ( $orderby ) {
					case 'none':
					case 'ID':
					case 'author':
					case 'post__in':
					case 'type':
					case 'post_type':
						$args['orderby'] = 'id';
						break;
					case 'title':
						$args['orderby'] = 'name';
						break;
					case 'date':
					case 'post_date':
					case 'modified':
					case 'post_modified':
						$args['orderby'] = 'date_created';
						break;
					default:
						$args['orderby'] = 'id';
						break;
				}
			}

			$offset = $query->get( 'offset', false );
			if ( $offset ) {
				$args['offset'] = absint( $offset );
			} else {
				$args['offset'] = 0;
			}

			if ( 'any' === $args['status'] ) {
				$args['status'] = $defaults['status'];
			}

			$args = wp_parse_args( $args, $defaults );

			if ( array_key_exists( 'number', $args ) ) {
				$args['number'] = absint( $args['number'] );
			}

			$table_name = edd_get_component_interface( 'adjustment', 'table' )->table_name;

			$meta_query = $query->get( 'meta_query' );

			$clauses   = array();
			$sql_where = "WHERE type = 'discount'";

			$meta_key   = $query->get( 'meta_key',   false );
			$meta_value = $query->get( 'meta_value', false );
			$columns    = wp_list_pluck( edd_get_component_interface( 'adjustment', 'schema' )->columns, 'name' );

			// 'meta_key' and 'meta_value' passed as arguments
			if ( $meta_key && $meta_value ) {
				/**
				 * Check that the key exists as a column in the table.
				 * Note: there is no backwards compatibility support for product requirements and excluded
				 * products as these would be serialized under the old schema.
				 */
				if ( in_array( $meta_key, $columns, true ) ) {
					$sql_where .= ' ' . $wpdb->prepare( "{$meta_key} = %s", $meta_value );
				}
			}

			if ( ! empty( $meta_query ) ) {
				foreach ( $meta_query as $key => $query ) {
					$relation = 'AND'; // Default relation

					if ( is_string( $query ) && 'relation' === $key ) {
						$relation = $query;
					}

					if ( is_array( $query ) ) {
						if ( array_key_exists( 'key', $query ) ) {
							$query['key'] = str_replace( '_edd_discount_', '', $query['key'] );

							/**
							 * Check that the key exists as a column in the table.
							 * Note: there is no backwards compatibility support for product requirements and excluded
							 * products as these would be serialized under the old schema.
							 */
							if ( in_array( $query['key'], $columns, true ) && array_key_exists( 'value', $query ) ) {
								$meta_compare = ! empty( $query['compare'] ) ? $query['compare'] : '=';
								$meta_compare = strtoupper( $meta_compare );

								$meta_value = $query['value'];

								$where = null;

								switch ( $meta_compare ) {
									case 'IN':
									case 'NOT IN':
										$meta_compare_string = '(' . substr( str_repeat( ',%s', count( $meta_value ) ), 1 ) . ')';
										$where = $wpdb->prepare( $meta_compare_string, $meta_value );
										break;

									case 'BETWEEN':
									case 'NOT BETWEEN':
										$meta_value = array_slice( $meta_value, 0, 2 );
										$where      = $wpdb->prepare( '%1$s AND %1$s', $meta_value );
										break;

									case 'LIKE':
									case 'NOT LIKE':
										$meta_value = '%' . $wpdb->esc_like( $meta_value ) . '%';
										$where      = $wpdb->prepare( '%s', $meta_value );
										break;

									// EXISTS with a value is interpreted as '='.
									case 'EXISTS':
										$where = $wpdb->prepare( '%s', $meta_value );
										break;

									// 'value' is ignored for NOT EXISTS.
									case 'NOT EXISTS':
										$where = $query['key'] . ' IS NULL';
										break;

									default:
										$where = $wpdb->prepare( '%s', $meta_value );
										break;
								}

								if ( ! is_null( $where ) ) {
									$clauses['where'][] = $query['key'] . ' ' . $meta_compare . ' ' . $where;
								}
							}
						}

						if ( ! empty( $clauses['where'] ) && is_array( $clauses['where'] ) ) {
							$sql_where .= ' AND ( ' . implode( ' ' . $relation . ' ', $clauses['where'] ) . ' )';
						}
					}
				}
			}

			$request = "SELECT id FROM {$table_name} {$sql_where} ORDER BY {$args['orderby']} {$args['order']} LIMIT {$args['offset']}, {$args['number']};";
		}

		return $request;
	}

	/**
	 * Backwards compatibility filters for get_post_meta() calls on discounts.
	 *
	 * @since 3.0
	 *
	 * @param  mixed  $value       The value get_post_meta would return if we don't filter.
	 * @param  int    $object_id   The object ID post meta was requested for.
	 * @param  string $meta_key    The meta key requested.
	 * @param  bool   $single      If a single value or an array of the value is requested.
	 *
	 * @return mixed The value to return.
	 */
	public function get_post_metadata( $value, $object_id, $meta_key, $single ) {

		$meta_keys = apply_filters( 'edd_post_meta_discount_backwards_compat_keys', array(
			'_edd_discount_status',
			'_edd_discount_amount',
			'_edd_discount_uses',
			'_edd_discount_name',
			'_edd_discount_code',
			'_edd_discount_expiration',
			'_edd_discount_start',
			'_edd_discount_is_single_use',
			'_edd_discount_is_not_global',
			'_edd_discount_product_condition',
			'_edd_discount_min_price',
			'_edd_discount_max_uses'
		) );

		// Bail early of not a back-compat key
		if ( ! in_array( $meta_key, $meta_keys, true ) ) {
			return $value;
		}

		// Bail if discount does not exist
		$discount = edd_get_discount( $object_id );
		if ( empty( $discount->id ) ) {
			return $value;
		}

		switch ( $meta_key ) {
			case '_edd_discount_name':
			case '_edd_discount_status':
			case '_edd_discount_amount':
			case '_edd_discount_uses':
			case '_edd_discount_code':
			case '_edd_discount_expiration':
			case '_edd_discount_start':
			case '_edd_discount_product_condition':
			case '_edd_discount_min_price':
			case '_edd_discount_max_uses':
				$key = str_replace( '_edd_discount_', '', $meta_key );

				$value = $discount->{$key};

				if ( $this->show_notices ) {
					_doing_it_wrong( 'get_post_meta()', 'All discount postmeta has been <strong>deprecated</strong> since Easy Digital Downloads 3.0! Use <code>edd_get_adjustment_meta()</code> instead.', 'EDD 3.0' );

					if ( $this->show_backtrace ) {
						$backtrace = debug_backtrace();
						trigger_error( print_r( $backtrace, 1 ) );
					}
				}

				break;

			case '_edd_discount_is_single_use':
				$value = $discount->get_once_per_customer();

				if ( $this->show_notices ) {
					_doing_it_wrong( 'get_post_meta()', 'All discount postmeta has been <strong>deprecated</strong> since Easy Digital Downloads 3.0! Use <code>edd_get_adjustment_meta()</code> instead.', 'EDD 3.0' );

					if ( $this->show_backtrace ) {
						$backtrace = debug_backtrace();
						trigger_error( print_r( $backtrace, 1 ) );
					}
				}

				break;

			case '_edd_discount_is_not_global':
				$value = $discount->get_scope();

				if ( $this->show_notices ) {
					_doing_it_wrong( 'get_post_meta()', 'All discount postmeta has been <strong>deprecated</strong> since Easy Digital Downloads 3.0! Use <code>edd_get_adjustment_meta()</code> instead.', 'EDD 3.0' );

					if ( $this->show_backtrace ) {
						$backtrace = debug_backtrace();
						trigger_error( print_r( $backtrace, 1 ) );
					}
				}

				break;
			default:
				/*
				 * Developers can hook in here with add_filter( 'edd_get_post_meta_discount_backwards_compat-meta_key... in order to
				 * Filter their own meta values for backwards compatibility calls to get_post_meta instead of EDD_Discount::get_meta
				 */
				$value = apply_filters( 'edd_get_post_meta_discount_backwards_compat-' . $meta_key, $value, $object_id );
				break;
		}

		return $value;
	}


	/**
	 * Backwards compatibility filters for add/update_post_meta() calls on discounts.
	 *
	 * @since 3.0
	 *
	 * @param mixed  $check      Comes in 'null' but if returned not null, WordPress Core will not interact with the postmeta table.
	 * @param int    $object_id  The object ID post meta was requested for.
	 * @param string $meta_key   The meta key requested.
	 * @param mixed  $meta_value The value get_post_meta would return if we don't filter.
	 * @param mixed  $prev_value The previous value of the meta
	 *
	 * @return mixed Returns 'null' if no action should be taken and WordPress core can continue, or non-null to avoid postmeta.
	 */
	public function update_post_metadata( $check, $object_id, $meta_key, $meta_value, $prev_value ) {

		$meta_keys = apply_filters( 'edd_update_post_meta_discount_backwards_compat_keys', array(
			'_edd_discount_status',
			'_edd_discount_amount',
			'_edd_discount_uses',
			'_edd_discount_name',
			'_edd_discount_code',
			'_edd_discount_expiration',
			'_edd_discount_start',
			'_edd_discount_is_single_use',
			'_edd_discount_is_not_global',
			'_edd_discount_product_condition',
			'_edd_discount_min_price',
			'_edd_discount_max_uses'
		) );

		// Bail early of not a back-compat key
		if ( ! in_array( $meta_key, $meta_keys, true ) ) {
			return $check;
		}

		// Bail if discount does not exist
		$discount = edd_get_discount( $object_id );
		if ( empty( $discount->id ) ) {
			return $check;
		}

		switch ( $meta_key ) {
			case '_edd_discount_name':
			case '_edd_discount_status':
			case '_edd_discount_amount':
			case '_edd_discount_uses':
			case '_edd_discount_code':
			case '_edd_discount_expiration':
			case '_edd_discount_start':
			case '_edd_discount_product_condition':
			case '_edd_discount_min_price':
			case '_edd_discount_max_uses':
				$key              = str_replace( '_edd_discount_', '', $meta_key );
				$discount->{$key} = $meta_value;
				$check            = $discount->save();

				if ( $this->show_notices ) {
					_doing_it_wrong( 'add_post_meta()/update_post_meta()', 'All discount postmeta has been <strong>deprecated</strong> since Easy Digital Downloads 3.0! Use <code>edd_add_adjustment_meta()/edd_update_adjustment_meta()</code> instead.', 'EDD 3.0' );

					if ( $this->show_backtrace ) {
						$backtrace = debug_backtrace();
						trigger_error( print_r( $backtrace, 1 ) );
					}
				}

				break;
			case '_edd_discount_is_single_use':
				$discount->once_per_customer = $meta_value;
				$check                       = $discount->save();

				// Since the old discounts data was simply stored in a single post meta entry, just don't let it be added.
				if ( $this->show_notices ) {
					_doing_it_wrong( 'add_post_meta()/update_post_meta()', 'All discount postmeta has been <strong>deprecated</strong> since Easy Digital Downloads 3.0! Use <code>edd_add_adjustment_meta()/edd_update_adjustment_meta()</code> instead.', 'EDD 3.0' );

					if ( $this->show_backtrace ) {
						$backtrace = debug_backtrace();
						trigger_error( print_r( $backtrace, 1 ) );
					}
				}

				break;
			case '_edd_discount_is_not_global':
				$discount->scope = $meta_value;
				$check           = $discount->save();

				// Since the old discounts data was simply stored in a single post meta entry, just don't let it be added.
				if ( $this->show_notices ) {
					_doing_it_wrong( 'add_post_meta()/update_post_meta()', 'All discount postmeta has been <strong>deprecated</strong> since Easy Digital Downloads 3.0! Use <code>edd_add_adjustment_meta()/edd_update_adjustment_meta()</code> instead.', 'EDD 3.0' );

					if ( $this->show_backtrace ) {
						$backtrace = debug_backtrace();
						trigger_error( print_r( $backtrace, 1 ) );
					}
				}

				break;
			default:
				/*
				 * Developers can hook in here with add_filter( 'edd_get_post_meta_discount_backwards_compat-meta_key... in order to
				 * Filter their own meta values for backwards compatibility calls to get_post_meta instead of EDD_Discount::get_meta
				 */
				$check = apply_filters( 'edd_update_post_meta_discount_backwards_compat-' . $meta_key, $check, $object_id, $meta_value, $prev_value );
				break;
		}

		return $check;

	}
}
