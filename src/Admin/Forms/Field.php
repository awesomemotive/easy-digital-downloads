<?php
/**
 * Forms Field Abstract Class.
 *
 * @package     EDD\Forms\Fields
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.9
 */

namespace EDD\Admin\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Forms\Fields\FieldInterface;

/**
 * Field class.
 *
 * @since 3.3.9
 */
abstract class Field implements FieldInterface {

	/**
	 * The data passed to the field/form.
	 *
	 * @since 3.3.9
	 * @var array
	 */
	protected $data;

	/**
	 * Constructor.
	 *
	 * @since 3.3.9
	 * @param array $data The data.
	 */
	public function __construct( $data ) {
		$this->data = $data;
	}

	/**
	 * Render the field.
	 *
	 * @return void
	 */
	public function render(): void {
		?>
		<div class="<?php echo esc_attr( $this->get_css_class_string( $this->get_form_group_classes() ) ); ?>">
			<?php
			$action_hooks = $this->get_action_hooks();
			if ( ! empty( $action_hooks['before'] ) ) {
				do_action( $action_hooks['before'], $this->data->id, $this->data );
			}
			$this->do_label();
			?>
			<div class="edd-form-group__control">
				<?php
				$this->do_input();
				if ( ! empty( $action_hooks['after'] ) ) {
					do_action( $action_hooks['after'], $this->data->id, $this->data );
				}
				?>
			</div>
			<?php $this->do_description(); ?>
		</div>
		<?php
	}

	/**
	 * Get the field key.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	protected function get_key(): string {
		return $this->get_id();
	}

	/**
	 * Renders the label.
	 *
	 * @since 3.3.9
	 */
	protected function do_label(): void {
		?>
		<label for="<?php echo esc_attr( $this->get_id() ); ?>">
			<?php
			echo esc_html( $this->get_label() );
			if ( $this->is_required() ) {
				echo EDD()->html->show_required();
			}
			?>
		</label>
		<?php
	}

	/**
	 * Get the form group classes.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	protected function get_form_group_classes(): array {
		return array(
			'edd-form-group',
		);
	}

	/**
	 * Get the CSS class string.
	 *
	 * @since 3.3.9
	 * @param array $classes The classes.
	 * @return string
	 */
	protected function get_css_class_string( $classes = array() ): string {
		return implode( ' ', array_unique( array_filter( $classes ) ) );
	}

	/**
	 * Display a description.
	 *
	 * @since 3.3.9
	 */
	protected function do_description(): void {
		if ( empty( $this->get_description() ) ) {
			return;
		}
		?>
		<p class="description"><?php echo esc_html( $this->get_description() ); ?></p>
		<?php
	}

	/**
	 * Checks if the field is required.
	 *
	 * @since 3.3.9
	 * @return bool
	 */
	protected function is_required(): bool {
		return false;
	}

	/**
	 * Get the defaults for the field.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	protected function get_defaults(): array {
		return array(
			'name'     => $this->get_id(),
			'id'       => $this->get_id(),
			'class'    => $this->get_css_class_string( $this->get_field_classes() ),
			'required' => $this->is_required(),
		);
	}

	/**
	 * Get the classes for the field.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	protected function get_field_classes(): array {
		$classes = array(
			'edd-form-group__input',
			$this->get_key(),
		);
		if ( $this->is_required() ) {
			$classes[] = 'required';
		}

		return $classes;
	}

	/**
	 * Get the action hooks.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	protected function get_action_hooks(): array {
		return array();
	}
}
