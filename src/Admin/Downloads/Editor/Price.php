<?php
/**
 * General pricing section.
 *
 * @package   EDD\Admin\Downloads\Editor
 * @copyright Copyright (c) 2024, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.3.6
 */

namespace EDD\Admin\Downloads\Editor;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * General pricing section.
 *
 * @since 3.3.6
 */
class Price extends Section {

	/**
	 * Section ID.
	 *
	 * @since 3.3.6
	 * @var string
	 */
	protected $id = 'pricing';

	/**
	 * Section priority.
	 *
	 * @since 3.3.6
	 * @var int
	 */
	protected $priority = 500;

	/**
	 * Section icon.
	 *
	 * @since 3.3.6
	 * @var string
	 */
	protected $icon = 'money-alt';

	/**
	 * Get the section label.
	 *
	 * @since 3.3.6
	 * @return string
	 */
	public function get_label() {
		return __( 'Pricing', 'easy-digital-downloads' );
	}

	/**
	 * Render the section.
	 *
	 * @since 3.3.6
	 * @return void
	 */
	public function render() {
		$download = $this->item;
		if ( ! current_user_can( 'edit_product', $download->ID ) ) {
			return;
		}

		?>
		<div id="edd-variable-pricing-control" class="edd-form-group">
			<div class="edd-form-group__control edd-toggle">
				<input type="checkbox" class="edd-form-group__input edd-requirement" name="_variable_pricing" id="edd_variable_pricing" value="1" data-edd-requirement="variable-pricing" <?php checked( 1, $download->has_variable_prices() ); ?> />
				<label for="edd_variable_pricing">
					<?php
					echo esc_html(
						/* translators: %s: Download singular label */
						apply_filters( 'edd_variable_pricing_toggle_text', sprintf( __( 'Create price variations for this %s.', 'easy-digital-downloads' ), edd_get_label_singular( true ) ) )
					);
					?>
				</label>
			</div>
		</div>

		<?php
		$price             = $download->get_price();
		$currency_position = edd_get_option( 'currency_position', 'before' );
		?>

		<div id="edd_regular_price_field" class="edd-form-group edd_pricing_fields" data-edd-requires-variable-pricing="false">
			<label for="edd_price" class="edd-form-group__label">
				<?php esc_html_e( 'Price', 'easy-digital-downloads' ); ?>
			</label>
			<div class="edd-form-group__control">
				<?php
				if ( 'before' === $currency_position ) {
					?>
					<span class="edd-amount-control__currency is-before"><?php echo esc_html( edd_currency_filter( '' ) ); ?></span>
					<?php
				}
				echo EDD()->html->text( $this->get_price_args( $price ) );
				if ( 'after' === $currency_position ) {
					?>
					<span class="edd-amount-control__currency is-after"><?php echo esc_html( edd_currency_filter( '' ) ); ?></span>
					<?php
				}

				do_action( 'edd_price_field', $download->ID );
				?>
			</div>
		</div>

		<?php do_action( 'edd_after_price_field', $download->ID ); ?>

		<div class="edd-form-group" data-edd-requires-variable-pricing="true">
			<div class="edd-form-group__control">
				<?php
				$toggle = new \EDD\HTML\CheckboxToggle(
					array(
						'name'    => '_edd_price_options_mode',
						'current' => edd_single_price_option_mode( $download->ID ),
						'class'   => 'edd-form-group__input',
						'label'   => apply_filters(
							'edd_multi_option_purchase_text',
							__( 'Allow multiple price options to be added to your cart at once.', 'easy-digital-downloads' )
						),
					)
				);
				$toggle->output();
				?>
			</div>
		</div>
		<?php

		/**
		 * Output the price fields.
		 *
		 * @since 1.9
		 * @param int                 $download_id The download ID.
		 * @param \EDD_Download|false $download    The download object (added in 3.3.6).
		 */
		do_action( 'edd_meta_box_price_fields', $download->ID, $download );
	}

	/**
	 * Get the price arguments.
	 *
	 * @since 3.3.6
	 * @param float $price The price.
	 * @return array
	 */
	private function get_price_args( $price ) {
		return array(
			'name'  => 'edd_price',
			'id'    => 'edd_price',
			'value' => isset( $price ) ? esc_attr( edd_format_amount( $price ) ) : '',
			'class' => 'edd-form-group__input edd-price-field',
		);
	}
}
