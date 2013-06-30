<?php
/**
 * Import / Export Settings
 *
 * @package     EDD
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.7
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;



function edd_settings_export_import() {

	$current_page = admin_url( 'edit.php?post_type=download&page=edd-reports' );
	$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'reports';
	?>
	<div class="wrap">
		<h2><?php _e( 'Export / Import Settings', 'edd' ); ?></h2>
		<div class="metabox-holder">
			<div class="postbox">
				<h3><span><?php _e( 'Export Settings', 'edd' ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'Export the Easy Digital Downloads settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'edd' ); ?></p>
					<form method="post" action="<?php echo admin_url( 'tools.php?page=edd-settings-export-import' ); ?>">
						<p>
							<input type="hidden" name="edd_action" value="export_settings" />
						</p>
						<p>
							<?php wp_nonce_field( 'edd_settings_export_nonce', 'edd_settings_export_nonce' ); ?>
							<?php submit_button( __( 'Export', 'edd' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->
			<div class="postbox">
				<h3><span><?php _e( 'Import Settings', 'edd' ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'Import the Easy Digital Downloads settings from .json file. This file can be obtained by exporting the settings on another user using the form above.', 'edd' ); ?></p>
					<form method="post" action="<?php echo admin_url( 'tools.php?page=edd-settings-export-import' ); ?>">
						<p>
							<input type="file" name="import_file"/>
						</p>
						<p>
							<input type="hidden" name="edd_action" value="import_settings" />
							<?php wp_nonce_field( 'edd_settings_export_nonce', 'edd_settings_export_nonce' ); ?>
							<?php submit_button( __( 'Import', 'edd' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->
		</div><!-- .metabox-holder -->
	</div><!-- .wrap -->
	<?php

}