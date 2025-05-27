<?php
/**
 * Email Field.
 *
 * @package     EDD\Forms\Checkout\PersonalInfo
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.9
 */

namespace EDD\Forms\Checkout\PersonalInfo;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * The email field.
 *
 * @since 3.3.9
 */
class Email extends Field {

	/**
	 * Render the field.
	 * The checkout fields have an ID on the wrapper div and the
	 * description is printed before the input.
	 *
	 * @return void
	 */
	public function render(): void {
		parent::render();
		/**
		 * Allow users to add content after the email field.
		 *
		 * @param array $data The data array (the customer data).
		 *              This parameter is not in the original shortcode hook.
		 */
		do_action( 'edd_purchase_form_after_email', $this->data );
	}

	/**
	 * Render the input.
	 *
	 * @since 3.3.9
	 */
	public function do_input(): void {
		$input = new \EDD\HTML\Text(
			wp_parse_args(
				array(
					'placeholder'  => esc_html__( 'Email', 'easy-digital-downloads' ),
					'value'        => $this->data['email'],
					'autocomplete' => 'email',
				),
				$this->get_defaults()
			)
		);
		$input->output();
	}

	/**
	 * Get the field ID.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_id(): string {
		return 'edd-email';
	}

	/** Get the field label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_label(): string {
		return esc_html__( 'Email', 'easy-digital-downloads' );
	}

	/**
	 * Get the description.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_description(): string {
		return __( 'We will send the purchase receipt to this address.', 'easy-digital-downloads' );
	}

	/**
	 *  Get the field key.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	protected function get_key(): string {
		return 'edd_email';
	}

	/**
	 * Whether the field is required.
	 *
	 * @since 3.3.9
	 * @return bool
	 */
	protected function is_required(): bool {
		return true;
	}
}
