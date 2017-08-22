<?php
/**
 * Upgrade Screen
 *
 * @package     EDD
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.1
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Render Upgrades Screen
 *
 * @since 1.3.1
 * @return void
*/
function edd_upgrades_screen() {
	$action = isset( $_GET['edd-upgrade'] ) ? sanitize_text_field( $_GET['edd-upgrade'] ) : '';
	$step   = isset( $_GET['step'] )        ? absint( $_GET['step'] )                     : 1;
	$total  = isset( $_GET['total'] )       ? absint( $_GET['total'] )                    : false;
	$custom = isset( $_GET['custom'] )      ? absint( $_GET['custom'] )                   : 0;
	$number = isset( $_GET['number'] )      ? absint( $_GET['number'] )                   : 100;
	$steps  = round( ( $total / $number ), 0 );
	if ( ( $steps * $number ) < $total ) {
		$steps++;
	}

	$doing_upgrade_args = array(
		'page'        => 'edd-upgrades',
		'edd-upgrade' => $action,
		'step'        => $step,
		'total'       => $total,
		'custom'      => $custom,
		'steps'       => $steps
	);
	update_option( 'edd_doing_upgrade', $doing_upgrade_args );
	if ( $step > $steps ) {
		// Prevent a weird case where the estimate was off. Usually only a couple.
		$steps = $step;
	}
	?>
	<div class="wrap">
		<h2><?php _e( 'Easy Digital Downloads - Upgrades', 'easy-digital-downloads' ); ?></h2>

		<?php if( ! empty( $action ) ) : ?>

			<div id="edd-upgrade-status">
				<p><?php _e( 'The upgrade process has started, please be patient. This could take several minutes. You will be automatically redirected when the upgrade is finished.', 'easy-digital-downloads' ); ?></p>

				<?php if( ! empty( $total ) ) : ?>
					<p><strong><?php printf( __( 'Step %d of approximately %d running', 'easy-digital-downloads' ), $step, $steps ); ?></strong></p>
				<?php endif; ?>
			</div>
			<script type="text/javascript">
				setTimeout(function() { document.location.href = "index.php?edd_action=<?php echo $action; ?>&step=<?php echo $step; ?>&total=<?php echo $total; ?>&custom=<?php echo $custom; ?>"; }, 250);
			</script>

		<?php else : ?>

			<div id="edd-upgrade-status">
				<p>
					<?php _e( 'The upgrade process has started, please be patient. This could take several minutes. You will be automatically redirected when the upgrade is finished.', 'easy-digital-downloads' ); ?>
					<img src="<?php echo EDD_PLUGIN_URL . '/assets/images/loading.gif'; ?>" id="edd-upgrade-loader"/>
				</p>
			</div>
			<script type="text/javascript">
				jQuery( document ).ready( function() {
					// Trigger upgrades on page load
					var data = { action: 'edd_trigger_upgrades' };
					jQuery.post( ajaxurl, data, function (response) {
						if( response == 'complete' ) {
							jQuery('#edd-upgrade-loader').hide();
							document.location.href = 'index.php'; // Redirect to the dashboard
						}
					});
				});
			</script>

		<?php endif; ?>

	</div>
	<?php
}
