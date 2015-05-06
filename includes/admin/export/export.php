<?php
/**
 * Export Screen
 *
 * @package     EDD
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.4
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Render Export Screen
 *
 * @since 2.4
 * @return void
*/
function edd_export_screen() {

	$action = isset( $_GET['edd-export'] )  ? sanitize_text_field( $_GET['edd-export'] )  : '';
	$month  = isset( $_GET['month'] )       ? absint( $_GET['month'] )                    : date( 'n' );
	$year   = isset( $_GET['year'] )        ? absint( $_GET['year'] )                     : date( 'Y' );
	$status = isset( $_GET['status'] )      ? sanitize_text_field( $_GET['status'] )      : 'complete';
	$step   = isset( $_GET['step'] )        ? absint( $_GET['step'] )                     : 1;
	?>
	<div class="wrap">
		<h2><?php _e( 'Easy Digital Downloads - Export', 'edd' ); ?></h2>

		<div id="edd-export-status">
			<p><?php _e( 'Your export is processing, please be patient. This could take several minutes.', 'edd' ); ?></p>
		</div>
		<script type="text/javascript">
			document.location.href = "index.php?edd_action=<?php echo $action; ?>&step=<?php echo $step; ?>&month=<?php echo $month; ?>&year=<?php echo $year; ?>&status=<?php echo $status; ?>";
		</script>

	</div>
	<?php
}