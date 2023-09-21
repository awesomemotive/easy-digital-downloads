<?php
/**
 * Extensions
 *
 * Manages automatic installation/activation for all extensions.
 *
 * @package     EDD
 * @subpackage  Extensions
 * @copyright   Copyright (c) 2022, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.1.1
 */
namespace EDD\Admin\Extensions;

use EDD\Admin\Extensions\Extension;

class ExtensionPage extends Extension {

	/**
	 * The required AA pass level.
	 */
	const PASS_LEVEL = null;

	/**
	 * Renders the extensions page content.
	 *
	 * @return void
	 */
	public function init() {
		?>
		<div class="wrap edd-extension-manager__wrap">
			<div class="wp-header-end"></div>
			<div class="edd-extension-manager__bar">
				<div class="edd-extension-manager__bar-description">
					<div class="edd-extension-manager__bar-heading">
						<h2><?php echo esc_html( $this->get_heading_text() ); ?></h2>
						<?php $this->refresh(); ?>
					</div>
				</div>
				<div class="edd-extension-manager__bar-control">
					<label for="edd-extension-manager__bar-search" class="screen-reader-text"><?php esc_html_e( 'Search Extensions', 'easy-digital-downloads' ); ?></label>
					<input
						type="search"
						class="regular-text"
						id="edd-extension-manager__bar-search"
						placeholder="<?php esc_html_e( 'Search Extensions', 'easy-digital-downloads' ); ?>"
					>
				</div>
			</div>
			<p>
				<?php echo wp_kses_post( $this->get_intro_text() ); ?><br />
				<?php $this->show_missing_key_question(); ?>
			</p>
			<?php $this->do_cards(); ?>
		</div>
		<?php
	}

	/**
	 * @inheritDoc
	 * @since 3.1.1
	 * @return array
	 */
	protected function get_active_parameters( $product_data, $item_id ) {
		return $this->get_button_parameters( $product_data, $item_id );
	}

	/**
	 * Outputs the cards.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	protected function do_cards() {
		?>
		<div class="edd-extension-manager__card-group">
			<?php
			foreach ( $this->get_product_data() as $item_id => $extension ) {
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
		return __( 'Available Extensions', 'easy-digital-downloads' );
	}

	/**
	 * Shows a button to perform a pass "refresh" or check.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	protected function refresh() {
		if ( ! $this->can_show_pass_refresh() ) {
			return;
		}
		?>
		<a class="button button-primary" href="<?php echo esc_url( add_query_arg( 'edd_action', 'refresh_pass_status' ) ); ?>"><?php esc_html_e( 'Refresh Extensions', 'easy-digital-downloads' ); ?></a>
		<?php
	}

	/**
	 * Whether the pass refresh messaging should show.
	 *
	 * @since 3.1.1
	 * @return bool
	 */
	protected function can_show_pass_refresh() {
		if ( get_transient( 'edd_pass_refreshed' ) ) {
			return false;
		}

		return ! empty( get_site_option( 'edd_pro_license_key' ) );
	}

	/**
	 * Gets the intro text for the extensions page.
	 *
	 * @since 3.1.1
	 * @return string
	 */
	protected function get_intro_text() {
		if ( ! empty( $this->pass_manager->highest_pass_id ) ) {
			/* translators: the active pass name */
			return sprintf( __( 'Add functionality to your Easy Digital Downloads powered store with your <strong>%s</strong>.', 'easy-digital-downloads' ), $this->pass_manager->get_pass_name() );
		}

		return __( 'Add functionality to your Easy Digital Downloads powered store.', 'easy-digital-downloads' );
	}

	/**
	 * If a pass hasn't been saved, show the text offering to add it.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	protected function show_missing_key_question() {
		if ( ! empty( $this->pass_manager->highest_pass_id ) && edd_is_pro() ) {
			return;
		}

		// If this is Lite, and there is a pass active, mention that (Pro) is needed to install addons.
		if ( $this->pass_manager->has_pass() ) {
			$url = edd_get_admin_url(
				array(
					'page' => 'edd-settings',
				)
			);

			wp_kses_post(
				printf(
					// translators: 1. pass name; 2. opening anchor tag; 3. closing anchor tag.
					__( 'Using the 1-Click Installation feature requires Easy Digital Downloads (Pro), which you have access to with your %1$s. %2$sInstall (Pro) now%3$s.', 'easy-digital-downloads' ),
					$this->pass_manager->get_pass_name(),
					'<a href="' . esc_url( $url ) . '">',
					'</a>'
				)
			);

			return;
		}

		$url = edd_get_admin_url(
			array(
				'page' => 'edd-settings',
			)
		);
		printf(
			wp_kses_post(
				/* translators: 1. opening anchor tag; 2. closing anchor tag. */
				__( 'Missing access to an extension? %1$sAdd your license key now%2$s.', 'easy-digital-downloads' )
			),
			'<a href="' . esc_url( $url ) . '">',
			'</a>'
		);
	}

	/**
	 * Updates the card configuration.
	 *
	 * @since 3.1.1
	 * @param ProductData $product_data The extension data returned from the Products API.
	 * @return array
	 */
	protected function get_configuration( ProductData $product_data ) {

		return array(
			'style' => 'installer',
		);
	}

	/**
	 * Update the button parameters.
	 *
	 * @since 3.1.1
	 * @param ProductData $product_data The extension data returned from the Products API.
	 * @param bool|int $item_id
	 * @return array
	 */
	protected function get_button_parameters( ProductData $product_data, $item_id = false ) {
		$button = parent::get_button_parameters( $product_data, $item_id );

		// Lite can show two cases, since you can have a pass activated on lite, but lite cannot install addons.
		if ( $this->pass_manager->has_pass() && ! edd_is_pro() ) {
			$button['button_text'] = __( 'EDD (Pro) Required', 'easy-digital-downloads' );
			$button['disabled']    = true;
			unset( $button['href'] );
		} else {
			$button['button_text']  = __( 'Upgrade Now', 'easy-digital-downloads' );
			$button['button_class'] = 'button-primary edd-promo-notice__trigger';
			$button['type']         = 'extension';
		}

		return $button;
	}

	/**
	 * Overrides the body array sent to the Products API.
	 * Download category 1592 is "extensions".
	 *
	 * @since 3.1.1
	 * @return array
	 */
	protected function get_api_body() {
		return array( 'category' => 1592 );
	}

	/**
	 * Whether the current extension is activated.
	 * Not used here for now but required since it's an abstract method.
	 *
	 * @since 3.1.1
	 * @return bool
	 */
	protected function is_activated() {
		return false;
	}

	/**
	 * Checks the current user's capability level.
	 *
	 * @since 3.1.1
	 * @param string $capability
	 * @return bool
	 */
	protected function current_user_can( $capability = 'activate_plugins' ) {
		return false;
	}

	/**
	 * Gets the upgrade URL for the button.
	 *
	 * @since 3.1.1
	 * @param ProductData $product_data The product data object.
	 * @param int                               $item_id      The item/product ID.
	 * @param bool                              $has_access   Whether the user already has access to the extension (based on pass level).
	 * @return string
	 */
	protected function get_upgrade_url( ProductData $product_data, $item_id, $has_access = false ) {
		if ( $has_access ) {
			$url = 'https://easydigitaldownloads.com/your-account/your-downloads/';
		} else {
			$url = 'https://easydigitaldownloads.com/lite-upgrade';
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
}
