<?php
/**
 * Progress Bar utility.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.2.0
 */
namespace EDD\Utils;

defined( 'ABSPATH' ) || exit;

class ProgressBar {

	/**
	 * The array of parameters for the progress bar.
	 *
	 * @var array
	 */
	private $args = array();

	/**
	 * Constructor.
	 *
	 * @param array $args Array of parameters for the progress bar.
	 */
	public function __construct( $args ) {
		$this->args = wp_parse_args(
			$args,
			array(
				'current_percentage' => false,
				'size'               => 'medium',
				'show_percentage'    => false,
				'show_current'       => false,
				'show_total'         => false,
				'current_count'      => 0,
				'total_count'        => 100,
			)
		);
	}

	/**
	 * Gets the progress bar markup.
	 *
	 * @since 3.2.0
	 * @return string
	 */
	public function get() {
		return sprintf(
			'<div class="edd-progress-bar %s"><div class="progress" style="--progress-width: %s;"></div>%s</div>',
			$this->get_size(),
			$this->get_current_percentage(),
			$this->get_label(),
		);
	}

	/**
	 * Gets the progress bar classes.
	 *
	 * We only support the sizes defined in CSS, so we'll only return those.
	 *
	 * @since 3.2.0
	 * @return string
	 */
	private function get_size() {
		if ( in_array( $this->args['size'], array( 'small', 'medium', 'large' ), true ) ) {
			return $this->args['size'];
		}

		return 'medium';
	}

	/**
	 * Gets the progress bar % complete.
	 *
	 * This is a formatted string, complete with the % sign.
	 *
	 * @since 3.2.0
	 * @return string
	 */
	private function get_current_percentage() {
		if ( is_numeric( $this->args['current_percentage'] ) ) {
			$current_percentage = intval( $this->args['current_percentage'] );
		} elseif ( is_numeric( $this->args['current_count'] ) && is_numeric( $this->args['total_count'] ) ) {
			$current_percentage = intval( $this->args['current_count'] / $this->args['total_count'] * 100 );
		} else {
			$current_percentage = 0;
		}

		// Protect from ever going over 100%.
		if ( $current_percentage > 100 ) {
			$current_percentage = 100;
		}

		return $current_percentage . '%';
	}

	/**
	 * Gets the progress bar label.
	 *
	 * Determines what to show in the label, based on the passed in values for showing percentage, current count, and total count.
	 *
	 * @since 3.2.0
	 * @return string
	 */
	private function get_label() {
		$label = '';
		if ( $this->args['show_current'] ) {
			$label .= sprintf( '%s', $this->args['current_count'] );
		}

		if ( $this->args['show_total'] ) {
			if ( $this->args['show_current'] ) {
				$label .= sprintf( ' / %d', $this->args['total_count'] );
			} else {
				$label .= sprintf( '%s', $this->args['total_count'] );
			}
		}

		if ( $this->args['show_percentage'] ) {
			if ( $this->args['show_current'] || $this->args['show_total'] ) {
				$label .= sprintf( ' (%s)', $this->get_current_percentage() );
			} else {
				$label .= sprintf( '%s', $this->get_current_percentage() );
			}
		}

		// If our label isn't empty, wrap it up for styling.
		if ( ! empty( $label ) ) {
			$label = '<div class="label">' . $label . '</div>';
		}

		return $label;
	}
}
