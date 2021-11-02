<?php
/**
 * LicensedExtension.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 * @since     2.11.4
 */

namespace EDD\Licensing;

class LicensedExtension {

	/**
	 * @var string Plugin file.
	 */
	public $pluginFile;

	/**
	 * @var string Name of the product.
	 */
	public $name;

	/**
	 * @var string Current version.
	 */
	public $version;

	/**
	 * @var string A slug-friendly version of the product name. Used in building options.
	 */
	public $slug;

	/**
	 * @var string Name of the option where the license key is stored.
	 */
	public $licenseOptionName;

	/**
	 * @var int Unique ID of the product.
	 */
	public $productId;

	/**
	 * @var string License key for this product.
	 */
	public $license;

	/**
	 * @param string      $pluginFile
	 * @param string      $name
	 * @param string      $version
	 * @param string      $productId
	 * @param string|null $optionName For backwards compatibility only.
	 */
	public function __construct( $pluginFile, $name, $version, $productId, $optionName = null ) {
		$this->pluginFile        = $pluginFile;
		$this->name              = $name;
		$this->version           = $version;
		$this->productId         = $productId;
		$this->slug              = 'edd_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $this->name ) ) );
		$this->licenseOptionName = $this->slug . '_license_key';

		$this->license = trim( edd_get_option( $this->licenseOptionName, '' ) );

		/**
		 * Allows for backwards compatibility with old license options,
		 * i.e. if the plugins had license key fields previously, the license
		 * handler will automatically pick these up and use those in lieu of the
		 * user having to reactivate their license.
		 */
		if ( empty( $this->license ) && ! empty( $optionName ) ) {
			$oldLicense = edd_get_option( $optionName );
			if ( ! empty( $oldLicense ) ) {
				$this->license = trim( $oldLicense );

				edd_update_option( $this->licenseOptionName, sanitize_text_field( trim( $this->license ) ) );
				edd_delete_option( $optionName );
			}
		}
	}

}
