<?php
/**
 * Handles relative calculations for stats.
 *
 * @since 3.5.0
 * @package EDD\Stats\Traits
 */

namespace EDD\Stats\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Relative trait.
 *
 * @since 3.5.0
 */
trait Relative {

	/**
	 * Calculates the relative change between two datasets
	 * and outputs an array of details about comparison.
	 *
	 * @since 3.1
	 *
	 * @param int|float $total     The primary value result for the stat.
	 * @param int|float $relative  The value relative to the previous date range.
	 * @param bool      $reverse   If the stat being displayed is a 'reverse' state, where lower is better.
	 *
	 * @return array Details about the relative change between two datasets.
	 */
	public function generate_relative_data( $total = 0, $relative = 0, $reverse = false ) {
		$output = array(
			'comparable'                  => true,
			'no_change'                   => false,
			'percentage_change'           => false,
			'formatted_percentage_change' => false,
			'positive_change'             => false,
			'total'                       => $total,
			'relative'                    => $relative,
			'reverse'                     => $reverse,
		);

		if ( ( floatval( 0 ) === floatval( $total ) && floatval( 0 ) === floatval( $relative ) ) || ( $total === $relative ) ) {
			// There is no change between datasets.
			$output['no_change'] = true;
		} elseif ( floatval( 0 ) !== floatval( $relative ) ) {
			// There is a calculatable difference between datasets.
			$percentage_change           = ( $total - $relative ) / $relative * 100;
			$formatted_percentage_change = absint( $percentage_change );
			$positive_change             = false;

			if ( absint( $percentage_change ) < 100 ) {
				// Format the percentage change to two decimal places.
				$formatted_percentage_change = number_format( $percentage_change, 2 );

				// If the percentage change is negative, make it positive for display purposes. We handle the visual aspect via an icon in the UI.
				$formatted_percentage_change = $formatted_percentage_change < 0 ? $formatted_percentage_change * -1 : $formatted_percentage_change;
			}

			// Check if stat is in a 'reverse' state, where lower is better.
			$positive_change = (bool) ! $reverse;
			if ( 0 > $percentage_change ) {
				$positive_change = (bool) $reverse;
			}

			$output['percentage_change']           = $percentage_change;
			$output['formatted_percentage_change'] = $formatted_percentage_change;
			$output['positive_change']             = $positive_change;
		} else {
			// There is no data to compare.
			$output['comparable'] = false;
		}

		return $output;
	}

	/**
	 * Generates output for the report tiles when a relative % change is requested.
	 *
	 * @since 3.0
	 *
	 * @param int|float $total     The primary value result for the stat.
	 * @param int|float $relative  The value relative to the previous date range.
	 * @param bool      $reverse   If the stat being displayed is a 'reverse' state, where lower is better.
	 */
	private function generate_relative_markup( $total = 0, $relative = 0, $reverse = false ) {

		$relative_data   = $this->generate_relative_data( $total, $relative, $reverse );
		$total_output    = $this->maybe_format( $relative_data['total'] );
		$relative_markup = '';

		if ( $relative_data['no_change'] ) {
			$relative_output = esc_html__( 'No Change', 'easy-digital-downloads' );
		} elseif ( $relative_data['comparable'] ) {
			// Determine the direction of the change.
			$direction_suffix = $relative_data['reverse'] ? ' reverse' : '';
			$direction        = $relative_data['percentage_change'] > 0 ? 'up' : 'down';
			$direction       .= $direction_suffix;

			// Prepare the output with proper escaping and formatting.
			$icon            = '<span class="dashicons dashicons-arrow-' . esc_attr( $direction ) . '"></span>';
			$percentage      = $relative_data['formatted_percentage_change'] . '%';
			$relative_output = $icon . ' ' . $percentage;
		} else {
			$relative_output = '<span aria-hidden="true">&mdash;</span><span class="screen-reader-text">' . esc_html__( 'No data to compare', 'easy-digital-downloads' ) . '</span>';
		}

		$relative_markup = $total_output;
		if ( ! empty( $relative_output ) ) {
			$relative_markup .= '<div class="tile-relative">' . $relative_output . '</div>';
		}

		return $relative_markup;
	}
}
