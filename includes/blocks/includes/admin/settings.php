<?php
/**
 * Block settings.
 *
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 * @since 2.0
 */

namespace EDD\Blocks\Admin\Settings;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

add_filter( 'edd_settings_general', __NAMESPACE__ . '\pages' );
/**
 * Updates some of the pages settings.
 *
 * @since 2.0
 * @param array $settings
 * @return array
 */
function pages( $settings ) {
	if ( empty( $settings['pages'] ) ) {
		return $settings;
	}

	$pages = edd_get_pages();

	$login_description  = __( 'This page must include the EDD Login block. Setting this allows the front end form to be used for resetting passwords.', 'easy-digital-downloads' );
	$login_description .= '<br />';
	$login_description .= sprintf(
		/* translators: 1. opening code tag, do not translate; 2. closing code tag, do not translate. */
		__( 'Do not use this with the %1$s[edd_login]%2$s shortcode; it does not support resetting passwords.', 'easy-digital-downloads' ),
		'<code>',
		'</code>'
	);

	// Login page.
	$login_page = array(
		array(
			'id'          => 'login_page',
			'name'        => __( 'Login Page', 'easy-digital-downloads' ),
			'desc'        => $login_description,
			'type'        => 'select',
			'options'     => $pages,
			'chosen'      => true,
			'placeholder' => __( 'Select a page', 'easy-digital-downloads' ),
		),
	);
	array_splice( $settings['pages'], -1, 0, $login_page );

	if ( ! empty( $settings['pages']['purchase_page']['desc'] ) ) {
		$description  = __( 'This is the checkout page where customers will complete their purchases.', 'easy-digital-downloads' );
		$description .= '<br />';
		$description .= sprintf(
			/* translators: 1. opening code tag, do not translate; 2. closing code tag, do not translate. */
			__( 'The Checkout block or %1$s[download_checkout]%2$s shortcode must be on this page.', 'easy-digital-downloads' ),
			'<code>',
			'</code>'
		);

		$settings['pages']['purchase_page']['desc'] = $description;
	}

	// Update the login redirect description.
	if ( ! empty( $settings['pages']['login_redirect_page']['desc'] ) ) {
		$description = sprintf(
			/* translators: 1. opening code tag, do not translate; 2. closing code tag, do not translate. */
			__( 'If a customer logs in using the EDD Login block or %1$s[edd_login]%2$s shortcode, will be redirected to this page.', 'easy-digital-downloads' ),
			'<code>',
			'</code>'
		);
		$description .= '<br />';
		$description .= __( 'This can be overridden in the block settings or shortcode parameters.', 'easy-digital-downloads' );

		$settings['pages']['login_redirect_page']['desc'] = $description;
	}

	// Update the purchase history page setting name/description.
	if ( ! empty( $settings['pages']['purchase_history_page']['desc'] ) ) {
		$description  = __( 'This page shows a complete order history for the current user, including download links.', 'easy-digital-downloads' );
		$description .= '<br />';
		$description .= sprintf(
			/* translators: 1. opening code tag, do not translate; 2. closing code tag, do not translate. */
			__( 'Either the EDD Order History block or the %1$s[purchase_history]%2$s shortcode must be on this page.', 'easy-digital-downloads' ),
			'<code>',
			'</code>'
		);

		$settings['pages']['purchase_history_page']['desc'] = $description;
		$settings['pages']['purchase_history_page']['name'] = __( 'Order History Page', 'easy-digital-downloads' );
	}

	$confirmation_description  = __( 'This page must include the EDD Confirmation block.', 'easy-digital-downloads' );
	$confirmation_description .= '<br />';
	$confirmation_description .= __( 'Use this page separately from your receipt page to ensure proper conversion tracking.', 'easy-digital-downloads' );
	$confirmation_page         = array(
		'confirmation_page' => array(
			'id'          => 'confirmation_page',
			'name'        => __( 'Confirmation Page', 'easy-digital-downloads' ),
			'desc'        => $confirmation_description,
			'type'        => 'select',
			'options'     => $pages,
			'chosen'      => true,
			'placeholder' => __( 'Select a page', 'easy-digital-downloads' ),
		),
	);

	// Insert the confirmation page after checkout and before the receipt.
	array_splice( $settings['pages'], 2, 0, $confirmation_page );

	if ( ! empty( $settings['pages']['success_page']['desc'] ) ) {
		$receipt_description  = __( 'This is the page to show a detailed receipt for an order.', 'easy-digital-downloads' );
		$receipt_description .= '<br />';
		$receipt_description .= sprintf(
			/* translators: 1. opening code tag, do not translate; 2. closing code tag, do not translate. */
			__( 'Use the EDD Receipt block or the %1$s[edd_receipt]%2$s shortcode to work with the confirmation page.', 'easy-digital-downloads' ),
			'<code>',
			'</code>'
		);

		$settings['pages']['success_page']['desc'] = $receipt_description;
		$settings['pages']['success_page']['name'] = __( 'Receipt Page', 'easy-digital-downloads' );
	}

	return $settings;
}

add_filter( 'edd_settings_misc', __NAMESPACE__ . '\button_color' );
/**
 * Adds the EDD block button color setting to the miscellaneous section.
 *
 * @since 2.0
 * @param array $settings
 * @return array
 */
function button_color( $settings ) {
	$color_settings = array(
		'button_colors' => array(
			'id'   => 'blocks_button_colors',
			'name' => __( 'Default Button Colors', 'easy-digital-downloads' ),
			'type' => 'hook',
		),
	);
	array_splice( $settings['button_text'], 1, 0, $color_settings );

	$new_colors = edd_get_option( 'button_colors' );
	if ( ! empty( $new_colors['background'] ) ) {
		unset( $settings['button_text']['checkout_color'] );
	}

	return $settings;
}

add_action( 'edd_blocks_button_colors', __NAMESPACE__ . '\button_colors' );
/**
 * Renders the settings field for the button colors.
 *
 * @since 2.0
 * @param array $args
 * @return void
 */
function button_colors( $args ) {
	$colors   = edd_get_option( 'button_colors' );
	$settings = array(
		'background' => __( 'Background', 'easy-digital-downloads' ),
		'text'       => __( 'Text', 'easy-digital-downloads' ),
	);

	echo '<div class="edd-settings-colors">';
	foreach ( $settings as $setting => $label ) {
		$color_value = ! empty( $colors[ $setting ] ) ? $colors[ $setting ] : '';
		?>
		<div class="edd-settings-color">
			<label for="edd_settings[button_colors][<?php echo esc_attr( $setting ); ?>]"><?php echo esc_html( $label ); ?></label>
			<input type="text" class="edd-color-picker" id="edd_settings[button_colors][<?php echo esc_attr( $setting ); ?>]" name="edd_settings[button_colors][<?php echo esc_attr( $setting ); ?>]" value="<?php echo esc_attr( $color_value ); ?>" data-default-color="" />
		</div>
		<?php
	}
	echo '</div>';
}

add_filter( 'edd_settings_misc', __NAMESPACE__ . '\disable_redownload' );
/**
 * Update the text for the `disable_redownload` setting.
 *
 * @since 2.0.4
 * @param array $settings
 * @return array
 */
function disable_redownload( $settings ) {
	if ( ! empty( $settings['file_downloads']['disable_redownload']['desc'] ) ) {
		$settings['file_downloads']['disable_redownload']['desc'] = __( 'Do not allow users to redownload items from their order history.', 'easy-digital-downloads' );
	}

	return $settings;
}
