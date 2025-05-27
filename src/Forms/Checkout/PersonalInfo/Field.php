<?php
/**
 * Personal Info Field Abstract Class.
 *
 * @package     EDD\Forms\Checkout
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.9
 */

namespace EDD\Forms\Checkout\PersonalInfo;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Forms\Fields\Field as BaseField;

/**
 * Field class.
 *
 * @since 3.3.9
 */
abstract class Field extends BaseField {

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
			id="edd-<?php echo esc_attr( $this->get_key() ); ?>-wrap"
			<?php if ( ! empty( $classes ) ) : ?>
				class="<?php echo esc_attr( $this->get_css_class_string( $classes ) ); ?>"
			<?php endif; ?>
		>
			<?php
			$this->do_label();
			$this->do_input();
			$this->do_description();
			?>
		</div>
		<?php
	}

	/**
	 * Get the classes for the field.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	protected function get_field_classes(): array {
		$classes   = parent::get_field_classes();
		$classes[] = 'edd-' . $this->get_key();

		return $classes;
	}

	/**
	 * Checks if the field is required.
	 *
	 * @since 3.3.9
	 * @return bool
	 */
	protected function is_required(): bool {
		return edd_field_is_required( $this->get_id() );
	}

	/**
	 * Get the defaults for the field.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	protected function get_defaults(): array {
		return array(
			'name'         => $this->get_key(),
			'id'           => $this->get_id(),
			'class'        => $this->get_css_class_string( $this->get_field_classes() ),
			'required'     => $this->is_required(),
			'include_span' => false,
		);
	}
}
