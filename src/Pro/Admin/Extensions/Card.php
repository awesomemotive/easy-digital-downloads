<?php

namespace EDD\Pro\Admin\Extensions;

class Card extends \EDD\Admin\Extensions\Card {

	/**
	 * Gets the settings link.
	 *
	 * @since 3.1.1
	 * @param ProductData $product_data The product data.
	 * @return void
	 */
	protected function do_settings_link( $product_data ) {
		if ( empty( $product_data->tab ) || empty( $product_data->pass_id ) ) {
			return;
		}
		$args = array(
			'page' => 'edd-settings',
			'tab'  => urlencode( $product_data->tab ),
		);
		if ( ! empty( $product_data->section ) ) {
			$args['section'] = urlencode( $product_data->section );
		}
		$url = edd_get_admin_url( $args );
		?>
		<a
			href="<?php echo esc_url( $url ); ?>"
			class="button edd-extension-manager__button-settings"
		>
			<span class="screen-reader-text"><?php esc_html_e( 'Configure Settings', 'easy-digital-downloads' ); ?></span>
		</a>
		<?php
	}

	/**
	 * Selects which action button should show.
	 *
	 * @since 3.1.1
	 * @param array $args
	 * @return void
	 */
	protected function select_installer_action( $args ) {
		if ( ! empty( $args['action'] ) && in_array( $args['action'], array( 'activate', 'deactivate' ), true ) && ! empty( $args['plugin'] ) ) {
			$buttons = new Buttons();
			$buttons->get_activate_deactivate_button( $args, true );
			return;
		}
		parent::select_installer_action( $args );
	}
}
