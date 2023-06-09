<?php
/**
 * Gets the licensing data to send to our telemetry server.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.1.1
 */
namespace EDD\Telemetry;

use EDD\Licensing\License;

class Licenses {

	/**
	 * Gets the gateway data.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	public function get() {
		$data        = $this->get_extensions();
		$pro_license = $this->get_pro_license();
		if ( $pro_license ) {
			$data[] = $pro_license;
		}

		return $data;
	}

	/**
	 * Gets the pro license status.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	private function get_pro_license() {
		if ( ! edd_is_pro() ) {
			return false;
		}
		$pro_license = new License( 'pro' );

		return array(
			'extension' => 'edd_pro',
			'status'    => $this->get_license_status( $pro_license ),
		);
	}

	/**
	 * Gets licensed extensions' statuses.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	private function get_extensions() {
		$data       = array();
		$extensions = \EDD\Extensions\get_licensed_extension_slugs();
		foreach ( $extensions as $slug ) {
			$shortname = str_replace( 'edd_', '', $slug );
			$license   = new License( $shortname );
			$data[]    = array(
				'extension' => $slug,
				'status'    => $this->get_license_status( $license ),
			);
		}

		return $data;
	}

	/**
	 * Gets the license status.
	 *
	 * @since 3.1.2
	 * @param \EDD\Licensing\License $license
	 * @return string
	 */
	private function get_license_status( $license ) {
		if ( ! empty( $license->license ) ) {
			return $license->license;
		}
		if ( ! empty( $license->error ) ) {
			return $license->error;
		}

		return 'missing';
	}
}
