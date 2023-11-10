<?php

namespace EDD\Telemetry\Traits;

defined( 'ABSPATH' ) || exit;

/**
 * Trait Anonymize
 *
 * @since 3.2.5
 * @package EDD\Telemetry\Traits
 */
trait Anonymize {

	/**
	 * Attempts to anonymize a string.
	 *
	 * @since 3.1.1
	 * @param string $value The string to anonymize.
	 * @return string
	 */
	private function anonymize( $value ) {
		if ( is_email( $value ) ) {
			return edd_pseudo_mask_email( $value );
		}
		if ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
			return edd_mask_domain( $value );
		}

		return $this->anonymize_site_name( $value );
	}

	/**
	 * Replace any use of the site name in a string.
	 *
	 * @since 3.2.5
	 * @param string $value The string to anonymize.
	 * @return string
	 */
	private function anonymize_site_name( $value ) {
		$site_name = get_bloginfo( 'name' );
		if ( false === strpos( $value, $site_name ) ) {
			return $value;
		}

		return str_replace( $site_name, edd_mask_string( $site_name ), $value );
	}
}
