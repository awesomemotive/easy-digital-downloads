<?php
/**
 * Anonymize Trait
 *
 * @since 3.2.5
 * @package EDD\Telemetry\Traits
 */

namespace EDD\Telemetry\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Trait Anonymize
 *
 * @since 3.2.5
 * @package EDD\Telemetry\Traits
 */
trait Anonymize {
	/**
	 * The unique anonymized site ID.
	 *
	 * @var string
	 */
	private $id;

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

	/**
	 * Gets the unique site ID.
	 * This is generated from the home URL and two random pieces of data
	 * to create a hashed site ID that anonymizes the site data.
	 *
	 * @since 3.1.1
	 * @since 3.3.0 Moved to the Anonymize trait, to modularize the information.
	 * @return string
	 */
	private function get_id() {
		$this->id = get_option( 'edd_telemetry_uuid' );
		if ( $this->id ) {
			return $this->id;
		}
		$home_url = get_home_url();
		$uuid     = wp_generate_uuid4();
		$today    = gmdate( 'now' );
		$this->id = md5( $home_url . $uuid . $today );

		update_option( 'edd_telemetry_uuid', $this->id, false );

		return $this->id;
	}
}
