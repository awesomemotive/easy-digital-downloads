<?php
/**
 * License Registry
 *
 * Responsible for holding information about premium EDD extensions in use on this site.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 * @since     2.11.4
 */

namespace EDD\Extensions;

class ExtensionRegistry extends \ArrayObject {

	/**
	 * Adds an extension to the registry.
	 *
	 * @since 2.11.4
	 *
	 * @param string      $pluginFile     Path to the plugin's main file.
	 * @param string      $pluginName     Display name of the plugin.
	 * @param int         $pluginId       EDD product ID for the plugin.
	 * @param string      $currentVersion Current version number.
	 * @param string|null $optionName     Option name where the license key is stored. If omitted, automatically generated.
	 */
	public function addExtension( $pluginFile, $pluginName, $pluginId, $currentVersion, $optionName = null ) {
		if ( $this->offsetExists( $pluginId ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The extension %d is already registered.',
				$pluginId
			) );
		}

		$this->offsetSet(
			$pluginId,
			new Handler( $pluginFile, $pluginId, $pluginName, $currentVersion, $optionName )
		);
	}

	/**
	 * Returns all registered extensions, regardless of whether they have licenses activated.
	 *
	 * At some point we could make this public, just making it private for now so that we have
	 * flexibility to change exactly what it returns in the future.
	 *
	 * @since 2.11.4
	 * @return Handler[]
	 */
	private function getExtensions() {
		return $this->getArrayCopy();
	}

	/**
	 * Counts the number of licensed extensions active on this site.
	 * Note: This only looks at extensions registered via this registry, then filters down
	 * to those that have a license key entered. It does not check to verify if the license
	 * key is actually valid / not expired.
	 *
	 * @since 2.11.4
	 *
	 * @return int
	 */
	public function countLicensedExtensions() {
		$licensedExtensions = array_filter( $this->getExtensions(), function ( Handler $license ) {
			return ! empty( $license->license_key );
		} );

		return count( $licensedExtensions );
	}

}
