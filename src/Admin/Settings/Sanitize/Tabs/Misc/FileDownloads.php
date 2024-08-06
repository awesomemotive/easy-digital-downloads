<?php
/**
 * Sanitizes the File Downloads section.
 *
 * @since 3.3.3
 * @package EDD\Admin\Settings\Sanitize\Tabs\Misc
 */

namespace EDD\Admin\Settings\Sanitize\Tabs\Misc;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Admin\Settings\Sanitize\Tabs\Section;

/**
 * Sanitizes the File Downloads section.
 *
 * @since 3.3.3
 */
class FileDownloads extends Section {
	/**
	 * Handle additional processing for the File Downloads section.
	 *
	 * @since 3.3.3
	 * @param array $input The array of settings for the settings tab.
	 * @return array
	 */
	protected static function additional_processing( $input ) {
		if ( strtolower( edd_get_file_download_method() ) !== strtolower( $input['download_method'] ) || ! edd_htaccess_exists() ) {
			// Force the .htaccess files to be updated if the Download method was changed.
			edd_create_protection_files( true, $input['download_method'] );
		}

		return $input;
	}
}
