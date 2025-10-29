<?php
/**
 * EDD Blocks Utility
 *
 * @package     EDD\Blocks
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Utility class.
 *
 * @since 3.6.0
 */
class Utility {

	/**
	 * Checks whether we are viewing content in the block editor.
	 *
	 * @since 3.6.0
	 * @param string $current_user_can Whether the current user needs to have a specific capability.
	 * @return false|string
	 */
	public static function is_block_editor( $current_user_can = '' ) {
		$is_block_editor = ! empty( $_GET['edd_blocks_is_block_editor'] ) ? $_GET['edd_blocks_is_block_editor'] : false;

		// If not the block editor or custom capabilities are not required, return.
		if ( ! $is_block_editor || empty( $current_user_can ) ) {
			return $is_block_editor;
		}
		$user = wp_get_current_user();

		return hash_equals( md5( $user->user_email ), $is_block_editor ) && current_user_can( $current_user_can );
	}

	/**
	 * Whether the checkout page is being previewed as a guest.
	 *
	 * @since 3.6.0
	 * @return bool
	 */
	public static function doing_guest_preview(): bool {
		$is_block_editor = (bool) self::is_block_editor();
		$is_preview      = filter_input( INPUT_GET, 'preview', FILTER_VALIDATE_BOOLEAN );

		return apply_filters( 'edd_blocks_doing_guest_preview', $is_block_editor && $is_preview );
	}

	/**
	 * Get the purchase button.
	 *
	 * @since 3.6.0
	 * @return void
	 */
	public static function do_preview_purchase_button(): void {
		if ( ! self::is_block_editor() ) {
			return;
		}

		ob_start();
		$color = edd_get_button_color_class();
		$style = edd_get_option( 'button_style', 'button' );
		$label = edd_get_checkout_button_purchase_label();
		$class = implode( ' ', array_filter( array( 'edd-submit', $color, $style ) ) );

		?>
		<input type="submit" class="<?php echo esc_attr( $class ); ?>" id="edd-purchase-button" name="edd-purchase" value="<?php echo esc_html( $label ); ?>"/>
		<?php

		echo apply_filters( 'edd_checkout_button_purchase', ob_get_clean() );
	}
}
