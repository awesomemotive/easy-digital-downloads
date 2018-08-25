<?php
/**
 * Upgrade Screen
 *
 * @package     EDD
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.1
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Render Upgrades Screen
 *
 * @since 1.3.1
 * @return void
*/
function edd_upgrades_screen() {

	// Get the upgrade being performed
	$action = ! empty( $_GET['edd-upgrade'] )
		? sanitize_text_field( $_GET['edd-upgrade'] )
		: ''; ?>

	<div class="wrap">

	<?php if ( is_callable( 'edd_upgrade_render_' . $action ) ) {

		// This is the new method to register an upgrade routine, so we can use
		// an ajax and progress bar to display any needed upgrades.
		call_user_func( 'edd_upgrade_render_' . $action );

	} else {

		// This is the legacy upgrade method, which requires a page refresh
		// at each step.
		$step   = isset( $_GET['step']   ) ? absint( $_GET['step']   ) : 1;
		$total  = isset( $_GET['total']  ) ? absint( $_GET['total']  ) : false;
		$custom = isset( $_GET['custom'] ) ? absint( $_GET['custom'] ) : 0;
		$number = isset( $_GET['number'] ) ? absint( $_GET['number'] ) : 100;
		$steps  = round( ( $total / $number ), 0 );

		// Bump step
		if ( ( $steps * $number ) < $total ) {
			$steps++;
		}

		// Update step option
		update_option( 'edd_doing_upgrade', array(
			'post_type'   => 'download',
			'page'        => 'edd-tools',
			'tab'         => 'upgrades',
			'edd-upgrade' => $action,
			'step'        => $step,
			'total'       => $total,
			'custom'      => $custom,
			'steps'       => $steps
		) );

		// Prevent step estimate from going over
		if ( $step > $steps ) {
			$steps = $step;
		}

		if ( ! edd_maybe_resume_upgrade() ) {

		} else {
			if ( ! empty( $action ) ) :

				// Redirect URL
				$redirect = wp_nonce_url( edd_get_admin_url( array(
					'page'       => 'edd-tools',
					'tab'        => 'upgrades',
					'edd_action' => $action,
					'step'       => $step,
					'total'      => $total,
					'custom'     => $custom
				) ), 'edd_upgrades' ); ?>

				<div id="edd-upgrade-status">
					<p><?php _e( 'The upgrade process has started, please be patient. This could take several minutes. You will be automatically redirected when the upgrade is finished.', 'easy-digital-downloads' ); ?></p>

					<?php if ( ! empty( $total ) ) : ?>
						<p><strong><?php printf( __( 'Step %d of approximately %d running', 'easy-digital-downloads' ), $step, $steps ); ?></strong></p>
					<?php endif; ?>
				</div>
				<script type="text/javascript">
					setTimeout( function() {
						document.location.href = '<?php echo $redirect; // Do not escape ?>';
					}, 250 );
				</script>

			<?php else :

				// Redirect URL
				$redirect = wp_nonce_url( edd_get_admin_url( array(
					'page' => 'edd-tools',
					'tab'  => 'upgrades'
				) ), 'edd_upgrades' ); ?>

				<div id="edd-upgrade-status">
					<p>
						<?php _e( 'The upgrade process has started, please be patient. This could take several minutes. You will be automatically redirected when the upgrade is finished.', 'easy-digital-downloads' ); ?>
					</p>
				</div>

				<script type="text/javascript">
					jQuery( document ).ready( function() {

						// Trigger upgrades on page load
						var data = {
							action: 'edd_trigger_upgrades'
						};

						jQuery.post( ajaxurl, data, function (response) {
							if ( 'complete' !== response ) {
								return;
							}

							jQuery( '#edd-upgrade-loader' ).hide();

							setTimeout( function() {
								document.location.href = '<?php echo $redirect; // Do not escape ?>';
							}, 250 );
						});
					});
				</script>

			<?php endif;
		}
	} ?>

	</div>

	<?php
}
add_action( 'edd_tools_tab_upgrades', 'edd_upgrades_screen' );