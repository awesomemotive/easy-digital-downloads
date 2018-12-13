<?php
/**
 * Thickbox
 *
 * @package     EDD
 * @subpackage  Admin
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Adds an "Insert Download" button above the TinyMCE Editor on add/edit screens.
 *
 * @since 1.0
 * @return string "Insert Download" Button
 */
function edd_media_button() {
	global $pagenow, $typenow;
	$output = '';

	/** Only run in post/page creation and edit screens */
	if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) && $typenow != 'download' ) {

		$img = '<span class="wp-media-buttons-icon dashicons dashicons-download" id="edd-media-button"></span>';
		$output = '<a href="#TB_inline?width=640&inlineId=choose-download" class="thickbox button edd-thickbox" style="padding-left: .4em;">' . $img . sprintf( __( 'Insert %s', 'easy-digital-downloads' ), strtolower( edd_get_label_singular() ) ) . '</a>';

	}

	echo $output;
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
	global $pagenow, $typenow;

	// Only run in post/page creation and edit screens
	if ( in_array( $pagenow, array( 'post.php', 'page.php', 'post-new.php', 'post-edit.php' ) ) && $typenow != 'download' ) { ?>
		<script type="text/javascript">
			function insertDownload() {
				var id = jQuery('#products').val(),
					direct = jQuery('#select-edd-direct').val(),
					style = jQuery('#select-edd-style').val(),
					color = jQuery('#select-edd-color').is(':visible') ? jQuery('#select-edd-color').val() : '',
					text = jQuery('#edd-text').val() || '<?php _e( "Purchase", "easy-digital-downloads" ); ?>';

				// Return early if no download is selected
				if ('' === id) {
					alert('<?php _e( "You must choose a download", "easy-digital-downloads" ); ?>');
					return;
				}

				if ( '2' == direct ) {
					direct = ' direct="true"';
				} else {
					direct = '';
				}

				if ( 'text link' == style ) {
					style = 'plain';
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
			<div class="wrap" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
				<p><?php echo sprintf( __( 'Use the form below to insert the shortcode for purchasing a %s', 'easy-digital-downloads' ), edd_get_label_singular() ); ?></p>
				<div>
					<?php echo EDD()->html->product_dropdown( array( 'chosen' => true )); ?>
				</div>
				<?php if( edd_shop_supports_buy_now() ) : ?>
					<div>
						<select id="select-edd-direct" style="clear: both; display: block; margin-bottom: 1em; margin-top: 1em;">
							<option value="0"><?php _e( 'Choose the button behavior', 'easy-digital-downloads' ); ?></option>
							<option value="1"><?php _e( 'Add to Cart', 'easy-digital-downloads' ); ?></option>
							<option value="2"><?php _e( 'Direct Purchase Link', 'easy-digital-downloads' ); ?></option>
						</select>
					</div>
				<?php endif; ?>
				<div>
					<select id="select-edd-style" style="clear: both; display: block; margin-bottom: 1em; margin-top: 1em;">
						<option value=""><?php _e( 'Choose a style', 'easy-digital-downloads' ); ?></option>
						<?php
							$styles = array( 'button', 'text link' );
							foreach ( $styles as $style ) {
								echo '<option value="' . $style . '">' . $style . '</option>';
							}
						?>
					</select>
				</div>
				<?php
				$colors = edd_get_button_colors();
				if( $colors ) { ?>
				<div id="edd-color-choice" style="display: none;">
					<select id="select-edd-color" style="clear: both; display: block; margin-bottom: 1em;">
						<option value=""><?php _e('Choose a button color','easy-digital-downloads' ); ?></option>
						<?php
							foreach ( $colors as $key => $color ) {
								echo '<option value="' . str_replace( ' ', '_', $key ) . '">' . $color['label'] . '</option>';
							}
						?>
					</select>
				</div>
				<?php } ?>
				<div>
					<input type="text" class="regular-text" id="edd-text" value="" placeholder="<?php _e( 'Link text . . .', 'easy-digital-downloads' ); ?>"/>
				</div>
				<p class="submit">
					<input type="button" id="edd-insert-download" class="button-primary" value="<?php echo sprintf( __( 'Insert %s', 'easy-digital-downloads' ), edd_get_label_singular() ); ?>" onclick="insertDownload();" />
					<a id="edd-cancel-download-insert" class="button-secondary" onclick="tb_remove();"><?php _e( 'Cancel', 'easy-digital-downloads' ); ?></a>
				</p>
			</div>
		</div>
	<?php
	}
}
add_action( 'admin_footer', 'edd_admin_footer_for_thickbox' );
