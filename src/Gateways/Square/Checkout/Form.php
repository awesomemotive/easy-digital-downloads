<?php
/**
 * Square checkout form.
 *
 * Handles the frontend rendering of the Square payment form.
 *
 * @package     EDD\Gateways\Square\Checkout
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Checkout;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Utils\Tokenizer;
use EDD\Sessions\PurchaseData;
use EDD\Gateways\Square\Helpers\Compat;

/**
 * Square checkout form.
 *
 * @since 3.4.0
 */
class Form {

	/**
	 * Renders the Square payment form.
	 *
	 * @since 3.4.0
	 * @return void
	 */
	public static function render() {
		// Only render if Square is the selected gateway.
		if ( ! self::is_square_selected() ) {
			return;
		}
		ob_start();

		do_action( 'edd_before_cc_fields' );
		?>
		<fieldset id="edd_cc_fields" class="edd-do-validate">
			<legend><?php esc_html_e( 'Payment Info', 'easy-digital-downloads' ); ?></legend>
			<?php if ( is_ssl() ) : ?>
			<div id="edd_secure_site_wrapper">
				<span class="padlock">
				<?php
				echo edd_get_payment_icon(
					array(
						'icon'    => 'lock',
						'width'   => 18,
						'height'  => 28,
						'classes' => array(
							'edd-icon',
							'edd-icon-lock',
						),
					)
				);
				?>
				</span>
				<span><?php _e( 'This is a secure SSL encrypted payment.', 'easy-digital-downloads' ); ?></span>
			</div>
			<?php endif; ?>
			<div id="edd-square-card-element" class="edd-square-card-element">
				<!-- Square Web Payments SDK will be inserted here -->
			</div>
			<div class="edd_errors edd-alert edd-alert-error" id="edd-square-card-errors" style="display: none;">
				<p class="edd_error" id="edd-square-card-error-message"></p>
			</div>
		</fieldset>
		<?php
		echo self::get_tokenizer_input();

		do_action( 'edd_after_cc_fields' );

		$form = ob_get_clean();
		echo $form;
	}

	/**
	 * Checks if Square is the selected gateway.
	 *
	 * @since 3.4.0
	 * @return bool
	 */
	private static function is_square_selected() {
		$chosen_gateway = edd_get_chosen_gateway();
		return 'square' === $chosen_gateway;
	}

	/**
	 * Gets the tokenizer input for the Square checkout form.
	 *
	 * @since 3.4.0
	 * @return string
	 */
	private static function get_tokenizer_input() {
		return sprintf(
			'<input type="hidden" id="%s" data-timestamp="%s" data-token="%s" />',
			esc_attr( 'edd-process-square-token' ),
			esc_attr( time() ),
			esc_attr( Tokenizer::tokenize( time() ) )
		);
	}

	/**
	 * Validates the Square payment form submission.
	 *
	 * @since 3.4.0
	 * @return void|WP_Error
	 * @throws \Exception If an error occurs.
	 */
	public static function validate() {
		Compat::map_form_data_to_request( $_POST );

		// Unset any Errors so they aren't left over form other attempts.
		edd_clear_errors();

		// Catch exceptions at a high level.
		try {
			/*
			* Reset the tax rate so that it will be recalculated correctly.
			* This is only needed on EDD 3.0+.
			*/
			EDD()->cart->set_tax_rate( null );

			/**
			 * @since unknown
			 * @todo document
			 */
			do_action( 'edd_pre_process_purchase' );

			// Make sure the cart isn't empty.
			if ( empty( EDD()->cart->contents ) && empty( EDD()->cart->fees ) ) {
				edd_debug_log( 'EDD Square: Cart is empty.' );
				throw new \Exception( esc_html__( 'Your cart is empty.', 'easy-digital-downloads' ) );
			}

			if ( ! isset( $_POST['edd-process-checkout-nonce'] ) ) {
				edd_debug_log( __( 'Missing nonce when processing checkout. Please read the following for more information: https://easydigitaldownloads.com/development/2018/07/05/important-update-to-ajax-requests-in-easy-digital-downloads-2-9-4', 'easy-digital-downloads' ), true );
			}

			// Verify the checkout session only.
			$token_verified = Tokenizer::is_token_valid( $_POST['token'], $_POST['timestamp'] );
			if ( false === $token_verified ) {
				edd_debug_log( 'EDD Square: Token is invalid.' );
				throw new \Exception( esc_html__( 'Error processing purchase. Please reload the page and try again.', 'easy-digital-downloads' ) );
			}

			$purchase_data = PurchaseData::start( false );
			if ( empty( $purchase_data ) ) {
				edd_debug_log( 'EDD Square: Purchase data is empty.' );
				throw new \Exception( esc_html__( 'Error processing purchase. Please reload the page and try again.', 'easy-digital-downloads' ) );
			}

			$errors = edd_get_errors();
			if ( empty( $purchase_data['user_info'] ) || ! empty( $errors ) ) {
				edd_debug_log( 'EDD Square: Purchase data is empty or errors are present.' );
				if ( is_array( $errors ) ) {
					throw new \Exception( current( $errors ) );
				}

				throw new \Exception( esc_html__( 'Error processing purchase. Please reload the page and try again.', 'easy-digital-downloads' ) );
			}

			// Add the source ID to the purchase data.
			$purchase_data['source_id'] = $_POST['source_id'];
			/**
			 * Allows further processing.
			 *
			 * @param array $purchase_data The purchase data.
			 */
			do_action( 'edd_gateway_square', $purchase_data );
		} catch ( \Exception $e ) {
			edd_debug_log( 'EDD Square: Error processing purchase: ' . $e->getMessage() );
			return wp_send_json_error(
				array(
					'message' => $e->getMessage(),
				)
			);
		}
	}
}
