<?php
/**
 * FromTo class file for getting a fieldset with two date fields (for a range).
 *
 * @package     EDD\HTML
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.8
 */

namespace EDD\HTML;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * FromTo class.
 */
class FromTo extends Base {

	/**
	 * Gets the HTML for the element.
	 *
	 * @since 3.3.8
	 * @return string Element HTML.
	 */
	public function get() {
		ob_start();
		?>
		<fieldset class="edd-from-to-wrapper">
			<legend class="screen-reader-text">
					<?php echo esc_html( $this->args['legend'] ); ?>
			</legend>
			<label for="edd-<?php echo esc_attr( $this->args['id'] ); ?>-start" class="screen-reader-text"><?php esc_html_e( 'Set start date', 'easy-digital-downloads' ); ?></label>
				<?php
				echo EDD()->html->date_field(
					array(
						'id'          => "edd-{$this->args['id']}-start",
						'class'       => "{$this->args['class']}-start",
						'name'        => "{$this->args['id']}-start",
						'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' ),
					)
				);
				?>
			<label for="edd-<?php echo esc_attr( $this->args['id'] ); ?>-end" class="screen-reader-text"><?php esc_html_e( 'Set end date', 'easy-digital-downloads' ); ?></label>
				<?php
				echo EDD()->html->date_field(
					array(
						'id'          => "edd-{$this->args['id']}-end",
						'class'       => "{$this->args['class']}-end",
						'name'        => "{$this->args['id']}-end",
						'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' ),
					)
				);
				?>
		</fieldset>
		<?php

		return ob_get_clean();
	}

	/**
	 * Gets the default arguments for the element.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	protected function defaults() {
		return array(
			'legend' => '',
			'id'     => 'export',
			'class'  => 'edd-export',
		);
	}
}
