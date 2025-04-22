<?php
/**
 * Forms Field Abstract Class.
 *
 * @package     EDD\Forms\Fields
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.8
 */

namespace EDD\Forms\Fields;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Field class.
 *
 * @since 3.3.8
 */
abstract class Field implements FieldInterface {

	/**
	 * The data passed to the field/form.
	 *
	 * @since 3.3.8
	 * @var array
	 */
	protected array $data;

	/**
	 * Constructor.
	 *
	 * @since 3.3.8
	 * @param array $data The data.
	 */
	public function __construct( array $data ) {
		$this->data = wp_parse_args(
			$data,
			array(
				'is_block' => true,
			)
		);
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
			$this->do_label();
			$this->do_input();
			$this->do_description();
			?>
		</div>
		<?php
	}

	/**
	 * Get the field key.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	protected function get_key(): string {
		return $this->get_id();
	}

	/**
	 * Renders the label.
	 *
	 * @since 3.3.8
	 */
	protected function do_label(): void {
		?>
		<label for="<?php echo esc_attr( $this->get_id() ); ?>" class="edd-label">
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
	 * @since 3.3.8
	 * @return array
	 */
	protected function get_form_group_classes(): array {
		return $this->is_block()
			? array(
				'edd-blocks-form__group',
				'edd-blocks-form__group-' . $this->get_key(),
			)
			: array();
	}

	/**
	 * Get the classes for the field.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	protected function get_field_classes(): array {
		$classes = array(
			'edd-input',
			$this->get_key(),
		);
		if ( $this->is_required() ) {
			$classes[] = 'required';
		}

		return $classes;
	}

	/**
	 * Get the CSS class string.
	 *
	 * @since 3.3.8
	 * @param array $classes The classes.
	 * @return string
	 */
	protected function get_css_class_string( $classes = array() ): string {
		return implode( ' ', array_unique( array_filter( $classes ) ) );
	}

	/**
	 * Display a description.
	 *
	 * @since 3.3.8
	 */
	protected function do_description(): void {
		if ( empty( $this->get_description() ) ) {
			return;
		}
		?>
		<p class="edd-description"><?php echo esc_html( $this->get_description() ); ?></p>
		<?php
	}

	/**
	 * Checks if the field is required.
	 *
	 * @since 3.3.8
	 * @return bool
	 */
	protected function is_required(): bool {
		return false;
	}

	/**
	 * Get the defaults for the field.
	 *
	 * @since 3.3.8
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
	 * Checks if the field is a block.
	 *
	 * @since 3.3.8
	 * @return bool
	 */
	protected function is_block(): bool {
		return ! empty( $this->data['is_block'] );
	}

	/**
	 * Get the selected country.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	protected function get_selected_country(): string {
		if ( ! empty( $this->data['address']['country'] ) && '*' !== $this->data['address']['country'] ) {
			return $this->data['address']['country'];
		}

		return edd_get_shop_country();
	}

	/**
	 * Get the selected state.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	protected function get_selected_state(): string {
		if ( ! empty( $this->data['address']['state'] ) ) {
			return $this->data['address']['state'];
		}

		return edd_get_shop_state();
	}
}
