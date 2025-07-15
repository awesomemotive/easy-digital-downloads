<?php
/**
 * Database components.
 *
 * @package   EDD\Database
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.5.0
 */

namespace EDD\Database;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Database components.
 */
class Components {

	/**
	 * Register components.
	 *
	 * @since 3.5.0
	 */
	public static function register() {
		foreach ( self::get() as $name => $args ) {
			edd_register_component( $name, $args );
		}
	}

	/**
	 * Get components.
	 *
	 * @since 3.5.0
	 * @return array
	 */
	private static function get() {
		return array(
			'customer'               => array(
				'schema' => '\\EDD\\Database\\Schemas\\Customers',
				'table'  => '\\EDD\\Database\\Tables\\Customers',
				'meta'   => '\\EDD\\Database\\Tables\\Customer_Meta',
				'query'  => '\\EDD\\Database\\Queries\\Customer',
				'object' => '\\EDD_Customer',
			),
			'customer_address'       => array(
				'schema' => '\\EDD\\Database\\Schemas\\Customer_Addresses',
				'table'  => '\\EDD\\Database\\Tables\\Customer_Addresses',
				'query'  => '\\EDD\\Database\\Queries\\Customer_Address',
				'object' => '\\EDD\\Customers\\Customer_Address',
				'meta'   => false,
			),
			'customer_email_address' => array(
				'schema' => '\\EDD\\Database\\Schemas\\Customer_Email_Addresses',
				'table'  => '\\EDD\\Database\\Tables\\Customer_Email_Addresses',
				'query'  => '\\EDD\\Database\\Queries\\Customer_Email_Address',
				'object' => '\\EDD\\Customers\\Customer_Email_Address',
				'meta'   => false,
			),
			'adjustment'             => array(
				'schema' => '\\EDD\\Database\\Schemas\\Adjustments',
				'table'  => '\\EDD\\Database\\Tables\\Adjustments',
				'meta'   => '\\EDD\\Database\\Tables\\Adjustment_Meta',
				'query'  => '\\EDD\\Database\\Queries\\Adjustment',
				'object' => '\\EDD\\Adjustments\\Adjustment',
			),
			'note'                   => array(
				'schema' => '\\EDD\\Database\\Schemas\\Notes',
				'table'  => '\\EDD\\Database\\Tables\\Notes',
				'meta'   => '\\EDD\\Database\\Tables\\Note_Meta',
				'query'  => '\\EDD\\Database\\Queries\\Note',
				'object' => '\\EDD\\Notes\\Note',
			),
			'order'                  => array(
				'schema' => '\\EDD\\Database\\Schemas\\Orders',
				'table'  => '\\EDD\\Database\\Tables\\Orders',
				'meta'   => '\\EDD\\Database\\Tables\\Order_Meta',
				'query'  => '\\EDD\\Database\\Queries\\Order',
				'object' => '\\EDD\\Orders\\Order',
			),
			'order_item'             => array(
				'schema' => '\\EDD\\Database\\Schemas\\Order_Items',
				'table'  => '\\EDD\\Database\\Tables\\Order_Items',
				'meta'   => '\\EDD\\Database\\Tables\\Order_Item_Meta',
				'query'  => '\\EDD\\Database\\Queries\\Order_Item',
				'object' => '\\EDD\\Orders\\Order_Item',
			),
			'order_adjustment'       => array(
				'schema' => '\\EDD\\Database\\Schemas\\Order_Adjustments',
				'table'  => '\\EDD\\Database\\Tables\\Order_Adjustments',
				'meta'   => '\\EDD\\Database\\Tables\\Order_Adjustment_Meta',
				'query'  => '\\EDD\\Database\\Queries\\Order_Adjustment',
				'object' => '\\EDD\\Orders\\Order_Adjustment',
			),
			'order_address'          => array(
				'schema' => '\\EDD\\Database\\Schemas\\Order_Addresses',
				'table'  => '\\EDD\\Database\\Tables\\Order_Addresses',
				'query'  => '\\EDD\\Database\\Queries\\Order_Address',
				'object' => '\\EDD\\Orders\\Order_Address',
				'meta'   => false,
			),
			'order_transaction'      => array(
				'schema' => '\\EDD\\Database\\Schemas\\Order_Transactions',
				'table'  => '\\EDD\\Database\\Tables\\Order_Transactions',
				'query'  => '\\EDD\\Database\\Queries\\Order_Transaction',
				'object' => '\\EDD\\Orders\\Order_Transaction',
				'meta'   => false,
			),
			'log'                    => array(
				'schema' => '\\EDD\\Database\\Schemas\\Logs',
				'table'  => '\\EDD\\Database\\Tables\\Logs',
				'meta'   => '\\EDD\\Database\\Tables\\Log_Meta',
				'query'  => '\\EDD\\Database\\Queries\\Log',
				'object' => '\\EDD\\Logs\\Log',
			),
			'log_api_request'        => array(
				'schema' => '\\EDD\\Database\\Schemas\\Logs_Api_Requests',
				'table'  => '\\EDD\\Database\\Tables\\Logs_Api_Requests',
				'meta'   => '\\EDD\\Database\\Tables\\Logs_Api_Request_Meta',
				'query'  => '\\EDD\\Database\\Queries\\Log_Api_Request',
				'object' => '\\EDD\\Logs\\Api_Request_Log',
			),
			'log_file_download'      => array(
				'schema' => '\\EDD\\Database\\Schemas\\Logs_File_Downloads',
				'table'  => '\\EDD\\Database\\Tables\\Logs_File_Downloads',
				'meta'   => '\\EDD\\Database\\Tables\\Logs_File_Download_Meta',
				'query'  => '\\EDD\\Database\\Queries\\Log_File_Download',
				'object' => '\\EDD\\Logs\\File_Download_Log',
			),
			'notification'           => array(
				'schema' => '\\EDD\\Database\\Schemas\\Notifications',
				'table'  => '\\EDD\\Database\\Tables\\Notifications',
				'query'  => '\\EDD\\Database\\Queries\\Notification',
				'object' => '\\EDD\\Notifications\\Notification',
				'meta'   => false,
			),
			'email'                  => array(
				'schema' => '\\EDD\\Database\\Schemas\\Emails',
				'table'  => '\\EDD\\Database\\Tables\\Emails',
				'query'  => '\\EDD\\Database\\Queries\\Email',
				'object' => '\\EDD\\Emails\\Email',
				'meta'   => '\\EDD\\Database\\Tables\\EmailMeta',
			),
			'log_email'              => array(
				'schema' => '\\EDD\\Database\\Schemas\\LogsEmails',
				'table'  => '\\EDD\\Database\\Tables\\LogsEmails',
				'query'  => '\\EDD\\Database\\Queries\\LogEmail',
				'object' => '\\EDD\\Emails\\LogEmail',
				'meta'   => '\\EDD\\Database\\Tables\\LogsEmailMeta',
			),
			'tax_rate'               => array(
				'schema' => '\\EDD\\Database\\Schemas\\TaxRates',
				'table'  => '\\EDD\\Database\\Tables\\TaxRates',
				'query'  => '\\EDD\\Database\\Queries\\TaxRate',
				'object' => '\\EDD\\Taxes\\Rate',
				'meta'   => false,
			),
		);
	}
}
