<?php
/**
 * Checkout Field Abstract Class.
 *
 * @package     EDD\Forms\Checkout
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.8
 */

namespace EDD\Forms\Checkout;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Forms\Fields\Field as BaseField;

/**
 * Field class.
 *
 * @since 3.3.8
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
			id="edd-card-<?php echo esc_attr( $this->get_key() ); ?>-wrap"
			<?php if ( ! empty( $classes ) ) : ?>
				class="<?php echo esc_attr( $this->get_css_class_string( $classes ) ); ?>"
			<?php endif; ?>
		>
			<?php
			$this->do_label();
			$this->do_description();
			$this->do_input();
			?>
		</div>
		<?php
	}

	/**
	 * Get the classes for the field.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	protected function get_field_classes(): array {
		$classes   = parent::get_field_classes();
		$classes[] = 'edd-card-' . $this->get_key();

		return $classes;
	}

	/**
	 * Display a description.
	 *
	 * @since 3.3.8
	 */
	protected function do_description(): void {
		if ( $this->is_block() ) {
			return;
		}

		parent::do_description();
	}

	/**
	 * Checks if the field is required.
	 *
	 * @since 3.3.8
	 * @return bool
	 */
	protected function is_required(): bool {
		return edd_field_is_required( $this->get_id() );
	}
}
