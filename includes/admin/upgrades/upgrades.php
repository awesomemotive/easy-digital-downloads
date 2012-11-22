<?php

/**
 * Upgrade Screen
 *
 * @package     Easy Digital Downloads
 * @subpackage  admin/upgrades
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.1
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Render Upgrades Screen
 *
 * @access      private
 * @since       1.3.1
 * @return      void
*/

function edd_upgrades_screen() {
	?>
	<div class="wrap">
		<h2><?php _e( 'Easy Digital Downloads - Upgrades', 'edd' ); ?></h2>
		<div id="edd-upgrade-status">
			<p>
				<?php _e( 'The upgrade process has started, please be patient. This could take several minutes. You will be automatically redirected when the upgrade is finished.', 'edd' ); ?>
				<img src="<?php echo EDD_PLUGIN_URL . '/includes/images/loading.gif'; ?>" id="edd-upgrade-loader"/>
			</p>
		</div>
		<script type="text/javascript">
			jQuery( document ).ready( function() {
				// trigger upgrades on page load
				var data = { action: 'edd_trigger_upgrades' };
		        jQuery.post( ajaxurl, data, function (response) {
		        	if( response == 'complete' ) {
			        	jQuery('#edd-upgrade-loader').hide();
			        	document.location.href = 'index.php'; // redirect back to the dashboard when complete
					}
		        });
			});
		</script>
	</div>
	<?php
}
