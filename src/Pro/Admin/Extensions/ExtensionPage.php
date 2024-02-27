<?php

namespace EDD\Pro\Admin\Extensions;

defined( 'ABSPATH' ) || exit;

use EDD\Admin\Extensions\ProductData;

/**
 * Class to handle the extensions page.
 *
 * @since 3.1.1
 */
class ExtensionPage extends \EDD\Admin\Extensions\ExtensionPage {
	use \EDD\Admin\Extensions\Traits\Buttons;

	/**
	 * Whether the current active pass can install an extension.
	 * We assume true by default.
	 *
	 * @var bool
	 */
	private $can_install = true;

	/**
	 * The license object.
	 *
	 * @var \EDD\Licensing\License
	 */
	private $license;

	/**
	 * The class constructor.
	 *
	 * This checks if the user is a super admin on multisite. If not, they cannot install extensions.
	 *
	 * @since 3.2.0
	 */
	public function __construct() {
		parent::__construct();

		if ( ( is_multisite() && ! is_super_admin() ) || edd_is_inactive_pro() || $this->is_license_expired() ) {
			$this->can_install = false;
		}
	}

	/**
	 * Outputs the cards.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	protected function do_cards() {

		// If this is an inactive pro install, we will show all extensions without the break in between.
		if ( $this->show_inactive_pro_message() ) {
			$this->can_install = false;
			return;
		}

		// At this point, we know the user can install at least some extensions.
		?>
		<div class="edd-extension-manager__card-group">
			<?php
			foreach ( $this->get_product_data() as $item_id => $extension ) {
				$this->maybe_insert_break( $extension );
				$this->do_single_extension_card( $item_id );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Gets the heading text for the extensions page.
	 *
	 * @since 3.1.1
	 * @return string
	 */
	protected function get_heading_text() {
		return ! edd_is_inactive_pro() ? __( 'Pro Available Extensions', 'easy-digital-downloads' ) : parent::get_heading_text();
	}

	/**
	 * If a pass hasn't been saved, show the text offering to add it.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	protected function show_missing_key_question() {
		if ( ! $this->can_show_pass_refresh() ) {
			return;
		}
		printf(
			wp_kses_post(
				/* translators: 1. opening anchor tag; 2. closing anchor tag. */
				__( 'Missing an extension that you think you should be able to see? %1$sRefresh your extensions now%2$s.', 'easy-digital-downloads' )
			),
			'<a href="' . esc_url( add_query_arg( 'edd_action', 'refresh_pass_status' ) ) . '">',
			'</a>'
		);
	}

	/**
	 * Maybe add a break and start showing extensions they cannot install.
	 *
	 * @since 3.1.1
	 * @param array $extension
	 * @return void
	 */
	private function maybe_insert_break( $extension ) {
		if ( ! $this->can_install ) {
			return;
		}
		if ( ! empty( $extension['terms'] ) && property_exists( $extension['terms'], 'recommended' ) ) {
			return;
		}
		if ( ! empty( $extension['categories'] ) && ! $this->pass_manager->can_access_categories( $extension['categories'] ) ) {
			$this->can_install = false;
			?>
			</div>
			<div class="edd-extension-manager__unlock">
				<h2><?php esc_html_e( 'Unlock More Extensions', 'easy-digital-downloads' ); ?></h2>
				<p>
					<?php
					$url = edd_link_helper(
						'https://easydigitaldownloads.com/your-account/',
						array(
							'utm_medium'  => 'extensions-page',
							'utm_content' => 'unlock-more',
						)
					);
					printf(
						wp_kses_post(
							/* translators: 1. opening anchor tag; 2. closing anchor tag. */
							__( 'Want to get even more extensions? Upgrade your %1$sEasy Digital Downloads license%2$s and unlock the following extensions.', 'easy-digital-downloads' )
						),
						'<a href="' . esc_url( $url ) . '">',
						'</a>'
					);
					?>
				</p>
			</div>
			<div class="edd-extension-manager__card-group edd-extension-manager__disable">
			<?php
		}
	}

	/**
	 * Update the button parameters.
	 *
	 * @since 3.1.1
	 * @param ProductData $product_data The extension data returned from the Products API.
	 * @param bool|int    $item_id
	 * @return array
	 */
	protected function get_button_parameters( ProductData $product_data, $item_id = false ) {
		$button = parent::get_button_parameters( $product_data, $item_id );

		// If Pro is inactive, show a disabled button.
		if ( edd_is_inactive_pro() ) {
			$button['button_text'] = __( 'License Required', 'easy-digital-downloads' );
			$button['disabled']    = true;
			unset( $button['href'] );
		}

		// If the user doesn't have access to the extension, show a disabled button.
		if ( ! edd_is_inactive_pro() && $this->manager->pass_can_download( $product_data->pass_id ) ) {
			$button['button_text'] = __( 'Log In to Download', 'easy-digital-downloads' );
			unset( $button['button_class'] );
		}

		// If the license is expired, show a disabled button.
		if ( $this->is_license_expired() && $this->manager->pass_can_download( $product_data->pass_id ) ) {
			$button['button_text'] = __( 'License Expired', 'easy-digital-downloads' );
			$button['disabled']    = true;
			unset( $button['href'] );
		}

		if ( ! $this->can_show_install_button( $product_data ) ) {
			return $button;
		}

		$button['button_text']  = __( 'Install', 'easy-digital-downloads' );
		$button['action']       = 'install';
		$button['product']      = $item_id;
		$button['button_class'] = 'edd-button__install';
		unset( $button['href'] );
		unset( $button['new_tab'] );

		return $button;
	}

	/**
	 * Checks the current user's capability level.
	 *
	 * @since 3.1.1
	 * @param string $capability The capability to check.
	 * @return bool
	 */
	protected function current_user_can( $capability = 'activate_plugins' ) {
		return $this->can_install && current_user_can( $capability ) && ! edd_is_inactive_pro();
	}

	/**
	 * Gets the upgrade URL for the button.
	 *
	 * @since 3.1.1
	 * @param ProductData $product_data The product data object.
	 * @param int         $item_id      The item/product ID.
	 * @param bool        $has_access   Whether the user already has access to the extension (based on pass level).
	 * @return string
	 */
	protected function get_upgrade_url( ProductData $product_data, $item_id, $has_access = false ) {
		if ( $has_access ) {
			$url = 'https://easydigitaldownloads.com/your-account/your-downloads/';
		} else {
			$url = 'https://easydigitaldownloads.com/pricing';
		}

		$utm_parameters = array(
			'utm_medium'  => 'extensions-page',
			'utm_content' => $product_data->slug,
		);

		return edd_link_helper(
			$url,
			$utm_parameters
		);
	}

	/**
	 * Shows the inactive pro message.
	 *
	 * @since 3.2.0
	 * @return bool True if a message was shown, false otherwise.
	 */
	private function show_inactive_pro_message() {
		if ( ! edd_is_inactive_pro() ) {
			return false;
		}
		if ( $this->is_license_expired() ) {
			parent::do_cards();
			return true;
		}

		$settings_url = edd_get_admin_url(
			array(
				'page' => 'edd-settings',
			)
		);

		$purchase_url = edd_link_helper(
			'https://easydigitaldownloads.com/pricing',
			array(
				'utm_medium'  => 'extensions-page',
				'utm_content' => 'missing-key-purchase',
			)
		);
		?>
		<div class="edd-extension-manager__key-notice">
			<p><?php esc_html_e( 'You have not yet added a license key. A valid license key is required in order to use our extensions.', 'easy-digital-downloads' ); ?></p>
			<div class="edd-extension-manager__key-actions">
				<a href="<?php echo esc_url( $settings_url ); ?>" class="button button-primary edd-extension-manager__action-settings"><?php esc_html_e( 'Enter License Key', 'easy-digital-downloads' ); ?></a>
				<a href="<?php echo $purchase_url; ?>" class="button edd-extension-manager__action-upgrade" target="_blank"><?php esc_html_e( 'Purchase License', 'easy-digital-downloads' ); ?></a>
			</div>
		</div>
		<?php
		parent::do_cards();

		return true;
	}

	/**
	 * Whether the install button should be shown.
	 *
	 * @param ProductData $product_data The product data object.
	 * @return bool
	 */
	private function can_show_install_button( $product_data ) {
		if ( ! $this->can_install ) {
			return false;
		}

		if ( empty( $product_data->basename ) || $this->manager->is_plugin_installed( $product_data->basename ) ) {
			return false;
		}

		// Check the product categories before the pass ID. This will catch All Access passes.
		if ( ! empty( $product_data->categories ) && $this->pass_manager->can_access_categories( $product_data->categories ) ) {
			return true;
		}

		// Check the pass ID.
		return $this->manager->pass_can_download( $product_data->pass_id );
	}

	/**
	 * Whether the license is expired.
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	private function is_license_expired() {
		if ( is_null( $this->license ) ) {
			$this->license = new \EDD\Licensing\License( 'pro' );
		}

		return $this->license->is_expired();
	}
}
