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
	add_filter( 'edd_price_options_heading', function( $heading ) {
		return '<label class="edd-onboarding__pricing-options-label">' . esc_html( __( 'Pricing options:', 'easy-digital-downloads' ) ) . '</label>';
	}, 10, 1 );

	ob_start();
	?>
	<form method="post" class="edd-onboarding__create-product-form">
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><label><h3><?php echo esc_html( __( 'Product details', 'easy-digital-downloads' ) ); ?></h3></label></th>
					<td><span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="Explanation for basic product details."></span></td>
				</tr>
				<tr>
					<th scope="row"><label for="product_title"><?php echo esc_html( __( 'Product name:', 'easy-digital-downloads' ) ); ?></label></th>
					<td>
						<input type="text" class="regular-text" id="product_title" name="product_title" required>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="product_image_url"><?php echo esc_html( __( 'Product image:', 'easy-digital-downloads' ) ); ?></label></th>
					<td>
						<div class="edd-onboarding__product-image-wrapper">
							<input type="hidden" id="product_image_id" name="product_image_id">
							<input type="text" class="regular-text" id="product_image_url" class="" name="product_image_url" data-attachment-id-field="#product_image_id" />
							<span>&nbsp;
							<input type="button" data-uploader-title="Set Image" data-uploader-button-text="Set Image" class="edd_settings_upload_button button-secondary" value="Set Image"/></span>
						</div>
					</td>
				</tr>
				<tr class="edd-onboarding__product-pricing-row">
					<td colspan="2">
						<div class="edd-onboarding__product-pricing-wrapper">
							<?php edd_render_price_field( null ); ?>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row"><label><h3><?php echo esc_html( __( 'Add your first file', 'easy-digital-downloads' ) ); ?></h3></label></th>
					<td><span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="Explanation for adding the files."></span></td>
				</tr>
				<tr class="edd-onboarding__product-files-row">
					<td colspan="2">
						<?php edd_render_files_field( null ); ?>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	<pre>
	</pre>
	<?php

	return ob_get_clean();
}
