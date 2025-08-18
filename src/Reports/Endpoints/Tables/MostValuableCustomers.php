<?php
/**
 * Most Valuable Customers Table
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
class MostValuableCustomers extends Table {

	/**
	 * Gets the ID for the table.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_id(): string {
		return 'most_valuable_customers';
	}

	/**
	 * Gets the label for the table.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_label(): string {
		return __( 'Most Valuable Customers', 'easy-digital-downloads' ) . ' &mdash; ' . $this->get_chart_label();
	}

	/**
	 * Gets the class name for the table.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_class_name(): string {
		return '\\EDD\\Reports\\Data\\Customers\\Most_Valuable_Customers_List_Table';
	}
}
