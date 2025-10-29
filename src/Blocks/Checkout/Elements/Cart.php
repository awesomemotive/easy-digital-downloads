<?php
/**
 * Checkout Block Elements Cart
 *
 * @package     EDD\Blocks\Checkout\Elements
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Blocks\Checkout\Elements;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Cart class.
 *
 * @since 3.6.0
 */
class Cart {

	/**
	 * Helper function to render the cart template with all required variables set.
	 * This ensures consistent variable setup across all includes.
	 *
	 * @since 3.6.0
	 * @param array $args Optional arguments to override defaults.
	 * @return void
	 */
	public static function render( $args = array() ) {
		$defaults = array(
			'is_cart_widget' => false,
			'doing_ajax'     => edd_doing_ajax(),
		);

		$template_vars = wp_parse_args( $args, $defaults );

		if ( ! isset( $template_vars['block_attributes'] ) ) {
			$template_vars['block_attributes'] = \EDD\Blocks\Checkout\Attributes::get();
		}

		if ( ! isset( $template_vars['cart_items'] ) ) {
			$template_vars['cart_items'] = edd_get_cart_contents();
		}

		$block_attributes = $template_vars['block_attributes'];
		$is_cart_widget   = $template_vars['is_cart_widget'];
		$cart_items       = $template_vars['cart_items'];

		/**
		 * The ajax request replaces only the #edd_checkout_cart_form element,
		 * so we need to not trigger the action hooks or add the container during an ajax request.
		 */
		if ( $template_vars['doing_ajax'] ) {
			include EDD_BLOCKS_DIR . 'views/checkout/cart/cart.php';
			return;
		}

		?>
		<div class="edd-blocks__cart">
			<?php
			do_action( 'edd_before_checkout_cart', $block_attributes );
			include EDD_BLOCKS_DIR . 'views/checkout/cart/cart.php';
			do_action( 'edd_after_checkout_cart', $block_attributes );
			?>
		</div>
		<?php
	}
}
