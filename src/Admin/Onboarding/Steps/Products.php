<?php
/**
 * Onboarding Wizard Products Step.
 *
 * @package     EDD
 * @subpackage  Onboarding
 * @copyright   Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.1.1
 */

namespace EDD\Admin\Onboarding\Steps;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Products step class.
 */
class Products extends Step {

	/**
	 * Get step view.
	 *
	 * @since 3.1.1
	 */
	public function step_html() {
		$currency_position = edd_get_option( 'currency_position', 'before' );
		// Remove all actions that may have been added by extensions.
		remove_all_actions( 'edd_after_price_field' );
		remove_all_actions( 'edd_download_price_option_row' );
		?>
		<form method="post" class="edd-onboarding__create-product-form">
			<input type="hidden" name="_edd_product_type" value="0">
			<table class="form-table edd-download-editor__sections" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><h3><?php echo esc_html_e( 'Product details', 'easy-digital-downloads' ); ?></h3></th>
						<td>
							<?php
							$tooltip = new \EDD\HTML\Tooltip(
								array(
									'content' => __( 'We\'ll get started with some basic information. Don\'t worry, you can add more details later. When you\'re finished here, the product will be saved as a draft so you can finish up later.', 'easy-digital-downloads' ),
								)
							);
							$tooltip->output();
							?>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="product_title"><?php echo esc_html_e( 'Product Name', 'easy-digital-downloads' ); ?></label></th>
						<td>
							<input type="text" class="regular-text" id="product_title" name="product_title" required>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="product_image_url"><?php echo esc_html_e( 'Product Image', 'easy-digital-downloads' ); ?></label></th>
						<td>
							<div class="edd-onboarding__product-image-wrapper">
								<input type="hidden" id="product_image_id" name="product_image_id">
								<div class="edd-upload-button-wrapper">
									<input type="text" class="regular-text" id="product_image_url" class="" name="product_image_url" data-attachment-id-field="#product_image_id" />
									<button data-input="#product_image_url" data-uploader-title="<?php echo esc_attr( __( 'Set image', 'easy-digital-downloads' ) ); ?>" data-uploader-button-text="<?php echo esc_attr( __( 'Set image', 'easy-digital-downloads' ) ); ?>" class="button edd_settings_upload_button button-secondary"><?php esc_html_e( 'Set Image', 'easy-digital-downloads' ); ?></button>
								</div>
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="edd_variable_pricing"><?php echo apply_filters( 'edd_price_options_heading', __( 'Pricing Options', 'easy-digital-downloads' ) ); ?></label></th>
						<td>
							<input style="display:none;" type="checkbox" class="edd-form-group__input" name="_variable_pricing" id="edd_variable_pricing" value="1"/>
							<div class="edd-onboarding__pricing-option-pill">
								<button class="left-option active" data-variable-pricing="false"><?php echo esc_html_e( 'Single price', 'easy-digital-downloads' ); ?></button>
								<button class="right-option" data-variable-pricing="true"><?php echo esc_html_e( 'Variable price', 'easy-digital-downloads' ); ?></button>
							</div>
						</td>
					</tr>
					<tr class="edd-onboarding__product-single-price">
						<th scope="row"><label for="edd_price"><?php echo esc_html_e( 'Product Price', 'easy-digital-downloads' ); ?></label></th>
						<td>
							<div class="edd-form-group__control">
								<span class="edd-amount-type-wrapper">
									<?php
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
											'value'        => '0.00',
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
								<?php do_action( 'edd_price_field', null ); ?>
							</div>
						</td>
					</tr>

					<tr class="edd-onboarding__product-variable-price no-table-row-padding">
						<td colspan="2">
							<div id="edd_download_editor__variable-pricing" class="edd_pricing_fields edd-onboarding__product-variable-price-fields edd_download_editor__variable-pricing">
								<input type="hidden" id="edd_variable_prices" class="edd_variable_prices_name_field" value=""/>
								<div id="edd_price_fields" class="edd_meta_table_wrap">
									<div class="widefat edd-variable-prices__rows">
										<?php
										$section = new \EDD\Admin\Downloads\Editor\VariablePrices();
										$section->do_row(
											new \EDD_Download(),
											array(
												'name'   => '',
												'amount' => '',
												'index'  => 1,
											),
											1
										);
										?>
									</div>

									<div class="edd__header-footer">
										<?php $timestamp = time(); ?>
										<button type="button" class="button button-secondary edd-button__add--variation" data-timestamp="<?php echo esc_attr( $timestamp ); ?>" data-token="<?php echo esc_attr( \EDD\Utils\Tokenizer::tokenize( $timestamp ) ); ?>">
											<?php esc_html_e( 'Add Variation', 'easy-digital-downloads' ); ?>
										</button>
									</div>
								</div>
							</div><!--end #edd_download_editor__variable-pricing-->
						</td>
					</tr>
					<tr>
						<th scope="row">
							<div class="edd-form-group">
								<div class="edd-form-group__control">
									<label class="edd-toggle edd-onboarding__upload-files-toggle" for="_edd_upload_files">
										<?php
										echo EDD()->html->checkbox(
											array(
												'name'    => '_edd_upload_files',
												'current' => null,
												'class'   => 'edd-form-group__input',
											)
										);
										?>
										<span><?php echo esc_html_e( 'Add your first file', 'easy-digital-downloads' ); ?></span>
									</label>
								</div>
							</div>
						</th>
						<td>
							<?php
							$tooltip = new \EDD\HTML\Tooltip(
								array(
									'content' => __( 'Ready to add your first downloadable file to your product? Great! These files will be protected and only available to people who purchase your product. Not ready yet? No problem, you can always add and update files later.', 'easy-digital-downloads' ),
								)
							);
							$tooltip->output();
							?>
						</td>
					</tr>
					<tr class="edd-onboarding__product-files-row" style="display: none;">
						<td colspan="2">
							<?php edd_render_files_field( null ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</form>
		<div class="edd-onboarding__product-created" style="display: none;">
			<h1>🎉 <?php esc_html_e( 'Congratulations!', 'easy-digital-downloads' ); ?></h1>
			<p><?php esc_html_e( 'You\'ve set up your store and your first product has been created.', 'easy-digital-downloads' ); ?></p>
			<a href="<?php echo esc_url( edd_get_admin_url() ); ?>" class="button button-primary button-hero edd-onboarding__edit-my-product"><?php esc_html_e( 'Edit My Product', 'easy-digital-downloads' ); ?></a>
			<a href="<?php echo esc_url( edd_get_admin_url( array( 'page' => 'edd-addons' ) ) ); ?>" class="button button-secondary button-hero"><?php esc_html_e( 'Explore Extensions', 'easy-digital-downloads' ); ?></a>
		</div>
		<?php
	}
}
