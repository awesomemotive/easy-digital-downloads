<?php
/**
 * Metabox Functions
 *
 * @package     EDD
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2017, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.9
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register all the meta boxes for the Page post type
 *
 * @since 2.9
 * @return void
 */
function edd_add_page_meta_box() {

	$post_types = apply_filters( 'edd_page_metabox_post_types' , array( 'page' ) );

	foreach ( $post_types as $post_type ) {

		/** EDD Page Settings **/
		add_meta_box( 'edd_page_settings', __( 'Easy Digital Downloads Settings', 'easy-digital-downloads' ),  'edd_render_page_settings_meta_box', $post_type, 'side', 'default' );

	}
}
add_action( 'add_meta_boxes', 'edd_add_page_meta_box' );

/**
 * Runs the actions allowing developers to add EDD settings to pages.
 *
 * @since 2.9
 */
function edd_render_page_settings_meta_box() {
	global $post;

	/*
	 * Output the files fields
	 * @since 2.9
	 */
	do_action( 'edd_page_meta_box_settings_fields', $post->ID );
}

/**
 * The setting for a page to determine if it is a checkout page.
 *
 * @since 2.9
 * @param int $post_id Download (Post) ID
 * @return void
 */
function edd_render_is_checkout_setting( $post_id ) {
	if( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	$page_has_checkout = get_post_meta( $post_id, '_edd_has_checkout', true );
	$page_has_checkout = ! empty( $page_has_checkout ) ? '1' : '0';
	?>
	<div id="edd-page-has-checkout">
		<label for="_edd_has_checkout">
			<?php
			$args = array(
				'name'    => '_edd_has_checkout',
				'current' => $page_has_checkout
			);

			$primary_purchase_page = edd_get_option( 'purchase_page' );
			if ( (int) $primary_purchase_page === (int) $post_id ) {
				$args['options']['disabled'] = true;
				$args['current'] = '1';
			}
			?>
			<?php echo EDD()->html->checkbox( $args ); ?>
			<?php _e( 'Contains Checkout', 'easy-digital-downloads' ); ?>
		</label>
		<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Contains Checkout</strong>: If this is a custom page that is not using the [download_checkout] shortcode, you can force Easy Digital Downloads to load the resources necessary for a checkout page.', 'easy-digital-downloads' ); ?>"></span>
	</div>
	<?php
}
add_action( 'edd_page_meta_box_settings_fields', 'edd_render_is_checkout_setting', 20 );

/**
 * Returns the EDD settings for pages.
 *
 * @since 2.9
 * @return array $fields Array of fields.
 */
function edd_page_metabox_fields() {

	$fields = array(
		'_edd_has_checkout',
	);

	return apply_filters( 'edd_page_metabox_fields_save', $fields );
}

/**
 * Save post meta when the save_post action is called
 *
 * @since 2.9
 * @param int $post_id Page ID
 * @global array $post All the data of the the current post
 * @return void
 */
function edd_page_meta_box_save( $post_id, $post ) {

	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'DOING_AJAX') && DOING_AJAX ) || isset( $_REQUEST['bulk_edit'] ) ) {
		return;
	}

	if ( isset( $post->post_type ) && 'revision' == $post->post_type ) {
		return;
	}

	// The default fields that get saved
	$fields = edd_page_metabox_fields();

	foreach ( $fields as $field ) {

		if ( '_edd_has_checkout' == $field ) {

			if ( isset( $_POST[ $field ] ) && $_POST[ $field ] === 'on' ) {
				update_post_meta( $post_id, '_edd_has_checkout', '1' );
			} else {
				delete_post_meta( $post_id, '_edd_has_checkout' );
			}

		} else {

			if ( ! empty( $_POST[ $field ] ) ) {
				$new = apply_filters( 'edd_page_metabox_save_' . $field, $_POST[ $field ] );
				update_post_meta( $post_id, $field, $new );
			} else {
				delete_post_meta( $post_id, $field );
			}
		}

	}

	do_action( 'edd_save_post', $post_id, $post );
}

add_action( 'save_post', 'edd_page_meta_box_save', 10, 2 );