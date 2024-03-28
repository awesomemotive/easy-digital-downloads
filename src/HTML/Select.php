<?php
/**
 * Select HTML Element
 *
 * @package EDD
 * @subpackage HTML
 * @since 3.2.8
 */

namespace EDD\HTML;

defined( 'ABSPATH' ) || exit;

/**
 * Class Select
 *
 * @since 3.2.8
 * @package EDD\HTML
 */
class Select extends Base {

	/**
	 * Gets the HTML for the select.
	 *
	 * @since 3.2.8
	 * @return string
	 */
	public function get() {
		$this->maybe_update_selected();
		ob_start();
		?>
		<select
			<?php
			if ( ! empty( $this->args['disabled'] ) ) {
				?>
				disabled
				<?php
			}
			if ( ! empty( $this->args['readonly'] ) ) {
				?>
				readonly
				<?php
			}
			if ( ! empty( $this->args['required'] ) ) {
				?>
				required
				<?php
			}
			?>
			name="<?php echo esc_attr( $this->args['name'] ); ?>"
			id="<?php echo esc_attr( str_replace( '-', '_', $this->args['id'] ) ); ?>"
			class="<?php echo esc_attr( $this->get_css_class_string() ); ?>"
			<?php if ( $this->args['multiple'] ) : ?>
				multiple
			<?php endif; ?>
			<?php echo $this->get_data_elements(); ?>
		>
			<?php
			if ( ! empty( $this->args['show_option_all'] ) ) {
				?>
				<option value="all"<?php echo $this->is_selected( 0 ) ? ' selected' : ''; ?>>
					<?php echo esc_html( $this->args['show_option_all'] ); ?>
				</option>
				<?php
			}

			if ( ! empty( $this->args['options'] ) ) {
				if ( $this->args['show_option_none'] ) {
					?>
					<option value="-1"<?php echo $this->is_selected( -1 ) ? ' selected' : ''; ?>>
						<?php echo esc_html( $this->args['show_option_none'] ); ?>
					</option>
					<?php
				}

				foreach ( $this->args['options'] as $key => $option ) {
					?>
					<option value="<?php echo esc_attr( $key ); ?>"<?php echo $this->is_selected( $key ) ? ' selected' : ''; ?>>
						<?php echo esc_html( $option ); ?>
					</option>
					<?php
				}
			}
			?>
		</select>
		<?php

		return ob_get_clean();
	}

	/**
	 * Parses the arguments for the select.
	 *
	 * @since 3.2.8
	 * @return array
	 */
	protected function defaults() {
		return array(
			'options'          => array(),
			'name'             => null,
			'class'            => '',
			'id'               => '',
			'selected'         => 0,
			'chosen'           => false,
			'placeholder'      => null,
			'multiple'         => false,
			'show_option_all'  => _x( 'All', 'all dropdown items', 'easy-digital-downloads' ),
			'show_option_none' => _x( 'None', 'no dropdown items', 'easy-digital-downloads' ),
			'data'             => array(),
			'readonly'         => false,
			'disabled'         => false,
			'required'         => false,
		);
	}

	/**
	 * Gets the base CSS classes for the select element.
	 *
	 * @since 3.2.8
	 * @return array
	 */
	protected function get_base_classes(): array {
		$base_classes = array(
			'edd-select',
		);
		if ( $this->args['chosen'] ) {
			$base_classes[] = 'edd-select-chosen';
			if ( is_rtl() ) {
				$base_classes[] = ' chosen-rtl';
			}
		}

		return $base_classes;
	}

	/**
	 * Checks if the value is selected.
	 *
	 * @since 3.2.8
	 * @param string|int $value The value to check. This could be a string or an integer.
	 * @return bool
	 */
	private function is_selected( $value ) {
		if ( $this->args['multiple'] ) {
			return selected( true, in_array( (string) $value, $this->args['selected'], true ), false );
		}
		if ( ! empty( $this->args['selected'] ) && ! is_array( $this->args['selected'] ) ) {
			return selected( $this->args['selected'], $value, false );
		}

		return false;
	}

	/**
	 * Updates the selected value. If the select is a multiple select, the selected value will be an array of strings.
	 * This is only for comparison purposes.
	 *
	 * @since 3.2.10
	 * @return void
	 */
	private function maybe_update_selected() {
		if ( $this->args['multiple'] || is_array( $this->args['selected'] ) ) {
			$this->args['selected'] = array_map( 'strval', (array) $this->args['selected'] );
		}
	}
}
