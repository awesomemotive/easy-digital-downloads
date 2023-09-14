<?php
/**
 * Downloads as Services
 * @package     EDD
 * @subpackage  Services
 * @copyright   Copyright (c) 2023, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.2.0
 */
namespace EDD\Downloads;

defined( 'ABSPATH' ) || exit;

class Service extends \EDD_Download {

	/**
	 * Determines if the download is a service.
	 *
	 * @since 3.2.0
	 * @param null|int $price_id
	 * @return bool
	 */
	public function is_service( $price_id = null ) {

		// If the download has files, it's not a service.
		if ( edd_get_download_files( $this->ID, $price_id ) ) {
			return false;
		}

		if ( 'service' === $this->get_type() ) {
			return true;
		}

		$terms = array_filter( edd_get_option( 'edd_das_service_categories', array() ) );

		return has_term( $terms, 'download_category', $this->ID );
	}
}
