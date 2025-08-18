<?php
/**
 * Top Five Customers Table
 *
 * @package     EDD\Reports\Endpoints\Tables
 * @copyright   Copyright (c) 2025, Easy Digital Downloads, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.1
 */

namespace EDD\Reports\Endpoints\Tables;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Top Five Customers Table
 *
 * @since 3.5.1
 */
class TopFiveCustomers extends Table {

	/**
	 * Gets the ID for the table.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'top_five_customers';
	}

	/**
	 * Gets the label for the table.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Top Five Customers &mdash; All Time', 'easy-digital-downloads' );
	}

	/**
	 * Gets the class name for the table.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_class_name(): string {
		return '\\EDD\\Reports\\Data\\Customers\\Top_Five_Customers_List_Table';
	}
}
