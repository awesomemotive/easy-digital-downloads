<?php
/**
 * ExportLoader.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 * @since     3.x
 */

namespace EDD\Admin\Export;

class ExportLoader {

	/**
	 * Bootstraps the exporter.
	 *
	 * @since 3.x
	 */
	public static function bootstrap() {
		require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/export/class-batch-export.php';

		/**
		 * Initializes after the bootstrap has completed.
		 *
		 * @since 3.x
		 *
		 * @param ExportRegistry $registry
		 */
		do_action( 'edd_export_init', \EDD\Admin\Export\ExportRegistry::instance() );
	}

}
