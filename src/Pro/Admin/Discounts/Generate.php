<?php
/**
 * Handles discount code generation via the admin area via AJAX.
 *
 * @since 3.2.0
 * @package EDD
 * @subpackage Admin
 * @category Discounts
 */

namespace EDD\Pro\Admin\Discounts;

defined( 'ABSPATH' ) || exit;

use EDD\Pro\Discounts\Generator as Generator;

class Generate extends \EDD\Admin\Discounts\Generate {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$events                                             = parent::get_subscribed_events();
		$events['wp_ajax_edd_admin_generate_discount_code'] = array( 'generate_discount_code', 10, 2 );

		return $events;
	}

	/**
	 * Outputs the button to generate a discount code.
	 *
	 * @since 3.2.0
	 */
	public function output_button() {
		?>
		<button class="button edd-popup-trigger" type="button">
			<span class="dashicons dashicons-randomize"></span>
			<span><?php esc_html_e( 'Generate Code', 'easy-digital-downloads' ); ?></span>
		</button>
		<?php
	}

	/**
	 * Outputs the controls for the discount code generator.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	public function output_controls() {
		?>
		<div class="edd-code-generator-popup">
			<div class="edd-form-group">
				<label for="generator-prefix"><?php esc_html_e( 'Prefix', 'easy-digital-downloads' ); ?></label>
				<div class="edd-form-group__control">
					<input type="text" id="generator-prefix" />
				</div>
			</div>
			<div class="edd-form-group">
				<label for="generator-length"><?php esc_html_e( 'Length', 'easy-digital-downloads' ); ?></label>
				<div class="edd-form-group__control edd-range">
					<div class="edd-range__slider" data-max="50" data-min="6" data-value="10"></div>
					<input type="number" id="generator-length" class="edd-range__input" max="50" min="6" value="10" />
				</div>
			</div>
			<div id="edd-generator-characters">
				<div class="edd-form-group">
					<label for="generator-letters"><?php esc_html_e( 'Letters', 'easy-digital-downloads' ); ?></label>
					<div class="edd-form-group__control edd-toggle">
						<input type="checkbox" id="generator-letters" checked/>
					</div>
				</div>
				<div class="edd-form-group">
					<label for="generator-numbers"><?php esc_html_e( 'Numbers', 'easy-digital-downloads' ); ?></label>
					<div class="edd-form-group__control edd-toggle">
						<input type="checkbox" id="generator-numbers" checked />
					</div>
				</div>
			</div>
			<button class="button button-secondary" type="button" id="edd-generate-code"><?php esc_html_e( 'Generate', 'easy-digital-downloads' ); ?></button>
		</div>
		<?php
	}

	/**
	 * Generates a discount code.
	 *
	 * @since 3.2.0
	 * @param array $data
	 *
	 * @return string JSON encoded response of the code generation results.
	 */
	public function generate_discount_code( $data ) {
		if ( ! isset( $_POST['edd-discount-nonce'] ) || ! wp_verify_nonce( $_POST['edd-discount-nonce'], 'edd_discount_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Nonce verification failed.', 'easy-digital-downloads' ) ) );
		}

		if ( ! check_admin_referer( 'edd_discount_nonce', 'edd-discount-nonce' ) || ! current_user_can( 'manage_shop_discounts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to generate a discount code.', 'easy-digital-downloads' ) ) );
		}

		$limit  = filter_input( INPUT_POST, 'limit', FILTER_VALIDATE_INT );
		$prefix = isset( $_POST['prefix'] ) ? sanitize_text_field( $_POST['prefix'] ) : '';

		if ( $limit < 6 ) {
			wp_send_json_error(
				array(
					'message' => sprintf(
						/* translators: Characters limit. */
						__( 'Limit should be greater than %d.', 'easy-digital-downloads' ),
						max( 6, strlen( $prefix ) )
					),
				)
			);
		}

		$type = $this->get_type();

		if ( false === $type ) {
			wp_send_json_error( array( 'message' => __( 'Either Letters or Numbers must be selected.', 'easy-digital-downloads' ) ) );
		}

		$code = Generator::generate( $prefix, $type, $limit );
		if ( false === $code ) {
			wp_send_json_error( array( 'message' => __( 'Unable to generate discount code.', 'easy-digital-downloads' ) ) );
		}

		wp_send_json_success( array( 'code' => $code ) );
	}

	/**
	 * Gets the type of discount code to generate.
	 *
	 * @since 3.2.0
	 *
	 * @return string|bool
	 */
	private function get_type() {
		$include_letters = filter_input( INPUT_POST, 'letters', FILTER_VALIDATE_BOOLEAN );
		$include_numbers = filter_input( INPUT_POST, 'numbers', FILTER_VALIDATE_BOOLEAN );

		if ( ! $include_letters && ! $include_numbers ) {
			return false;
		}

		// Default to a hash, just in case.
		$type = 'hash';

		if ( $include_letters && $include_numbers ) {
			$type = 'hash';
		} else if ( $include_numbers ) {
			$type = 'numbers';
		} else if ( $include_letters ) {
			$type = 'letters';
		}

		return $type;
	}
}
