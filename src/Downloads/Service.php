<?php
/**
 * Downloads as Services
 *
 * @package     EDD
 * @subpackage  Services
 * @copyright   Copyright (c) 2023, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.2.0
 */

namespace EDD\Downloads;

defined( 'ABSPATH' ) || exit;

/**
 * Class Service
 *
 * @since 3.2.0
 * @package EDD\Downloads
 */
class Service extends \EDD_Download {

	/**
	 * Determines if the download is a service.
	 *
	 * @since 3.2.0
	 * @param null|int $price_id Optional. The price ID to check.
	 * @return bool
	 */
	public function is_service( $price_id = null ) {

		// If the download has files, it's not a service.
		if ( edd_get_download_files( $this->ID, $price_id ) ) {
			return false;
		}

		if ( 'service' === $this->type ) {
			return true;
		}

		// If the product type is explicitly set to something other than the default, return false.
		if ( ! in_array( $this->type, array( '', 'default' ), true ) ) {
			return false;
		}

		$terms = array_filter( edd_get_option( 'edd_das_service_categories', array() ) );
		if ( empty( $terms ) ) {
			return false;
		}

		return has_term( $terms, 'download_category', $this->ID );
	}
}
