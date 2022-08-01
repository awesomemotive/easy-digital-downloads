<?php
/**
 * Top Five Customers list table.
 *
 * @package     EDD
 * @subpackage  Reports/Data/Customers
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Reports\Data\Customers;

use EDD\Reports as Reports;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Load \EDD_Customer_Reports_Table if not loaded
if ( ! class_exists( '\EDD_Customer_Reports_Table' ) ) {
	require_once EDD_PLUGIN_DIR . 'includes/admin/customers/class-customer-table.php';
}

/**
 * Top_Five_Customers_List_Table class.
 *
 * @since 3.0
 */
class Top_Five_Customers_List_Table extends \EDD_Customer_Reports_Table {

	/**
	 * Query the database and fetch the top five customers of all time.
	 *
	 * @since 3.0
	 *
	 * @return array $data Customers.
	 */
	public function get_data() {
		$data  = array();
		$taxes = Reports\get_taxes_excluded_filter();

		if ( false === $taxes ) {
			$args = array(
				'number'  => 5,
				'order'   => 'DESC',
				'orderby' => 'purchase_value',
			);

			$customers = edd_get_customers( $args );

			foreach ( $customers as $customer ) {
				/** @var \EDD_Customer $customer */

				$user_id = ! empty( $customer->user_id )
					? intval( $customer->user_id )
					: 0;

				$data[] = array(
					'id'           => $customer->id,
					'user_id'      => $user_id,
					'name'         => $customer->name,
					'email'        => $customer->email,
					'order_count'  => $customer->purchase_count,
					'spent'        => $customer->purchase_value,
					'date_created' => $customer->date_created,
				);
			}
		} else {
			global $wpdb;

			// @todo DRY with Most_Valuable_Customers_List_Table

			$column          = Reports\get_taxes_excluded_filter() ? 'total - tax' : 'total';
			$currency        = Reports\get_filter_value( 'currencies' );
			$currency_clause = '';

			if ( empty( $currency ) || 'convert' === $currency ) {
				$column = sprintf( '%s / rate', $column );
			} else {
				$currency_clause = $wpdb->prepare( 'AND status = %s', strtoupper( $currency ) );
			}

			$sql = "SELECT customer_id, COUNT(id) AS order_count, SUM({$column}) AS total_spent
					FROM {$wpdb->edd_orders}
					WHERE status IN (%s, %s) AND type = 'sale'
					{$currency_clause}
					GROUP BY customer_id
					ORDER BY total_spent DESC
					LIMIT 5";

			$results = $wpdb->get_results( $wpdb->prepare( $sql, sanitize_text_field( 'complete' ), sanitize_text_field( 'revoked' ) ) );

			foreach ( $results as $result ) {
				$customer = edd_get_customer( (int) $result->customer_id );

				// Skip if customer record not found.
				if ( ! $customer ) {
					continue;
				}

				$user_id = ! empty( $customer->user_id )
					? intval( $customer->user_id )
					: 0;

				$data[] = array(
					'id'           => $customer->id,
					'user_id'      => $user_id,
					'name'         => $customer->name,
					'email'        => $customer->email,
					'order_count'  => absint( $result->order_count ),
					'spent'        => $result->total_spent,
					'date_created' => $customer->date_created,
				);
			}
		}

		return $data;
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @since 3.0
	 *
	 * @return array $columns Array of all the list table columns.
	 */
	public function get_columns() {
		$columns = parent::get_columns();

		// Remove the checkbox if it exists.
		if ( isset( $columns['cb'] ) ) {
			unset( $columns['cb'] );
		}

		return $columns;
	}

	/**
	 * Overrides the `spent` column value to possibly display in the filtered currency.
	 *
	 * @since 3.0
	 *
	 * @param array  $item
	 * @param string $column_name
	 *
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		if ( 'spent' !== $column_name ) {
			return parent::column_default( $item, $column_name );
		}

		$currency          = '';
		$selected_currency = Reports\get_filter_value( 'currencies' );
		if ( ! empty( $selected_currency ) && 'convert' !== $selected_currency ) {
			$currency = $selected_currency;
		}

		$value = edd_currency_filter( edd_format_amount( $item[ $column_name ] ), $currency );

		return apply_filters( 'edd_customers_column_' . $column_name, $value, $item['id'] );
	}

	/**
	 * Return empty array to disable sorting.
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array();
	}

	/**
	 * Return empty array to remove bulk actions.
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array();
	}

	/**
	 * Hide pagination.
	 *
	 * @since 3.0
	 *
	 * @param string $which
	 */
	protected function pagination( $which ) {

	}

	/**
	 * Hide table navigation.
	 *
	 * @since 3.0
	 *
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {

	}
}
