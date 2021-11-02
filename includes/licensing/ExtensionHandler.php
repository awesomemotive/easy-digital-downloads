<?php
/**
 * ExtensionHandler.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 * @since     2.11.4
 */

namespace EDD\Licensing;

class ExtensionHandler {

	public function init() {
		add_filter( 'edd_settings_licenses', array( $this, 'settings' ), 1 );
		add_action( 'edd_settings_tab_top', array( $this, 'licenseHelpText' ) );
		add_action( 'admin_init', array( $this, 'activateLicense' ) );
	}

	public function settings( $settings ) {
		$products = EDD()->extensionRegistry->getRegisteredProducts();
		if ( empty( $products ) ) {
			return $settings;
		}

		usort( $products, function ( LicensedExtension $a, LicensedExtension $b ) {
			return strcmp( $a->name, $b->name );
		} );

		$extensionLicenses = array();
		foreach ( $products as $product ) {
			/** @var LicensedExtension $product */
			$extensionLicenses[] = array(
				'id'      => $product->licenseOptionName,
				'name'    => $product->name,
				'desc'    => '',
				'type'    => 'license_key',
				'options' => array( 'is_valid_license_option' => $product->slug . '_license_active' ),
				'size'    => 'regular',
			);
		}

		return array_merge( $settings, $extensionLicenses );
	}

	public function licenseHelpText( $active_tab = '' ) {
		if ( 'licenses' !== $active_tab ) {
			return;
		}
		?>
		<p>
			<?php
			printf(
				__( 'Enter your extension license keys here to receive updates for purchased extensions. If your license key has expired, please <a href="%s" target="_blank">renew your license</a>.', 'easy-digital-downloads' ),
				'https://docs.easydigitaldownloads.com/article/1000-license-renewal'
			);
			?>
		</p>
		<?php
	}

	public function activateLicense() {

	}

}

$handler = new ExtensionHandler();
$handler->init();
