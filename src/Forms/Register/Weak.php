<?php
/**
 * Registration form weak password field.
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
 * Registration form weak password field.
 *
 * @since 3.3.9
 */
class Weak extends Field {

	/**
	 * Get the field ID.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_id(): string {
		return 'pw-weak';
	}

	/** Get the field label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_label(): string {
		return esc_html__( 'Confirm use of weak password', 'easy-digital-downloads' );
	}

	/**
	 * Render the field.
	 * The checkout fields have an ID on the wrapper div and the
	 * description is printed before the input.
	 *
	 * @return void
	 */
	public function render(): void {
		$classes = $this->get_form_group_classes();
		?>
		<div
			<?php if ( ! empty( $classes ) ) : ?>
				class="<?php echo esc_attr( $this->get_css_class_string( $classes ) ); ?>"
			<?php endif; ?>
		>
			<?php $this->do_input(); ?>
		</div>
		<?php
	}

	/**
	 * Render the input.
	 *
	 * @since 3.3.9
	 */
	public function do_input(): void {
		?>
		<div class="edd-blocks-form__control">
			<input type="checkbox" name="pw_weak" id="pw-weak" class="pw-checkbox" />
			<?php $this->do_label(); ?>
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
	 * Get the form group classes.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	protected function get_form_group_classes(): array {
		$classes   = parent::get_form_group_classes();
		$classes[] = 'pw-weak';

		return $classes;
	}
}
