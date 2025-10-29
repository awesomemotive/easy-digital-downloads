<?php
/**
 * Checkout Block Elements Purchase Form
 *
 * @package     EDD\Blocks\Checkout\Elements
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Blocks\Checkout\Elements;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Purchase Form class.
 *
 * @since 3.6.0
 */
class PurchaseForm {

	/**
	 * Outputs the purchase form for checkout.
	 *
	 * @since 3.6.0
	 * @param array $block_attributes The block attributes.
	 * @return void
	 */
	public static function render( $block_attributes ) {
		?>
		<div class="edd-blocks__purchase-form">
			<?php
			$payment_mode = edd_get_chosen_gateway();
			$form_action  = edd_get_checkout_uri( 'payment-mode=' . $payment_mode );
			do_action( 'edd_before_purchase_form' );
			?>
			<form id="edd_purchase_form" class="edd_form edd-blocks-form edd-blocks-form__purchase" action="<?php echo esc_url( $form_action ); ?>" method="POST">
				<?php
				remove_action( 'edd_checkout_form_top', 'edd_show_payment_icons' );
				/**
				 * Hooks in at the start of the blocks purchase form.
				 *
				 * @since 3.6.0
				 * @param array $block_attributes The block attributes.
				 */
				do_action( 'edd_checkout_form_top', $block_attributes );

				PaymentDetails::render( $block_attributes );

				/**
				 * Hooks in at the end of the blocks purchase form.
				 *
				 * @since 3.6.0
				 * @param array $block_attributes The block attributes.
				 */
				do_action( 'edd_checkout_form_bottom', $block_attributes );

				if ( \EDD\Captcha\Utility::can_do_captcha() ) {
					require_once EDD_BLOCKS_DIR . 'includes/forms/recaptcha.php';
					\EDD\Blocks\Recaptcha\initialize();
				}
				?>
			</form>
		</div>
		<?php
	}
}
