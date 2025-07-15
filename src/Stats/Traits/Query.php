<?php
/**
 * Handles stats queries.
 *
 * @since 3.5.0
 * @package EDD\Stats\Traits
 */

namespace EDD\Stats\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Query trait.
 *
 * @since 3.5.0
 */
trait Query {

	/**
	 * Ensures arguments exist before going ahead and calculating statistics.
	 *
	 * @since 3.0
	 *
	 * @param array $query
	 */
	protected function pre_query( $query = array() ) {

		$this->parse_query( $query );

		// Generate date query SQL if dates have been set.
		if ( ! empty( $this->query_vars['start'] ) || ! empty( $this->query_vars['end'] ) ) {
			$date_query_sql = ' AND ';

			if ( ! empty( $this->query_vars['start'] ) ) {
				$date_query_sql .= "{$this->query_vars['table']}.{$this->query_vars['date_query_column']} ";
				$date_query_sql .= $this->get_db()->prepare( '>= %s', $this->query_vars['start'] );
			}

			// Join dates with `AND` if start and end date set.
			if ( ! empty( $this->query_vars['start'] ) && ! empty( $this->query_vars['end'] ) ) {
				$date_query_sql .= ' AND ';
			}

			if ( ! empty( $this->query_vars['end'] ) ) {
				$date_query_sql .= $this->get_db()->prepare( "{$this->query_vars['table']}.{$this->query_vars['date_query_column']} <= %s", $this->query_vars['end'] );
			}

			$this->query_vars['date_query_sql'] = $date_query_sql;
		}

		// Generate status SQL if statuses have been set.
		if ( ! empty( $this->query_vars['status'] ) ) {
			if ( 'any' === $this->query_vars['status'] ) {
				$this->query_vars['status_sql'] = '';
			} else {
				$this->query_vars['status'] = array_map( 'sanitize_text_field', $this->query_vars['status'] );

				$placeholders = $this->get_placeholder_string( $this->query_vars['status'] );

				$this->query_vars['status_sql'] = $this->get_db()->prepare( "AND {$this->query_vars['table']}.status IN ({$placeholders})", $this->query_vars['status'] );
			}
		}

		if ( ! empty( $this->query_vars['type'] ) ) {

			// We always want to format this as an array, so account for a possible string.
			if ( ! is_array( $this->query_vars['type'] ) ) {
				$this->query_vars['type'] = array( $this->query_vars['type'] );
			}

			$this->query_vars['type'] = array_map( 'sanitize_text_field', $this->query_vars['type'] );

			$placeholders = $this->get_placeholder_string( $this->query_vars['type'] );

			$this->query_vars['type_sql'] = $this->get_db()->prepare( "AND {$this->query_vars['table']}.type IN ({$placeholders})", $this->query_vars['type'] );
		}

		if ( ! empty( $this->query_vars['currency'] ) && 'convert' !== strtolower( $this->query_vars['currency'] ) ) {
			$this->query_vars['currency_sql'] = $this->get_db()->prepare( "AND {$this->query_vars['table']}.currency = %s", $this->query_vars['currency'] );
		}
	}

	/**
	 * Runs after a query. Resets query vars back to the originals passed in via the constructor.
	 *
	 * @since 3.0
	 */
	protected function post_query() {
		$this->query_vars = $this->query_var_originals;
	}

	/**
	 * Parse query vars to be passed to the calculation methods.
	 * This method should only be used by `pre_query()`.
	 *
	 * @since 3.0
	 * @access private
	 *
	 * @see \EDD\Stats::__construct()
	 *
	 * @param array $query Array of arguments. See \EDD\Stats::__construct().
	 */
	private function parse_query( $query = array() ) {

		$this->query_vars = wp_parse_args( $query, $this->query_vars );

		// Set date ranges.
		$this->set_date_ranges();

		// Use Carbon to set up start and end date based on range passed.
		if ( ! empty( $this->query_vars['range'] ) && isset( $this->date_ranges[ $this->query_vars['range'] ] ) ) {

			if ( ! empty( $this->date_ranges[ $this->query_vars['range'] ]['start'] ) ) {
				$this->query_vars['start'] = $this->date_ranges[ $this->query_vars['range'] ]['start']->format( 'mysql' );
			}

			if ( ! empty( $this->date_ranges[ $this->query_vars['range'] ]['end'] ) ) {
				$this->query_vars['end'] = $this->date_ranges[ $this->query_vars['range'] ]['end']->format( 'mysql' );
			}
		}

		// Use Carbon to set up start and end date based on range passed.
		if ( true === $this->query_vars['relative'] && ! empty( $this->query_vars['range'] ) && isset( $this->relative_date_ranges[ $this->query_vars['range'] ] ) ) {

			if ( ! empty( $this->relative_date_ranges[ $this->query_vars['range'] ]['start'] ) ) {
				$this->query_vars['relative_start'] = $this->relative_date_ranges[ $this->query_vars['range'] ]['start']->format( 'mysql' );
			}

			if ( ! empty( $this->relative_date_ranges[ $this->query_vars['range'] ]['end'] ) ) {
				$this->query_vars['relative_end'] = $this->relative_date_ranges[ $this->query_vars['range'] ]['end']->format( 'mysql' );
			}
		}

		// Validate currency.
		if ( empty( $this->query_vars['currency'] ) ) {
			$this->query_vars['currency'] = false;
		} elseif ( array_key_exists( strtoupper( $this->query_vars['currency'] ), edd_get_currencies() ) ) {
			$this->query_vars['currency'] = strtoupper( $this->query_vars['currency'] );
		} else {
			$this->query_vars['currency'] = 'convert';
		}

		// Correctly format functions and column names.
		if ( ! empty( $this->query_vars['function'] ) ) {
			$this->query_vars['function'] = strtoupper( $this->query_vars['function'] );
		}

		if ( ! empty( $this->query_vars['column'] ) ) {
			$this->query_vars['column'] = strtolower( $this->query_vars['column'] );
		}

		/** Parse country */
		$country = isset( $this->query_vars['country'] )
			? sanitize_text_field( $this->query_vars['country'] )
			: '';

		if ( $country ) {
			$country_list = array_filter( edd_get_country_list() );

			// Maybe convert country code to country name.
			$country = in_array( $country, array_flip( $country_list ), true )
				? $country_list[ $country ]
				: $country;

			// Ensure a valid county has been passed.
			$country = in_array( $country, $country_list, true )
				? $country
				: null;

			// Convert back to country code for SQL query.
			$country_list                = array_flip( $country_list );
			$this->query_vars['country'] = is_null( $country )
				? ''
				: $country_list[ $country ];
		}

		/** Parse state */

		$state = isset( $this->query_vars['region'] )
			? sanitize_text_field( $this->query_vars['region'] )
			: '';

		// Only parse state if one was passed.
		if ( $state ) {
			$state_list = array_filter( edd_get_shop_states( $this->query_vars['country'] ) );

			// Maybe convert state code to state name.
			$state = in_array( $state, array_flip( $state_list ), true )
				? $state_list[ $state ]
				: $state;

			// Ensure a valid state has been passed.
			$state = in_array( $state, $state_list, true )
				? $state
				: null;

			// Convert back to state code for SQL query.
			$state_codes                = array_flip( $state_list );
			$this->query_vars['region'] = is_null( $state )
				? ''
				: $state_codes[ $state ];
		}

		/**
		 * Fires after the item query vars have been parsed.
		 *
		 * @since 3.0
		 *
		 * @param \EDD\Stats &$this The \EDD\Stats (passed by reference).
		 */
		do_action_ref_array( 'edd_order_stats_parse_query', array( &$this ) );
	}

	/**
	 * Get the default query vars.
	 *
	 * @since 3.5.0
	 * @return array
	 */
	private function get_defaults() {
		return array(
			'start'             => '',
			'end'               => '',
			'range'             => '',
			'exclude_taxes'     => false,
			'currency'          => false,
			'currency_sql'      => '',
			'status'            => array(),
			'status_sql'        => '',
			'type'              => array(),
			'type_sql'          => '',
			'where_sql'         => '',
			'date_query_sql'    => '',
			'date_query_column' => '',
			'column'            => '',
			'table'             => '',
			'function'          => 'SUM',
			'output'            => 'raw',
			'relative'          => false,
			'relative_start'    => '',
			'relative_end'      => '',
			'grouped'           => false,
			'product_id'        => '',
			'price_id'          => null,
			'revenue_type'      => 'gross',
			'country'           => '',
			'region'            => '',
		);
	}

	/**
	 * Generates price ID query SQL.
	 *
	 * @since 3.0
	 * @return string
	 */
	private function generate_price_id_query_sql() {
		return ! is_null( $this->query_vars['price_id'] ) && is_numeric( $this->query_vars['price_id'] )
			? $this->get_db()->prepare( "AND {$this->query_vars['table']}.price_id = %d", absint( $this->query_vars['price_id'] ) )
			: '';
	}

	/** Private Getters *******************************************************/

	/**
	 * Return the global database interface.
	 *
	 * @since 3.0
	 * @access private
	 * @static
	 *
	 * @return \wpdb|\stdClass
	 */
	private static function get_db() {
		return isset( $GLOBALS['wpdb'] )
			? $GLOBALS['wpdb']
			: new \stdClass();
	}

	/**
	 * Get the discount code query.
	 *
	 * @since 3.5.0
	 * @return string
	 */
	private function get_discount_code() {
		return isset( $this->query_vars['discount_code'] )
			? $this->get_db()->prepare( 'AND type = %s AND description = %s', 'discount', sanitize_text_field( $this->query_vars['discount_code'] ) )
			: $this->get_db()->prepare( 'AND type = %s', 'discount' );
	}
}
