<?php
/**
 * Amount/type field.
 *
 * @package     EDD\HTML
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.5
 */

namespace EDD\HTML;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class AmountType
 *
 * @since 3.6.5
 * @package EDD\HTML
 */
class AmountType extends Base {

	/**
	 * Gets the HTML for the element.
	 *
	 * @since 3.6.5
	 * @return string Element HTML.
	 */
	public function get(): string {
		ob_start();
		?>
		<span class="edd-amount-type-wrapper">
			<?php
			// If the currency symbol is before the price, output the prefix.
			if ( 'before' === $this->args['position'] ) {
				?>
				<span class="edd-input__symbol edd-input__symbol--prefix"><?php echo esc_html( $this->args['unit'] ); ?></span>
				<?php
			}
			?>
			<?php
			$input = $this->get_input();
			$input->output();
			if ( 'after' === $this->args['position'] ) {
				?>
				<span class="edd-input__symbol edd-input__symbol--suffix"><?php echo esc_html( $this->args['unit'] ); ?></span>
				<?php
			}
			?>
		</span>
		<?php

		return ob_get_clean();
	}

	/**
	 * Gets the default arguments for the element.
	 *
	 * @since 3.6.5
	 * @return array Default arguments.
	 */
	public function defaults(): array {
		return array(
			'id'          => '',
			'name'        => '',
			'value'       => '',
			'position'    => 'after',
			'unit'        => '',
			'type'        => 'number',
			'placeholder' => '',
			'required'    => false,
			'min'         => '',
			'max'         => '',
			'step'        => '',
		);
	}

	/**
	 * Gets the input element.
	 *
	 * @since 3.6.5
	 * @return Base Input element.
	 */
	private function get_input(): Base {
		if ( 'number' === $this->args['type'] ) {
			return new Number( $this->args );
		}

		return new Text( $this->args );
	}
}
