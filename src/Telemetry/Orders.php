<?php
/**
 * Gets the order data to send to our telemetry server.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.1.1
 */
namespace EDD\Telemetry;

class Orders {

	/**
	 * The order type to query.
	 *
	 * @var string
	 */
	protected $type;

	public function __construct( $type = 'sale' ) {
		$this->type = $type;
	}

	/**
	 * Gets the gateway data.
	 *
	 * @return array
	 */
	public function get() {
		/**
		 * Allows the ability to opt-out of sending aggregate gateway data to the telemetry server.
		 *
		 * @since 3.2.8
		 *
		 * @param bool $send_aggregate_data Whether to send aggregate gateway data to the telemetry server. Default is true.
		 * @return bool
		 */
		if ( false === apply_filters( 'edd_telemetry_send_aggregate_gateway_data', true ) ) {
			return array();
		}

		$data = array(
			'all_gateways' => $this->get_totals(),
		);
		foreach ( $this->get_date_ranges() as $type => $start ) {
			foreach ( $this->get_totals_by_gateway( $start ) as $gateway => $currency ) {
				foreach ( $currency as $code => $amounts ) {
					$data[ $gateway ][ $code ][ $type ] = $amounts;
				}
			}
		}

		return $data;
	}

	/**
	 * Gets the store order totals for all currencies and gateways.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	private function get_totals() {
		$data = array();
		foreach ( $this->get_date_ranges() as $type => $start ) {
			$results = $this->get_totals_by_date( $start );
			foreach ( $results as $result ) {
				$data['all_currencies'][ $type ] = array(
					'count' => $result->sales,
					'total' => $result->earnings,
				);
			}
		}

		return $data;
	}

	/**
	 * Gets the order count/total for a given date range.
	 *
	 * @since 3.1.1
	 * @param string $start The start date (optional).
	 * @return array
	 */
	private function get_totals_by_gateway( $start = '' ) {
		$data = array();
		foreach ( $this->get_results_by_date( $start ) as $total ) {
			$gateway                       = $total->gateway ? $total->gateway : 'unknown';
			$currency                      = $total->currency ? $total->currency : 'unknown';
			$data[ $gateway ][ $currency ] = array(
				'count' => $total->sales,
				'total' => $total->earnings,
			);
		}

		return $data;
	}

	/**
	 * Gets order totals by date.
	 *
	 * @since 3.1.1
	 * @param string $start
	 * @return array
	 */
	private function get_totals_by_date( $start = '' ) {
		global $wpdb;

		return $wpdb->get_results(
			"SELECT COUNT(*) as sales, SUM(total) as earnings
			 FROM {$wpdb->edd_orders}
			 WHERE type = '{$this->type}'
			 {$this->get_status_query()}
			 {$this->get_date_query( $start )}
			 LIMIT 0, 99999;"
		);
	}

	/**
	 * Gets orders grouped by gateway and currency.
	 *
	 * @since 3.1.1
	 * @param string $start
	 * @return array
	 */
	private function get_results_by_date( $start = '' ) {
		global $wpdb;

		return $wpdb->get_results(
			"SELECT gateway, currency, COUNT(*) as sales, SUM(total) as earnings
			 FROM {$wpdb->edd_orders}
			 WHERE type = '{$this->type}'
			 {$this->get_status_query()}
			 {$this->get_date_query( $start )}
			 GROUP BY gateway, currency
			 LIMIT 0, 99999;"
		);
	}

	/**
	 * Gets the status query string.
	 *
	 * @since 3.1.1
	 * @return string
	 */
	private function get_status_query() {
		return "AND status IN ('" . implode( "', '", edd_get_gross_order_statuses() ) . "')";
	}

	/**
	 * Gets the date query string.
	 *
	 * @since 3.1.1
	 * @param string $start
	 * @return string
	 */
	private function get_date_query( $start = '' ) {
		return $start ? sprintf(
			"AND ( date_completed >= '%s' AND date_completed <= '%s' )",
			gmdate( 'Y-m-d 00:00:00', strtotime( $start ) ),
			gmdate( 'Y-m-d 00:00:00', strtotime( 'today' ) )
		) : '';
	}

	/**
	 * Gets the date ranges for each query.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	private function get_date_ranges() {
		return array(
			'lifetime' => '',
			'week'     => '-1 week',
			'month'    => '-30 days',
		);
	}
}
