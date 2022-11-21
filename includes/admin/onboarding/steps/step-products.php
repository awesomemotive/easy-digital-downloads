<?php
/**
 * Onboarding Wizard Products Step.
 *
 * @package     EDD
 * @subpackage  Onboarding
 * @copyright   Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.2
 */

namespace EDD\Onboarding\Steps\Products;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Onboarding\Helpers;

/**
 * Initialize step.
 *
 * @since 3.2
 */
function initialize() {
	add_action( 'wp_ajax_edd_onboarding_create_product', __NAMESPACE__ . '\create_product' );
}

/**
 * Ajax callback for creating a product.
 *
 * @since 3.2
 */
function create_product() {
	if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'edd_onboarding_wizard' ) ) {
		exit();
	}

	if ( ! current_user_can( 'edit_products' ) ) {
		return;
	}

	$response = array( 'success' => false );

	// Prepare product post details.
	$product = array(
		'post_title'  => wp_strip_all_tags( $_REQUEST['product_title'] ),
		'post_status' => 'draft',
		'post_type'   => 'download',
	);

	// Insert the product into the database.
	$post_id = wp_insert_post( $product );
	if ( $post_id ) {
		$post = get_post( $post_id );

		// Save meta fields.
		edd_download_meta_box_fields_save( $post_id, $post );

		// Set featured image.
		if ( ! empty( $_REQUEST['product_image_id'] ) ) {
			set_post_thumbnail( $post_id, absint( $_REQUEST['product_image_id'] ) );
		}

		$response['success']      = true;
		$response['redirect_url'] = get_edit_post_link( $post_id );
	}

	wp_send_json( $response );
	exit;
}

/**
 * Get step view.
 *
 * @since 3.2
 */
function step_html() {
	$currency_position  = edd_get_option( 'currency_position', 'before' );
	ob_start();
	?>
	<form method="post" class="edd-onboarding__create-product-form">
		<input type="hidden" name="_edd_product_type" value="0">
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><h3><?php echo esc_html_e( 'Product details', 'easy-digital-downloads' ); ?></h3></th>
					<td><span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php echo esc_attr( __( 'We\'ll get started with some basic information. Don\'t worry, you can add more details. When your finished here, the product will be saved as a draft so you can finish up later.', 'easy-digital-downloads' ) ); ?>"></span></td>
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
						<?php
							$price_args = array(
								'name'  => 'edd_price',
								'id'    => 'edd_price',
								'value' => '0.00',
								'class' => 'edd-form-group__input edd-price-field',
							);
							if ( 'before' === $currency_position ) {
								?>
								<span class="edd-amount-control__currency is-before"><?php echo esc_html( edd_currency_filter( '' ) ); ?></span>
								<?php
								echo EDD()->html->text( $price_args );
							} else {
								echo EDD()->html->text( $price_args );
								?>
								<span class="edd-amount-control__currency is-after"><?php echo esc_html( edd_currency_filter( '' ) ); ?></span>
								<?php
							}

							do_action( 'edd_price_field', null );
							?>
						</div>
					</td>
				</tr>

				<tr class="edd-onboarding__product-variable-price">
					<th scope="row"><label for="_edd_price_options_mode"><?php echo esc_html_e( 'Multi-option Mode', 'easy-digital-downloads' ); ?></label></th>
					<td>
						<div class="edd-form-group">
							<div class="edd-form-group__control">
								<label class="edd-toggle edd-onboarding__multi-option-toggle" for="_edd_price_options_mode">
									<?php echo EDD()->html->checkbox( array( 'name' => '_edd_price_options_mode', 'current' => null, 'class' => 'edd-form-group__input', ) ); ?>
									<span><?php echo esc_html( apply_filters( 'edd_multi_option_purchase_text', __( 'Allows multiple price options to be added to your cart at once', 'easy-digital-downloads' ) ) ); ?></span>
								</label>
							</div>
						</div>
					</td>
				</tr>
				<tr class="edd-onboarding__product-variable-price no-table-row-padding">
					<td colspan="2">
						<div id="edd_variable_price_fields" class="edd_pricing_fields edd-onboarding__product-variable-price-fields">
							<input type="hidden" id="edd_variable_prices" class="edd_variable_prices_name_field" value=""/>
							<div id="edd_price_fields" class="edd_meta_table_wrap">
								<div class="widefat edd_repeatable_table">
									<div class="edd-price-option-fields edd-repeatables-wrap">
										<div class="edd_variable_prices_wrapper edd_repeatable_row" data-key="1">
											<?php do_action( 'edd_render_price_row', 1, array(), null, 1 ); ?>
										</div>
									</div>

									<div class="edd-add-repeatable-row">
										<button class="button button-secondary edd_add_repeatable"><?php echo esc_html_e( 'Add New Price', 'easy-digital-downloads' ); ?></button>
									</div>
								</div>
							</div>
						</div><!--end #edd_variable_price_fields-->
					</td>
				</tr>
				<tr>
					<th scope="row"><label><h3><?php echo esc_html_e( 'Add your first file', 'easy-digital-downloads' ); ?></h3></label></th>
					<td><span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php echo esc_attr( __( 'We\'ll get started with some basic information. Don\'t worry, you can add more details. When your finished here, the product will be saved as a draft so you can finish up later.', 'easy-digital-downloads' ) ); ?>"></span></td>
				</tr>
				<tr class="edd-onboarding__product-files-row">
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
		<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download' ) );?>" class="button button-primary button-hero edd-onboarding__edit-my-product"><?php esc_html_e( 'Edit My Product', 'easy-digital-downloads' ); ?></a>
		<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download' ) );?>" class="button button-secondary button-hero"><?php esc_html_e( 'Exit to the Dashoard', 'easy-digital-downloads' ); ?></a>
	</div>
	<?php

	return ob_get_clean();
}
