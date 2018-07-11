<?php
/**
 * Sales Log View Class
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Sales_Log_Table Class
 *
 * Renders the sales log list table
 *
 * @since 1.4
 * @since 3.0 Updated to use the custom tables and new query classes.
 */
class EDD_Sales_Log_Table extends EDD_Base_Log_List_Table {

	/**
	 * Get things started
	 *
	 * @since 1.4
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct();
	}

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
	 * @param array $item Contains all the data of the log item
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		$return   = '';
		$currency = $item['currency'];

		switch ( $column_name ){
			case 'download' :
				$download_id = $item[ $column_name ];
				$download    = new EDD_Download( $download_id );

				$title = $download->post_title;
				if ( edd_has_variable_prices( $download->ID ) ) {
					$price_id = $item['price_id'];
					if ( ! is_null( $price_id ) && ! empty( $download->prices[ $price_id] ) ) {
						$title .= ' &mdash; ' . $download->prices[ $price_id ]['name'];
					}
				}

				$return = '<a href="' . add_query_arg( 'download', $item[ $column_name ] ) . '" >' . $title . '</a>';
				break;

			case 'customer' :
				$name = ! empty( $item['customer']->name ) ? $item['customer']->name : '<em>' . __( 'Unnamed Customer','easy-digital-downloads' ) . '</em>';
				$return = '<a href="' . esc_url( admin_url( 'edit.php?post_type=download&page=edd-customers&view=overview&id=' . $item['customer']->id ) ) . '">#' . $item['customer']->id . ' ' . $name . '</a>';
				break;

			case 'item_price' :
				$return = edd_currency_filter( edd_format_amount( $item['item_price'] ), $currency );
				break;

			case 'amount' :
				$return = edd_currency_filter( edd_format_amount( $item['amount'] / $item['quantity'] ), $currency );
				break;

			case 'payment_id' :
				$return = '<a href="' . admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $item['payment_id'] ) . '">' . edd_get_payment_number( $item['payment_id'] ) . '</a>';
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
			'ID'         => __( 'Log ID',       'easy-digital-downloads' ),
			'customer'   => __( 'Customer',     'easy-digital-downloads' ),
			'download'   => edd_get_label_singular(),
			'amount'     => __( 'Item Amount',  'easy-digital-downloads' ),
			'payment_id' => __( 'Order Number', 'easy-digital-downloads' ),
			'date'       => __( 'Date',         'easy-digital-downloads' ),
		);
	}

	/**
	 * Gets the meta query for the log query
	 *
	 * This is used to return log entries that match our search query, user query, or download query
	 *
	 * @since 1.4
	 * @return array $meta_query
	 */
	public function get_meta_query() {
		$user = $this->get_filtered_user();

		$meta_query = array();

		if( $user ) {
			// Show only logs from a specific user
			$meta_query[] = array(
				'key'   => '_edd_log_user_id',
				'value' => $user,
			);
		}

		$search = $this->get_search();
		if ( $search ) {
			if ( is_email( $search ) ) {
				// This is an email search. We use this to ensure it works for guest users and logged-in users
				$key     = '_edd_log_user_info';
				$compare = 'LIKE';
			} else {
				// Look for a user
				$key     = '_edd_log_user_id';
				$compare = 'LIKE';

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
				// Meta query only works for non file name searche
				$meta_query[] = array(
					'key'     => $key,
					'value'   => $search,
					'compare' => $compare,
				);

			}
		}

		return $meta_query;
	}

	/**
	 * Gets the log entries for the current view
	 *
	 * @since 1.4
	 * @global object $log_query EDD Logs Object
	 * @return array $logs_data Array of all the Log entries
	 */
	public function get_logs( $log_query = array() ) {
		$logs_data = array();
		$logs      = edd_get_logs( $log_query );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				/** @var EDD\Logs\Log $log */
				$payment_id = $log->get_meta( 'payment_id' );
				$payment    = new EDD_Payment( $payment_id );

				// Make sure this payment hasn't been deleted
				if ( ! empty( $payment->ID ) ) {
					$customer   = new EDD_Customer( $payment->customer_id );
					$cart_items = $payment->cart_details;
					$amount     = 0;

					if ( is_array( $cart_items ) ) {
						foreach ( $cart_items as $item ) {
							// If the item has variable pricing, make sure it's the right variation
							if ( $item['id'] == $log->object_id ) {
								if ( isset( $item['item_number']['options']['price_id'] ) ) {
									$log_price_id = $log->get_meta( 'price_id' );

									if ( (int) $item['item_number']['options']['price_id'] !== (int) $log_price_id ) {
										continue;
									}
								}

								$amount = isset( $item['price'] ) ? $item['price'] : $item['item_price'];
								break;
							}
						}

						$logs_data[] = array(
							'ID'         => $log->id,
							'payment_id' => $payment->ID,
							'customer'   => $customer,
							'download'   => $log->object_id,
							'price_id'   => isset( $log_price_id ) ? $log_price_id : null,
							'item_price' => isset( $item['item_price'] ) ? $item['item_price'] : $item['price'],
							'amount'     => $amount,
							'date'       => $payment->date,
							'quantity'   => $item['quantity'],
							// Keep track of the currency. Vital to produce the correct report
							'currency'   => $payment->currency,
						);
					}
				}
			}
		}

		return $logs_data;
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
		return edd_count_logs( $log_query );
	}
}
