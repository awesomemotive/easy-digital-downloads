<?php
/**
 * License Registry
 *
 * Responsible for holding all the `EDD_License` objects instantiated by extensions.
 * This allows EDD core to be aware of all the premium extensions that are activated
 * on the site.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 * @since     2.11.4
 */

namespace EDD\Licensing;

class LicenseRegistry extends \ArrayObject {

	/**
	 * Adds a license to the registry.
	 *
	 * @since 2.11.4
	 *
	 * @param int          $extensionId The extension's unique product ID.
	 * @param \EDD_License $license     License object for this extension.
	 */
	public function addLicense( $extensionId, \EDD_License $license ) {
		if ( $this->offsetExists( $extensionId ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The extension %d is already registered.',
				$extensionId
			) );
		}

		$this->offsetSet( $extensionId, $license );
	}

	/**
	 * Retrieves a license object by its ID.
	 *
	 * @since 2.11.4
	 *
	 * @param int $extensionId The extension's unique product ID.
	 *
	 * @return \EDD_License
	 * @throws \Exception
	 */
	public function getLicense( $extensionId ) {
		if ( ! $this->offsetExists( $extensionId ) ) {
			throw new \Exception( sprintf(
				'The extension %d is not registered.',
				$extensionId
			) );
		}

		return $this->offsetGet( $extensionId );
	}

	/**
	 * Returns all registered licenses.
	 *
	 * @since 2.11.4
	 *
	 * @return array
	 */
	public function getLicenses() {
		return $this->getArrayCopy();
	}

}
