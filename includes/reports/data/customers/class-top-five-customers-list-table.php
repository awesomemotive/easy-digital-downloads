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
namespace EDD\Reports;

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
	public function reports_data() {
		$data = array();

		$args = array(
			'limit'   => 5,
			'order'   => 'DESC',
			'orderby' => 'purchase_value',
		);

		$customers = edd_get_customers( $args );

		foreach ( $customers as $customer ) {
			/** @var \EDD_Customer $customer */

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