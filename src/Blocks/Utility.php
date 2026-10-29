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
	 * Whether the block checkout form top has already fired during the current checkout render.
	 *
	 * @since 3.7.3
	 * @var bool
	 */
	private static bool $did_checkout_form_top = false;

	/**
	 * Checks whether we are viewing content in the block editor.
	 *
	 * @since 3.6.0
	 * @since 3.7.1 Default capability changed to `edit_posts`.
	 * @since 3.7.3 Returns false for a request or block-context value that is neither a boolean nor a string.
	 * @param string    $current_user_can Whether the current user needs to have a specific capability.
	 * @param \WP_Block $block The block object.
	 * @return bool
	 */
	public static function is_block_editor( $current_user_can = 'edit_posts', $block = null ) {
		$is_block_editor = ! empty( $_GET['edd_blocks_is_block_editor'] ) ? $_GET['edd_blocks_is_block_editor'] : false;

		if ( empty( $is_block_editor ) && ! empty( $block ) && isset( $block->context['edd/previewMode'] ) ) {
			$is_block_editor = $block->context['edd/previewMode'];
		}

		if ( ! $is_block_editor ) {
			return false;
		}

		// The block-context value is a boolean set by the editor itself, but still requires the capability check.
		if ( is_bool( $is_block_editor ) ) {
			return current_user_can( $current_user_can );
		}

		// Only a string can match the token, and hash_equals() throws on anything else.
		if ( ! is_string( $is_block_editor ) ) {
			return false;
		}

		$user = wp_get_current_user();

		return hash_equals( md5( $user->user_email ), $is_block_editor ) && current_user_can( $current_user_can );
	}

	/**
	 * Whether the checkout page is being previewed as a guest.
	 *
	 * @since 3.6.0
	 * @param null|\WP_Block $block The block object.
	 * @return bool
	 */
	public static function doing_guest_preview( $block = null ): bool {
		$is_block_editor = (bool) self::is_block_editor( 'edit_shop_payments', $block );
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

	/**
	 * Resets the checkout form top flag so the next checkout render fires the hook again.
	 *
	 * @since 3.7.3
	 * @return void
	 */
	public static function reset_checkout_form_top(): void {
		self::$did_checkout_form_top = false;
	}

	/**
	 * Triggers the `edd_checkout_form_top` hook. The hook is invoked from multiple blocks
	 * to ensure that whichever is first triggers it.
	 *
	 * @since 3.7.0
	 * @since 3.7.3 Dedupes per checkout render instead of per request.
	 * @param array $block_attributes The array of block attributes.
	 * @return void
	 */
	public static function do_checkout_form_top( array $block_attributes ): void {
		if ( self::$did_checkout_form_top ) {
			return;
		}

		// Payment-info declares no attributes, so if it fired the hook first we
		// fall back to the checkout defaults so UserDetails still renders.
		if ( empty( $block_attributes ) ) {
			$block_attributes = \EDD\Blocks\Checkout\Attributes::get();
		}

		// The blocks handle the these differently from the legacy shortcode.
		remove_action( 'edd_checkout_form_top', 'edd_show_payment_icons' );
		remove_action( 'edd_checkout_form_top', 'edd_discount_field', -1 );

		self::$did_checkout_form_top = true;

		/**
		 * Hooks in at the start of the blocks purchase form.
		 *
		 * @since 3.6.0
		 * @param array $block_attributes The block attributes.
		 */
		do_action( 'edd_checkout_form_top', $block_attributes );
	}
}
