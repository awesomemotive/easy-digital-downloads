<?php
/**
 * Registration Password Field.
 *
 * @package     EDD\Forms\Register
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.9
 */

namespace EDD\Forms\Register;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Forms\Fields\Field;

/**
 * Registration Password Field.
 *
 * @since 3.3.9
 */
class Password extends Field {

	/**
	 * Get the field ID.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_id(): string {
		return 'pass1';
	}

	/** Get the field label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_label(): string {
		return esc_html__( 'Password', 'easy-digital-downloads' );
	}

	/**
	 * Render the input.
	 *
	 * @since 3.3.9
	 */
	public function do_input(): void {
		?>
		<div class="edd-blocks-form__control wp-pwd">
			<?php
			$password = new \EDD\HTML\Text(
				array(
					'type'         => 'password',
					'data'         => array(
						'reveal' => 1,
						'pw'     => wp_generate_password( 16 ),
					),
					'name'         => 'edd_user_pass',
					'id'           => $this->get_id(),
					'class'        => $this->get_field_classes(),
					'required'     => true,
					'include_span' => false,
				)
			);
			$password->output();
			?>

			<button type="button" class="button button-secondary wp-hide-pw edd-has-js" data-toggle="0" aria-label="<?php esc_attr_e( 'Hide password', 'easy-digital-downloads' ); ?>">
				<span class="dashicons dashicons-hidden" aria-hidden="true"></span>
			</button>
			<div id="pass-strength-result" class="edd-has-js" aria-live="polite"><?php esc_html_e( 'Strength indicator', 'easy-digital-downloads' ); ?></div>
		</div>
		<?php
	}

	/**
	 * Get the description for the field.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_description(): string {
		return '';
	}

	/**
	 * Checks if the field is required.
	 *
	 * @since 3.3.9
	 * @return bool
	 */
	protected function is_required(): bool {
		return true;
	}

	/**
	 * Get the field key.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	protected function get_key(): string {
		return 'edd-password';
	}

	/**
	 * Get the form group classes.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	protected function get_form_group_classes(): array {
		$classes   = parent::get_form_group_classes();
		$classes[] = 'user-pass1-wrap';

		return $classes;
	}
}
