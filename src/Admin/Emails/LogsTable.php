<?php
/**
 * API Requests Log View Class
 *
 * @package     EDD
 * @subpackage  Admin/Reporting
 * @copyright   Copyright (c) 2024, Easy Digital Downloads, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.0
 */

namespace EDD\Admin\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Database\Queries\LogEmail;
use EDD\Emails\Templates\Registry;

/**
 * EmailLogsTable List Table Class
 *
 * @since 3.3.0
 */
class LogsTable extends \EDD_Base_Log_List_Table {

	/**
	 * Log type
	 *
	 * @var string
	 */
	protected $log_type = 'email_logs';

	/**
	 * Registry instance
	 *
	 * @var \EDD\Emails\Templates\Registry
	 */
	private $registry;

	/**
	 * EmailLogsTable constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'email_log',
				'plural'   => 'email_logs',
				'ajax'     => false,
			)
		);

		$this->registry = new Registry();
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since 3.3.0
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		return array(
			'subject'      => __( 'Subject', 'easy-digital-downloads' ),
			'email'        => __( 'To', 'easy-digital-downloads' ),
			'object_id'    => __( 'Email Object', 'easy-digital-downloads' ),
			'date_created' => __( 'Date Sent', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 3.3.0
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'email';
	}

	/**
	 * This function renders the columns in the list table.
	 *
	 * @since 3.3.0
	 * @param \EDD\Emails\LogEmail $item        The current item.
	 * @param string               $column_name The name of the column.
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'date_created':
				$date  = edd_date_i18n( strtotime( $item->{$column_name} ), get_option( 'date_format' ) );
				$date .= '<br />' . edd_date_i18n( strtotime( $item->{$column_name} ), get_option( 'time_format' ) );

				return $date;

			case 'object_id':
				return $this->get_object_column( $item );

			default:
				return $item->{$column_name};
		}
	}

	/**
	 * Gets the log entries for the current view
	 *
	 * @since 3.3.0
	 * @param array $query The array of query vars.
	 * @return array Array of all the log entries.
	 */
	public function get_logs( $query = array() ) {
		$logs = new LogEmail();

		return $logs->query( $query );
	}

	/**
	 * Get the total number of items.
	 *
	 * @since 3.3.0
	 * @param array $query The array of query vars.
	 * @return int
	 */
	public function get_total( $query = array() ) {
		$logs  = new LogEmail();
		$query = wp_parse_args(
			$query,
			array(
				'count' => true,
			)
		);

		return $logs->query( $query );
	}

	/**
	 * Get the object column.
	 *
	 * @since 3.3.0
	 * @param \EDD\Emails\LogEmail $item The current item.
	 * @return string
	 */
	private function get_object_column( $item ) {
		$link = false;

		switch ( $item->object_type ) {
			case 'order':
				$order = edd_get_order( $item->object_id );

				if ( $order ) {
					$link = array(
						'url'   => edd_get_admin_url(
							array(
								'page' => 'edd-payment-history',
								'view' => 'view-order-details',
								'id'   => absint( $item->object_id ),
							)
						),
						'label' => sprintf(
							/* translators: %s: Order number */
							__( 'Order %s', 'easy-digital-downloads' ),
							$order->get_number()
						),
					);
				}
				break;

			case 'user':
				$user = get_userdata( $item->object_id );

				if ( $user ) {
					$link = array(
						'url'   => add_query_arg(
							array(
								'user_id' => absint( $item->object_id ),
							),
							admin_url( 'user-edit.php' )
						),
						'label' => sprintf(
							/* translators: %s: User display name */
							__( 'User %s', 'easy-digital-downloads' ),
							$user->display_name
						),
					);
				}
				break;

			case 'refund':
				$refund = edd_get_order( $item->object_id );

				if ( $refund ) {
					$link = array(
						'url'   => edd_get_admin_url(
							array(
								'page' => 'edd-payment-history',
								'view' => 'view-refund-details',
								'id'   => absint( $item->object_id ),
							)
						),
						'label' => sprintf(
							/* translators: %s: Refund number */
							__( 'Refund %s', 'easy-digital-downloads' ),
							$refund->get_number()
						),
					);
				}
				break;

			default:
				break;
		}

		if ( $link ) {
			return sprintf(
				'<a href="%1$s">%2$s</a>',
				esc_url( $link['url'] ),
				esc_html( $link['label'] )
			);
		}

		/**
		 * Allow extensions to filter the object column.
		 *
		 * @since 3.3.0
		 * @param $item->object_id The object ID.
		 * @param $item The current item.
		 */
		return apply_filters( 'edd_emails_logs_table_object', $item->object_id, $item );
	}
}
