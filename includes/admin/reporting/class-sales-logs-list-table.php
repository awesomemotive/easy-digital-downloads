<?php
/**
 * Sales Log Table.
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Sales_Log_Table Class.
 *
 * @since 1.4
 * @since 3.0 Updated to use the custom tables and new query classes.
 *            Updated table to display order items as sales logs have been deprecated.
 */
class EDD_Sales_Log_Table extends EDD_Base_Log_List_Table {

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 2.5
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'ID';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @since 1.4
	 *
	 * @param array  $item        Contains all the data of the log item.
	 * @param string $column_name The name of the column.
	 *
	 * @return string Column data.
	 */
	public function column_default( $item, $column_name ) {
		$return   = '';
		$currency = $item['currency'];

		switch ( $column_name ) {
			case 'download':
				$download_id = $item[ $column_name ];
				$download    = edd_get_download( $download_id );
				$price_id    = isset( $item['price_id'] ) && is_numeric( $item['price_id'] )
					? absint( $item['price_id'] )
					: null;

				$title  = $download->get_name( $price_id );
				$return = '<a href="' . esc_url( add_query_arg( 'download', urlencode( $item[ $column_name ] ) ) ) . '" >' . esc_html( $title ) . '</a>';
				break;

			case 'customer':
				$name = ! empty( $item['customer']->name )
					? $item['customer']->name
					: '<em>' . __( 'Unnamed Customer', 'easy-digital-downloads' ) . '</em>';

				$return = '<a href="' . esc_url( edd_get_admin_url( array( 'page' => 'edd-customers', 'view' => 'overview', 'id' => absint( $item['customer']->id ) ) ) ) . '">#' . esc_html( $item['customer']->id ) . ' ' . esc_html( $name ) . '</a>';
				break;

			case 'item_price':
				$return = edd_currency_filter( edd_format_amount( $item['item_price'] ), $currency );
				break;

			case 'amount':
				$return = edd_currency_filter( edd_format_amount( $item['amount'] / $item['quantity'] ), $currency );
				break;

			case 'ID':
				$return = '<a href="' . esc_url( edd_get_admin_url( array( 'page' => 'edd-payment-history', 'view' => 'view-order-details', 'id' => absint( $item['order_id'] ) ) ) ) . '">' . absint( $item['ID'] ) . '</a>';
				break;

			default:
				$return = $item[ $column_name ];
				break;
		}

		return $return;
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since 1.4
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		return array(
			'ID'         => __( 'Order Number', 'easy-digital-downloads' ),
			'customer'   => __( 'Customer', 'easy-digital-downloads' ),
			'download'   => edd_get_label_singular(),
			'amount'     => __( 'Item Amount', 'easy-digital-downloads' ),
			'date'       => __( 'Date', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Return array of query arguments.
	 *
	 * @since 3.0
	 *
	 * @param boolean $paginate
	 *
	 * @return array
	 */
	protected function get_query_args( $paginate = true ) {
		$retval = parent::get_query_args( $paginate );

		$user = $this->get_filtered_user();

		if ( $user ) {
			// Show only logs from a specific user
			$retval['user_id'] = $user;
		}

		$search = $this->get_search();
		if ( $search ) {
			if ( is_email( $search ) ) {
				$field = 'email';
			} else {
				// Look for a user
				$field = 'user_id';

				if ( ! is_numeric( $search ) ) {
					// Searching for user by username
					$user = get_user_by( 'login', $search );

					if ( $user ) {
						// Found one, set meta value to user's ID
						$search = $user->ID;
					} else {
						// No user found so let's do a real search query
						$users = new WP_User_Query( array(
							'search'         => $search,
							'search_columns' => array( 'user_url', 'user_nicename' ),
							'number'         => 1,
							'fields'         => 'ids',
						) );

						$found_user = $users->get_results();

						if ( $found_user ) {
							$search = $found_user[0];
						}
					}
				}
			}

			if ( ! $this->file_search ) {
				$retval[ $field ] = $search;
			}
		}

		return $retval;
	}

	/**
	 * Gets the log entries for the current view.
	 *
	 * @since 1.4
	 * @since 3.0 Refactored to fetch from order items table.
	 *
	 * @param array $log_query Query vars.
	 * @return array $data Array of all the sales.
	 */
	public function get_logs( $log_query = array() ) {
		$data = $order_args = array();

		// Customer ID
		if ( ! empty( $log_query['customer_id'] ) ) {
			$order_args = array(
				'customer_id'   => $log_query['customer_id'],
				'no_found_rows' => true
			);

		// Customer Email
		} elseif ( ! empty( $log_query['email'] ) ) {
			$order_args = array(
				'email'         => $log_query['email'],
				'no_found_rows' => true
			);
		}

		// Maybe query for orders first
		if ( ! empty( $order_args ) ) {
			$orders = edd_get_orders( $order_args );
			$log_query['order_id__in'] = wp_list_pluck( $orders, 'id' );
		}

		// Query order items
		$order_items = edd_get_order_items( $log_query );

		// Bail if no order items
		if ( empty( $order_items ) ) {
			return $data;
		}

		// Maybe prime orders
		if ( empty( $orders ) ) {
			$order_ids = array_values( array_unique( wp_list_pluck( $order_items, 'order_id' ) ) );

			if ( count( $order_ids ) > 2 ) {
				$orders = edd_get_orders( array(
					'id__in'        => $order_ids,
					'no_found_rows' => true
				) );
			}
		}

		// Maybe prime customers
		if ( ! empty( $orders ) ) {
			$customer_ids = array_values( array_unique( wp_list_pluck( $orders, 'customer_id' ) ) );

			if ( count( $customer_ids ) > 2 ) {
				edd_get_customers( array(
					'id__in'        => $customer_ids,
					'no_found_rows' => true
				) );
			}
		}

		// Loop through order items
		foreach ( $order_items as $order_item ) {
			$order = edd_get_order( $order_item->order_id );

			$data[] = array(
				'ID'         => $order->get_number(),
				'order_id'   => $order->id,
				'customer'   => edd_get_customer( $order->customer_id ),
				'download'   => $order_item->product_id,
				'price_id'   => $order_item->price_id,
				'item_price' => $order_item->amount,
				'amount'     => $order_item->total,
				'date'       => EDD()->utils->date( $order_item->date_created, null, true )->toDateTimeString(),
				'quantity'   => $order_item->quantity,
				'currency'   => $order->currency,
			);
		}

		return $data;
	}

	/**
	 * Get the total number of items
	 *
	 * @since 3.0
	 *
	 * @param array $log_query
	 *
	 * @return int
	 */
	public function get_total( $log_query = array() ) {
		return edd_count_order_items( $log_query );
	}
}
