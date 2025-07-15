<?php
/**
 * Helper methods for stats.
 *
 * @since 3.5.0
 */

namespace EDD\Stats\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Helpers trait.
 *
 * @since 3.5.0
 */
trait Helpers {

	/**
	 * Builds a fully qualified amount column and function, given the currency settings,
	 * tax settings, and accepted functions.
	 *
	 * @param array $args              {
	 *                                 Optional arguments.
	 *
	 * @type string $column_prefix     Column prefix (table alias or name).
	 * @type array  $accepted_function Accepted functions for this query.
	 *                    }
	 *
	 * @return string Example: `SUM( total / rate )`
	 * @throws \InvalidArgumentException
	 */
	private function get_amount_column_and_function( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'column_prefix'      => '',
				'accepted_functions' => array(),
				'requested_function' => false,
				'rate'               => true,
			)
		);

		$column        = $this->query_vars['column'];
		$column_prefix = '';

		if ( ! empty( $args['column_prefix'] ) ) {
			$column_prefix = $args['column_prefix'] . '.';
		}

		if ( empty( $column ) ) {
			$column = true === $this->query_vars['exclude_taxes'] ? "{$column_prefix}total - {$column_prefix}tax" : $column_prefix . 'total';
		} elseif ( false !== strpos( $column, '-' ) ) {

			$array = explode( '-', $column );
			foreach ( $array as $key => $column_name ) {
				$array[ $key ] = $column_prefix . trim( $column_name );
			}

			$column = implode( ' - ', $array );
		} else {
			$column = $column_prefix . $column;
		}

		$default_function = is_array( $args['accepted_functions'] ) && isset( $args['accepted_functions'][0] ) ? $args['accepted_functions'][0] : false;
		$function         = ! empty( $this->query_vars['function'] ) ? $this->query_vars['function'] : $default_function;

		if ( ! empty( $args['requested_function'] ) ) {
			$function = $args['requested_function'];
		}

		if ( empty( $function ) ) {
			throw new \InvalidArgumentException( 'Missing select function.' );
		}

		if ( ! empty( $args['accepted_functions'] ) && ! in_array( strtoupper( $function ), $args['accepted_functions'], true ) ) {
			if ( ! empty( $default_function ) ) {
				$function = $default_function;
			} else {
				throw new \InvalidArgumentException( sprintf( 'Invalid function "%s". Must be one of: %s', $this->query_vars['function'], json_encode( $args['accepted_functions'] ) ) );
			}
		}

		$function = strtoupper( $function );

		// Multiply by rate if currency conversion is enabled.
		if (
			! empty( $args['rate'] ) &&
			in_array( $function, array( 'SUM', 'AVG' ), true ) &&
			( empty( $this->query_vars['currency'] ) || 'convert' === $this->query_vars['currency'] ) &&
			( false !== strpos( $column, 'total' ) || false !== strpos( $column, 'tax' ) )
		) {
			$column = sprintf( '(%s) / %s', $column, $column_prefix . 'rate' );
		}

		return sprintf( '%s(%s)', $function, $column );
	}

	/**
	 * Based on the query_vars['revenue_type'], use gross or net statuses.
	 *
	 * @since 3.0
	 * @param array $query The unique query parameters.
	 * @return array The statuses of orders to use for the stats generation.
	 */
	private function get_revenue_type_statuses( $query = array() ) {
		$revenue_type = $this->query_vars['revenue_type'];
		if ( isset( $query['revenue_type'] ) ) {
			$revenue_type = $query['revenue_type'];
		}
		if ( 'net' === $revenue_type ) {
			return edd_get_net_order_statuses();
		}

		return edd_get_gross_order_statuses();
	}

	/**
	 * Based on the query_vars['revenue_type'], use just sale or also include refunds.
	 *
	 * @since 3.0
	 * @param array $query The unique query parameters.
	 * @return array The order types to use when generating stats.
	 */
	private function get_revenue_type_order_types( $query ) {
		$order_types  = array( 'sale' );
		$revenue_type = $this->query_vars['revenue_type'];
		if ( isset( $query['revenue_type'] ) ) {
			$revenue_type = $query['revenue_type'];
		}
		if ( 'net' === $revenue_type ) {
			$order_types[] = 'refund';
		}

		return $order_types;
	}

	/**
	 * Gets a placeholder string from an array.
	 *
	 * @since 3.1
	 * @param array $array
	 * @return string
	 */
	private function get_placeholder_string( $array ) {
		return implode( ', ', array_fill( 0, count( $array ), '%s' ) );
	}

	/**
	 * Get the column to use for the query.
	 *
	 * @since 3.5.0
	 * @return string
	 */
	private function get_column() {
		return true === $this->query_vars['exclude_taxes'] ? 'total - tax' : 'total';
	}
}
