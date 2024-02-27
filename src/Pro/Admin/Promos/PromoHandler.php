<?php
/**
 * Promo Handler
 *
 * Handles logic for displaying and dismissing promotional notices.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.1.1
 */

namespace EDD\Pro\Admin\Promos;

class PromoHandler extends \EDD\Admin\Promos\PromoHandler {

	/**
	 * Registered notices.
	 *
	 * @var array
	 */
	protected $pro_notices = array(
		'\\EDD\\Pro\\Admin\\Promos\\Notices\\InactivePro',
	);

	/**
	 * Lite notices to remove.
	 *
	 * @var array
	 */
	protected $lite_notices_to_remove = array(
		'\\EDD\\Admin\\Promos\\Notices\\License_Upgrade_Notice',
		'\\EDD\\Admin\\Promos\\Notices\\Lite',
	);

	/**
	 * Gets the notices.
	 * This method overrides the parent method if an inactive pro install is detected.
	 *
	 * @return void
	 */
	protected function get_notices() {
		// Search for lite notices to remove.
		foreach ( $this->lite_notices_to_remove as $notice_to_remove ) {
			$notice_key = array_search( $notice_to_remove, $this->notices, true );
			if ( ! is_null( $notice_key ) ) {
				unset( $this->notices[ $notice_key ] );
			}
		}

		if ( ! edd_is_inactive_pro() ) {
			$inactive_pro_key = array_search( '\\EDD\\Pro\\Admin\\Promos\\Notices\\InactivePro', $this->pro_notices, true );
			if ( ! is_null( $inactive_pro_key ) ) {
				unset( $this->pro_notices[ $inactive_pro_key ] );
			}
		}

		return array_unique( array_merge( $this->notices, $this->pro_notices ) );
	}
}
