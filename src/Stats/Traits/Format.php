<?php
/**
 * Handles formatting for stats.
 *
 * @since 3.5.0
 * @package EDD\Stats\Traits
 */

namespace EDD\Stats\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Format trait.
 *
 * @since 3.5.0
 */
trait Format {

	/**
	 * Format the data if requested via the query parameter.
	 *
	 * @since 3.0
	 * @access private
	 *
	 * @param mixed $data Data to format.
	 *
	 * @return mixed Raw or formatted data depending on query parameter.
	 */
	private function maybe_format( $data = null ) {

		// Bail if nothing was passed.
		if ( null === $data ) {
			return $data;
		}

		$allowed_output_formats = array( 'raw', 'typed', 'formatted' );

		// Output format. Default raw.
		$output = isset( $this->query_vars['output'] ) && in_array( $this->query_vars['output'], $allowed_output_formats, true )
			? $this->query_vars['output']
			: 'raw';

		// Return data as is if the format is raw.
		if ( 'raw' === $output ) {
			return $data;
		}

		$currency = $this->query_vars['currency'];
		if ( empty( $currency ) || 'convert' === strtolower( $currency ) ) {
			$currency = edd_get_currency();
		}

		if ( is_object( $data ) ) {
			foreach ( array_keys( get_object_vars( $data ) ) as $field ) {
				if ( is_numeric( $data->{$field} ) ) {
					$data->{$field} = edd_format_amount( $data->{$field}, true, $currency, $output );

					if ( 'formatted' === $output ) {
						$data->{$field} = edd_currency_filter( $data->{$field}, $currency );
					}
				}
			}
		} elseif ( is_array( $data ) ) {
			foreach ( array_keys( $data ) as $field ) {
				if ( is_numeric( $data[ $field ] ) ) {
					$data[ $field ] = edd_format_amount( $data[ $field ], true, $currency, $output );

					if ( 'formatted' === $output ) {
						$data[ $field ] = edd_currency_filter( $data[ $field ], $currency );
					}
				}
			}
		} else {
			if ( is_numeric( $data ) ) {
				$data = edd_format_amount( $data, true, $currency, $output );

				if ( 'formatted' === $output ) {
					$data = edd_currency_filter( $data, $currency );
				}
			}
		}

		return $data;
	}
}
