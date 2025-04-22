<?php
/**
 * General download details section.
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
class Details extends Section {

	/**
	 * Section ID.
	 *
	 * @since 3.3.6
	 * @var string
	 */
	protected $id = 'details';

	/**
	 * Section priority.
	 *
	 * @since 3.3.6
	 * @var int
	 */
	protected $priority = 1;

	/**
	 * Section icon.
	 *
	 * @since 3.3.6
	 * @var string
	 */
	protected $icon = 'download';

	/**
	 * Get the section label.
	 *
	 * @since 3.3.6
	 * @return string
	 */
	public function get_label() {
		return __( 'Details', 'easy-digital-downloads' );
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

		$types = edd_get_download_types();
		$type  = edd_get_download_type( $download->ID );
		ksort( $types );
		?>
		<div class="edd-form-group">
			<label for="_edd_product_type" class="edd-form-group__label">
				<?php
				echo esc_html(
					apply_filters( 'edd_product_type_options_heading', __( 'Product Type Options:', 'easy-digital-downloads' ) )
				);

				$tooltip = new \EDD\HTML\Tooltip(
					array(
						'id'      => 'edd-product-type-tooltip',
						'content' => __( 'Sell this item as a single product with download files, or select a custom product type with different options, which may not necessarily include download files.', 'easy-digital-downloads' ),
					)
				);
				$tooltip->output();
				?>
			</label>
			<div class="edd-form-group__control">
				<?php
				$select = new \EDD\HTML\Select(
					array(
						'options'          => $types,
						'name'             => '_edd_product_type',
						'id'               => '_edd_product_type',
						'selected'         => $type,
						'show_option_all'  => false,
						'show_option_none' => false,
						'class'            => 'edd-form-group__input edd-supports',
						'data'             => array(
							'edd-supported' => 'product-type',
						),
					)
				);
				$select->output();
				?>
			</div>
		</div>

		<div id="edd_regular_price_field" class="edd-form-group edd_pricing_fields" data-edd-requires-variable-pricing="false">
			<label for="edd_price" class="edd-form-group__label">
				<?php esc_html_e( 'Price', 'easy-digital-downloads' ); ?>
			</label>
			<div class="edd-form-group__control">
				<span class="edd-amount-type-wrapper">
					<?php
					$price             = $download->get_price();
					$currency_position = edd_get_option( 'currency_position', 'before' );
					$currency_symbol   = edd_currency_symbol();

					// If the currency symbol is before the price, output the prefix.
					if ( 'before' === $currency_position ) {
						?>
						<span class="edd-input__symbol edd-input__symbol--prefix"><?php echo esc_html( $currency_symbol ); ?></span>
						<?php
					}
					?>
					<?php
					$price_input = new \EDD\HTML\Text(
						array(
							'name'         => 'edd_price',
							'id'           => 'edd_price',
							'value'        => isset( $price ) ? esc_attr( edd_format_amount( $price ) ) : '',
							'class'        => array( 'edd-amount-input', 'edd-price-field', 'no-controls', 'symbol-' . $currency_position ),
							'include_span' => false,
						)
					);

					$price_input->output();
					if ( 'after' === $currency_position ) {
						?>
						<span class="edd-input__symbol edd-input__symbol--suffix"><?php echo esc_html( $currency_symbol ); ?></span>
						<?php
					}
					?>
				</span>
				<?php do_action( 'edd_price_field', $download->ID ); ?>
			</div>
		</div>

		<?php do_action( 'edd_after_price_field', $download->ID ); ?>

		<?php
		/**
		 * Output the price fields.
		 *
		 * @since 1.9
		 * @param int                 $download_id The download ID.
		 * @param \EDD_Download|false $download    The download object (added in 3.3.6).
		 */
		do_action( 'edd_meta_box_price_fields', $download->ID, $download );
		?>

		<div class="edd-form-group__label">
			<?php esc_html_e( 'Additional Details', 'easy-digital-downloads' ); ?>
		</div>

		<div class="edd-form-group">
			<div id="edd-variable-pricing-control" class="edd-form-group__control edd-toggle">
				<input type="hidden" name="_variable_pricing" value="0" />
				<input type="checkbox" class="edd-form-group__input edd-requirement" name="_variable_pricing" id="edd_variable_pricing" value="1" data-edd-requirement="variable-pricing" <?php checked( 1, $download->has_variable_prices() ); ?> />
				<label for="edd_variable_pricing">
					<?php
					echo esc_html(
						apply_filters(
							'edd_variable_pricing_toggle_text',
							/* translators: %s: Download singular label */
							sprintf( __( 'Create price variations for this %s.', 'easy-digital-downloads' ), edd_get_label_singular( true ) )
						)
					);
					?>
				</label>
			</div>
		</div>

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
							__( 'Allow purchasing multiple variations in the same order.', 'easy-digital-downloads' )
						),
					)
				);
				$toggle->output();
				?>
			</div>
		</div>

		<?php
		/**
		 * Hook to add additional fields to the meta box.
		 *
		 * @param int           $download_id The download ID.
		 * @param \EDD_Download $download    The download object.
		 */
		do_action( 'edd_meta_box_fields', $download->ID, $download );
	}
}
