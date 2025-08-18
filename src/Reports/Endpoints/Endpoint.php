<?php
/**
 * Base class for all reports endpoints.
 *
 * @package     EDD\Reports\Endpoints
 * @copyright   Copyright (c) 2025, Easy Digital Downloads, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.1
 */

namespace EDD\Reports\Endpoints;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Reports;

/**
 * Base class for all report endpoints.
 *
 * @since 3.5.1
 */
abstract class Endpoint {

	/**
	 * The endpoint ID.
	 *
	 * @since 3.5.1
	 * @var string
	 */
	protected $id;

	/**
	 * The reports object.
	 *
	 * @since 3.5.1
	 * @var object
	 */
	protected $reports;

	/**
	 * The dates filter.
	 *
	 * @since 3.5.1
	 * @var array
	 */
	protected $dates;

	/**
	 * The currency filter.
	 *
	 * @since 3.5.1
	 * @var string
	 */
	protected $currency;

	/**
	 * The exclude taxes filter.
	 *
	 * @since 3.5.1
	 * @var bool
	 */
	protected $exclude_taxes;

	/**
	 * The date column for the query.
	 *
	 * @since 3.5.1
	 * @var string
	 */
	protected $date_column = 'date_created';

	/**
	 * Constructor.
	 *
	 * @since 3.5.1
	 */
	public function __construct( $reports ) {
		$this->reports = $reports;
		$this->initialize_properties();
		$this->register();
	}

	/**
	 * Gets the data for the endpoint formatted for the callback system.
	 *
	 * @since 3.5.1
	 * @return mixed
	 */
	abstract public function get_data_for_callback();

	/**
	 * Gets the chart endpoint ID.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	abstract protected function get_id(): string;

	/**
	 * Registers the endpoint with the reports system.
	 *
	 * @since 3.5.1
	 */
	abstract protected function register(): void;

	/**
	 * Gets the data for the endpoint.
	 *
	 * @since 3.5.1
	 * @return mixed
	 */
	abstract protected function get_data();

	/**
	 * Gets the chart label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	abstract protected function get_label(): string;

	/**
	 * Gets the chart label for display.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_chart_label(): string {
		$options = \EDD\Reports\get_dates_filter_options();
		$dates   = \EDD\Reports\get_filter_value( 'dates' );
		$hbh     = \EDD\Reports\get_dates_filter_hour_by_hour();

		return $options[ $dates['range'] ] . ( $hbh ? ' (' . edd_get_timezone_abbr() . ')' : '' );
	}

	/**
	 * Sets up common properties used by all chart types.
	 *
	 * @since 3.5.1
	 */
	protected function initialize_properties(): void {
		$this->dates         = Reports\get_dates_filter( 'objects' );
		$this->currency      = Reports\get_filter_value( 'currencies' );
		$this->exclude_taxes = Reports\get_taxes_excluded_filter();
	}

	/**
	 * Gets the download data.
	 *
	 * @since 3.5.1
	 * @return array|false
	 */
	protected function get_download_data() {
		$download_data = Reports\get_filter_value( 'products' );
		if ( ! empty( $download_data ) && 'all' !== $download_data ) {
			return edd_parse_product_dropdown_value( $download_data );
		}

		return false;
	}

	/**
	 * Gets the gateway filter value.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_gateway(): string {
		return Reports\get_filter_value( 'gateways' );
	}

	/**
	 * Gets the order status filter value.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_order_status(): string {
		return Reports\get_filter_value( 'order_statuses' );
	}

	/**
	 * Gets the currency SQL for the query.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_currency_sql(): string {
		global $wpdb;

		if ( ! empty( $this->currency ) && array_key_exists( strtoupper( $this->currency ), edd_get_currencies() ) ) {
			return $wpdb->prepare( ' AND currency = %s ', strtoupper( $this->currency ) );
		}

		return '';
	}

	/**
	 * Gets the date SQL for the query.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_date_sql(): string {
		global $wpdb;

		return $wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
			sprintf(
				'%s >= %%s AND %s <= %%s',
				esc_sql( $this->date_column ),
				esc_sql( $this->date_column )
			),
			$this->dates['start']->copy()->format( 'mysql' ),
			$this->dates['end']->copy()->format( 'mysql' )
		);
	}

	/**
	 * Gets the discount data.
	 *
	 * @since 3.5.1
	 * @return string|false
	 */
	protected function get_discount_data() {
		$discount = Reports\get_filter_value( 'discounts' );
		if ( empty( $discount ) || 'all' === $discount ) {
			return false;
		}

		return edd_get_discount( $discount );
	}

	/**
	 * Gets the group by clause for the query.
	 *
	 * @since 3.5.1
	 * @return string
	 */
	protected function get_group_by(): string {
		return "
			GROUP BY {$this->sql_clauses['groupby']}
			ORDER BY {$this->sql_clauses['orderby']} ASC
			";
	}
}
