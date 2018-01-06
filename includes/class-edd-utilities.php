<?php
/**
 * EDD Utilities Bootstrap
 *
 * @package     EDD
 * @subpackage  Utilities
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

/**
 * Class that bootstraps various utilities leveraged in EDD core.
 *
 * @since 3.0
 */
class EDD_Utilities {

	/**
	 * Sets up instantiating core utilities.
	 *
	 * @since 3.0
	 */
	public function __construct() {
		$this->includes();
	}

	/**
	 * Loads needed files for core utilities.
	 *
	 * @since 3.0
	 */
	private function includes() {
		$utils_dir = EDD_PLUGIN_DIR . 'includes/utilities/';

		// Interfaces.
		require_once $utils_dir . 'interface-edd-exception.php';
		require_once $utils_dir . 'interface-static-registry.php';

		// Exceptions.
		require_once $utils_dir . 'class-edd-exception.php';
		require_once $utils_dir . 'exceptions/class-attribute-not-found.php';
		require_once $utils_dir . 'exceptions/class-invalid-argument.php';
		require_once $utils_dir . 'exceptions/class-invalid-parameter.php';

		// Registry.
		require_once $utils_dir . 'class-registry.php';
	}

	/**
	 * Retrieves a given registry instance by name.
	 *
	 * @since 3.0
	 *
	 * @param string $name Registry name.
	 * @return \EDD\Utils\Registry|null Null if the registry doesn't exist, otherwise the object instance.
	 */
	public function get_registry( $name ) {
		switch( $name ) {
			case 'reports':
				if ( class_exists( '\EDD\Admin\Reports\Registry' ) ) {
					$registry = \EDD\Admin\Reports\Registry::instance();
				}
				break;

			default:
				$registry = null;
				break;
		}

		return $registry;
	}

}
