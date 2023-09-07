<?php
/**
 * Handles discount code generation via the admin area via AJAX.
 *
 * @since 3.2.0
 * @package EDD
 * @subpackage Admin
 * @category Discounts
 */

namespace EDD\Admin\Discounts;

use EDD\EventManagement\SubscriberInterface;

class Generate implements SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'edd_add_discount_form_after_code_field'   => array( 'output_button', 10 ),
			'edd_add_discount_form_after_code_field_wrapper' => array( 'output_controls', 10 ),
		);
	}

	/**
	 * Outputs the button to generate a discount code.
	 *
	 * @since 3.2.0
	 */
	public function output_button() {
		?>
		<span class="edd-popup-trigger button disabled">
			<span class="dashicons dashicons-randomize"></span>
			<span><?php esc_html_e( 'Generate Code', 'easy-digital-downloads' ); ?></span>
		</span>
		<?php
	}

	/**
	 * Outputs the controls for the discount code generator.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	public function output_controls() {
		$control_data = $this->get_control_data();
		?>
		<div class="edd-code-generator-popup">
			<div class="edd-promo-notice__popup content">
				<h2><?php echo esc_html( $control_data['title'] ); ?></h2>
				<div class="content">
					<p><?php echo esc_html( $control_data['message'] ); ?></p>
					<p><a target="<?php echo esc_attr( $control_data['target'] ); ?>" href="<?php echo esc_url( $control_data['button_url'] ); ?>" class="button button-primary"><?php echo esc_html( $control_data['button_text'] ); ?></a></p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Gets the data for the control.
	 *
	 * @since 3.2.0
	 * @return array
	 */
	protected function get_control_data() {
		return array(
			'title'       => __( 'Unlock with Pro', 'easy-digital-downloads' ),
			'message'     => __( 'Upgrade to Easy Digital Downloads (Pro) to easily generate unique discount codes, and more.', 'easy-digital-downloads' ),
			'button_url'  => edd_link_helper(
				'https://easydigitaldownloads.com/lite-upgrade/',
				array(
					'utm_content'  => 'upgrade-to-pro',
					'utm_medium'   => 'discount-code-generator',
				),
				false
			),
			'button_text' => __( 'Upgrade to Pro', 'easy-digital-downloads' ),
			'target'      => '_blank',
		);
	}
}
