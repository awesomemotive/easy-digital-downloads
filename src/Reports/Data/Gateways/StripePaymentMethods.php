<?php
/**
 * Gateways Reports Table Class
 *
 * @package     EDD\Admin\Reports
 * @copyright   Copyright (c) 2024, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.5
 */

namespace EDD\Reports\Data\Gateways;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Stats;
use EDD\Reports;
use EDD\Admin\List_Table;

/**
 * StripePaymentMethods Class
 *
 * Renders the Download Reports table.
 *
 * @since 3.3.5
 */
class StripePaymentMethods extends List_Table {

	/**
	 * Get things started
	 *
	 * @since 3.3.5
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'stripe-payment-method',
				'plural'   => 'stripe-payment-methods',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 3.3.5
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'label';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @since 3.3.5
	 *
	 * @param array  $item Contains all the data of the payment method.
	 * @param string $column_name The name of the column.
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @since 3.3.5
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		return array(
			'label'          => __( 'Payment Method', 'easy-digital-downloads' ),
			'complete_sales' => __( 'Complete Sales', 'easy-digital-downloads' ),
			'pending_sales'  => __( 'Pending / Failed Sales', 'easy-digital-downloads' ),
			'refunded_sales' => __( 'Refunded Sales', 'easy-digital-downloads' ),
			'total_sales'    => __( 'Total Sales', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Outputs the reporting views.
	 *
	 * @since 3.3.5
	 * @return void
	 */
	public function bulk_actions( $which = '' ) {}

	/**
	 * Retrieves all of the Stripe payment methods data.
	 *
	 * @since 3.3.5
	 * @return array Payment gateways reports table data.
	 */
	public function get_data() {

		foreach ( \EDD\Gateways\Stripe\PaymentMethods::list() as $method => $label ) {

			$complete_count = $this->query(
				$method,
				array(
					'status' => edd_get_gross_order_statuses(),
				)
			);
			if ( empty( $complete_count ) ) {
				continue;
			}

			$pending_count = $this->query(
				$method,
				array(
					'status' => edd_get_incomplete_order_statuses(),
				)
			);

			$refunded_count = $this->query(
				$method,
				array(
					'status' => array( 'complete' ),
					'type'   => array( 'refund' ),
				)
			);

			$total_count = $this->query(
				$method,
				array()
			);

			$reports_data[] = array(
				'ID'             => $method,
				'label'          => $label,
				'complete_sales' => edd_format_amount( $complete_count, false ),
				'pending_sales'  => edd_format_amount( $pending_count, false ),
				'refunded_sales' => edd_format_amount( $refunded_count, false ),
				'total_sales'    => edd_format_amount( $total_count, false ),
			);
		}

		return $reports_data;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @since 3.3.5
	 * @uses StripePaymentMethods::get_columns()
	 * @uses StripePaymentMethods::get_sortable_columns()
	 * @uses StripePaymentMethods::reports_data()
	 * @return void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->get_data();
	}

	/**
	 * Queries the orders table for the count of orders with a specific payment method.
	 *
	 * @since 3.3.5
	 */
	private function query( $method, $args ) {
		$filter   = Reports\get_filter_value( 'dates' );
		$currency = Reports\get_filter_value( 'currencies' );

		$args = wp_parse_args(
			$args,
			array(
				'gateway' => 'stripe',
				'type'    => array( 'sale' ),
			)
		);

		$args['meta_query'] = $this->get_meta_query( $method );

		if ( ! empty( $currency ) && 'convert' !== $currency ) {
			$args['currency'] = $currency;
		}

		if ( ! empty( $filter['range']['start'] ) ) {
			$args['start'] = $filter['range']['start']->format( 'mysql' );
		}

		if ( ! empty( $filter['range']['end'] ) ) {
			$args['end'] = $filter['range']['end']->format( 'mysql' );
		}

		return edd_count_orders( $args );
	}

	/**
	 * Retrieves the meta query for a specific payment method.
	 * Card payments are grouped with no payment method as the default.
	 *
	 * @since 3.3.5
	 * @param string $method The payment method.
	 * @return array The meta query for the specified payment method.
	 */
	private function get_meta_query( $method ) {
		if ( empty( $method ) ) {
			return array(
				'relation' => 'OR',
				array(
					'key'     => 'stripe_payment_method_type',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'stripe_payment_method_type',
					'value'   => 'card',
					'compare' => '=',
				),
			);
		}

		return array(
			array(
				'key'     => 'stripe_payment_method_type',
				'value'   => $method,
				'compare' => '=',
			),
		);
	}
}
