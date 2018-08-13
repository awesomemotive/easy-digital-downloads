<?php
/**
 * Thickbox
 *
 * @package     EDD
 * @subpackage  Admin
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Whether the current admin area page is one that allows the insertion of a
 * button to make inserting Downloads easier.
 *
 * @since 3.0
 * @global $pagenow $pagenow
 * @global $typenow $typenow
 * @return boolean
 */
function edd_is_insertable_admin_page() {
	global $pagenow, $typenow;

	// Allowed pages
	$pages = array(
		'post.php',
		'page.php',
		'post-new.php',
		'post-edit.php'
	);

	// Allowed post types
	$types = get_post_types_by_support( 'edd_insert_download' );

	// Return if page and type are allowed
	return in_array( $pagenow, $pages, true ) && in_array( $typenow, $types, true );
}

/**
 * Adds an "Insert Download" button above the TinyMCE Editor on add/edit screens.
 *
 * @since 1.0
 * @return string "Insert Download" Button
 */
function edd_media_button() {

	// Bail if not a post new/edit screen
	if ( ! edd_is_insertable_admin_page() ) {
		return;
	}

	// Setup the icon
	$icon = '<span class="wp-media-buttons-icon dashicons dashicons-download" id="edd-media-button"></span>';

	// Output the thickbox button
	echo '<a href="#TB_inline?width=640&height=300&inlineId=choose-download" class="thickbox button edd-thickbox" style="padding-left: .4em;">' . $icon . sprintf( __( 'Insert %s', 'easy-digital-downloads' ), edd_get_label_singular() ) . '</a>';
}
add_action( 'media_buttons', 'edd_media_button', 11 );

/**
 * Admin Footer For Thickbox
 *
 * Prints the footer code needed for the Insert Download
 * TinyMCE button.
 *
 * @since 1.0
 * @global $pagenow
 * @global $typenow
 * @return void
 */
function edd_admin_footer_for_thickbox() {

	// Bail if not a post new/edit screen
	if ( ! edd_is_insertable_admin_page() ) {
		return;
	}

	// Styles
	$styles = array(
		'text link' => esc_html__( 'Link',   'easy-digital-downloads' ),
		'button'    => esc_html__( 'Button', 'easy-digital-downloads' )
	);

	// Colors
	$colors = edd_get_button_colors();

	?>

	<script type="text/javascript">

		/**
		 * Used to insert the download shortcode with attributes
		 */
		function insertDownload() {
			var id     = jQuery('#products').val(),
				direct = jQuery('#select-edd-direct').val(),
				style  = jQuery('#select-edd-style').val(),
				color  = jQuery('#select-edd-color').is(':visible') ? jQuery( '#select-edd-color').val() : '',
				text   = jQuery('#edd-text').val() || '<?php _e( 'Purchase', 'easy-digital-downloads' ); ?>';

			// Return early if no download is selected
			if ( '' === id ) {
				alert('<?php _e( 'You must choose a download', 'easy-digital-downloads' ); ?>');
				return;
			}

			if ( '2' === direct ) {
				direct = ' direct="true"';
			} else {
				direct = '';
			}

			// Send the shortcode to the editor
			window.send_to_editor('[purchase_link id="' + id + '" style="' + style + '" color="' + color + '" text="' + text + '"' + direct +']');
		}

		jQuery(document).ready(function ($) {
			$('#select-edd-style').change(function () {
				if ($(this).val() === 'button') {
					$('#edd-color-choice').slideDown();
				} else {
					$('#edd-color-choice').slideUp();
				}
			});
		});
	</script>

	<div id="choose-download" style="display: none;">
		<div class="wrap">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row" valign="top">
							<?php echo edd_get_label_singular(); ?>
							<?php esc_html_e( '', 'easy-digital-downloads' ); ?>
						</th>
						<td>
							<?php echo EDD()->html->product_dropdown( array( 'chosen' => true ) ); ?>
						</td>
					</tr>

					<?php if ( edd_shop_supports_buy_now() ) : ?>
						<tr>
							<th scope="row" valign="top">
								<?php esc_html_e( 'Behavior', 'easy-digital-downloads' ); ?>
							</th>
							<td>
								<select id="select-edd-direct">
									<option value="1"><?php _e( 'Add to Cart', 'easy-digital-downloads' ); ?></option>
									<option value="2"><?php _e( 'Direct Link', 'easy-digital-downloads' ); ?></option>
								</select>
							</td>
						</tr>
					<?php endif; ?>

					<tr>
						<th scope="row" valign="top">
							<?php esc_html_e( 'Style', 'easy-digital-downloads' ); ?>
						</th>
						<td>
							<select id="select-edd-style">
								<?php
								foreach ( $styles as $style => $label ) {
									echo '<option value="' . esc_attr( $style ) . '">' . esc_html( $label ) . '</option>';
								}
								?>
							</select>

						</td>
					</tr>

					<?php if ( ! empty( $colors ) ) : ?>
						<tr id="edd-color-choice" style="display: none;">
							<th scope="row" valign="top">
								<?php esc_html_e( 'Color', 'easy-digital-downloads' ); ?>
							</th>
							<td>
								<select id="select-edd-color">
									<?php
										foreach ( $colors as $key => $color ) {
											echo '<option value="' . str_replace( ' ', '_', $key ) . '">' . $color['label'] . '</option>';
										}
									?>
								</select>
							</td>
						</tr>
					<?php endif; ?>

					<tr>
						<th scope="row" valign="top">
							<?php esc_html_e( 'Text', 'easy-digital-downloads' ); ?>
						</th>
						<td>
							<input type="text" class="regular-text" id="edd-text" value="" placeholder="<?php _e( 'View Product', 'easy-digital-downloads' ); ?>"/>
						</td>
					</tr>
				</tbody>
			</table>

			<p class="submit">
				<input type="button" id="edd-insert-download" class="button-primary" value="<?php echo sprintf( __( 'Insert %s', 'easy-digital-downloads' ), edd_get_label_singular() ); ?>" onclick="insertDownload();" />
				<a id="edd-cancel-download-insert" class="button-secondary" onclick="tb_remove();"><?php _e( 'Cancel', 'easy-digital-downloads' ); ?></a>
			</p>
		</div>
	</div>

<?php
}
add_action( 'admin_footer', 'edd_admin_footer_for_thickbox' );
