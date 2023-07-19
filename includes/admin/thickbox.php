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
	/* translators: singular download label */
	$text = sprintf( __( 'Insert %s', 'easy-digital-downloads' ), edd_get_label_singular() );

	// Output the thickbox button
	echo '<a href="#TB_inline?&width=600&height=300&inlineId=choose-download" name="' . esc_attr( $text ) . '" class="thickbox button edd-thickbox">' . $icon . esc_html( $text ) . '</a>';
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
				( $(this).val() === 'button' )
					? $('#edd-color-choice').show()
					: $('#edd-color-choice').hide();
			});
		});
	</script>

	<div id="choose-download" style="display: none;">
		<div id="choose-download-wrapper">
			<div class="wrap">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row" valign="top">
								<?php echo edd_get_label_singular(); ?>
							</th>
							<td>
								<?php echo EDD()->html->product_dropdown( array( 'chosen' => true ) ); ?>
								<p class="description"><?php esc_html_e( 'Choose an existing product', 'easy-digital-downloads' ); ?></p>
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
									<p class="description"><?php esc_html_e( 'How do you want this to work?', 'easy-digital-downloads' ); ?></p>
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
								<p class="description"><?php esc_html_e( 'Choose between a Button or a Link', 'easy-digital-downloads' ); ?></p>
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
									<p class="description"><?php esc_html_e( 'Choose the button color', 'easy-digital-downloads' ); ?></p>
								</td>
							</tr>
						<?php endif; ?>

						<tr>
							<th scope="row" valign="top">
								<?php esc_html_e( 'Text', 'easy-digital-downloads' ); ?>
							</th>
							<td>
								<input type="text" class="regular-text" id="edd-text" value="" placeholder="<?php _e( 'View Product', 'easy-digital-downloads' ); ?>"/>
								<p class="description"><?php esc_html_e( 'This is the text inside the button or link', 'easy-digital-downloads' ); ?></p>
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="submit-wrapper">
				<div>
					<a id="edd-cancel-download-insert" class="button" onclick="tb_remove();"><?php esc_html_e( 'Cancel', 'easy-digital-downloads' ); ?></a>
					<input type="button" id="edd-insert-download" class="button-primary" value="<?php printf( esc_html__( 'Insert %s', 'easy-digital-downloads' ), esc_html( edd_get_label_singular() ) ); ?>" onclick="insertDownload();" />
				</div>
			</div>
		</div>
	</div>

<?php
}
add_action( 'admin_footer', 'edd_admin_footer_for_thickbox' );
