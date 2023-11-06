<?php
/**
 * Adds our version text to the EDD footer in the admin.
 *
 * @package     EDD
 * @subpackage  Admin/Footer
 * @since       3.2.4
 */

namespace EDD\Admin\Promos\Footer;

/**
 * Class Version
 *
 * @since 3.2.4
 */
class Version {

	/**
	 * Adds our version text to the EDD footer in the admin.
	 *
	 * @since 3.2.4
	 *
	 * @param string $version The current version text.
	 * @return string
	 */
	public static function version_message( $version ) {
		$edd_name = edd_is_pro() ? 'EDD (Pro)' : 'EDD';

		// translators: %1$s - EDD plugin name, %2$s - EDD version.
		$version .= sprintf( ' | %1$s %2$s', $edd_name, EDD_VERSION );

		return $version;
	}
}
