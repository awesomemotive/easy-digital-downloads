<?php
/**
 * Most Valuable Customers list table.
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
 * Most_Valuable_Customers_List_Table class.
 *
 * @since 3.0
 */
class Most_Valuable_Customers_List_Table extends \EDD_Customer_Reports_Table {

	/**
	 * Query the database and fetch the top five customers of all time.
	 *
	 * @since 3.0
	 *
	 * @return array $data Customers.
	 */
	public function reports_data() {
		$data = array();

		$filter  = Reports\get_filter_value( 'dates' );

		$start_date = date( 'Y-m-d 00:00:00', strtotime( $filter['from'] ) );
		$end_date   = date( 'Y-m-d 23:59:59', strtotime( $filter['to'] ) );

		$args = array(
			'number'   => 5,
			'order'   => 'DESC',
			'orderby' => 'total',
			'fields'  => 'customer_id',
			'date_query' => array(
				array(
					'after'     => $start_date,
					'before'    => $end_date,
					'inclusive' => true,
				)
			)
		);

		$customer_ids = array_unique( edd_get_orders( $args ) );

		foreach ( $customer_ids as $customer_id ) {
			$customer = edd_get_customer( $customer_id );

			$user_id = ! empty( $customer->user_id ) ? intval( $customer->user_id ) : 0;

			$data[] = array(
				'id'            => $customer->id,
				'user_id'       => $user_id,
				'name'          => $customer->name,
				'email'         => $customer->email,
				'num_purchases' => $customer->purchase_count,
				'amount_spent'  => $customer->purchase_value,
				'date_created'  => $customer->date_created,
			);
		}

		return $data;
	}
}