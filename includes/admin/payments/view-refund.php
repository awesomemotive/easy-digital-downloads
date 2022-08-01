<?php
/**
 * View Refund page.
 *
 * @package     EDD
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Outputs the View Refund page.
 *
 * @since 3.0
 */
function edd_view_refund_page_content() {
	// @todo Avoid killing page ouput.
	if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		wp_die( __( 'Refund ID not supplied. Please try again.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ) );
	}

	$refund_id = absint( $_GET['id'] );
	$refund    = edd_get_order( $refund_id );

	// Check that the refund exists in the database.
	// @todo Avoid killing page ouput.
	if ( empty( $refund ) || 'refund' !== $refund->type ) {
		wp_die( __( 'The specified ID does not belong to an refund. Please try again.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ) );
	}

	wp_enqueue_script( 'edd-admin-orders' );
	// Enqueued for backwards compatibility. Empty file.
	wp_enqueue_script( 'edd-admin-payments' );
?>

<?php edd_refund_details_notice( $refund ); ?>

<div class="wrap edd-wrap">

	<h1><?php printf( esc_html__( 'Refund: %s', 'easy-digital-downloads' ), $refund->order_number ); ?></h1>

	<?php
	/**
	 * Allows output before Refund page content.
	 *
	 * @since 3.0
	 *
	 * @param int $refund_id ID of the current Refund.
	 */
	do_action( 'edd_view_refund_details_before', $refund->id );
	?>

	<div id="poststuff">
		<div id="edd-dashboard-widgets-wrap">
			<div id="post-body" class="metabox-holder columns-2">

				<div id="postbox-container-2" class="postbox-container">
					<div id="normal-sortables">
						<?php
						/**
						 * Allows output before the Refund details.
						 *
						 * @since 3.0
						 *
						 * @param int $refund_id ID of the current Refund.
						 */
						do_action( 'edd_view_refund_details_main_before', $refund->id );

						// Refund Items.
						edd_refund_details_items( $refund );

						// Notes.
						edd_refund_details_notes( $refund );

						/**
						 * Allows further output after the Refund details.
						 *
						 * @since 3.0
						 *
						 * @param int $refund_id ID of the current Refund.
						 */
						do_action( 'edd_view_refund_details_main_after', $refund->id );
						?>
					</div>
				</div>

				<div id="postbox-container-1" class="postbox-container">
					<div id="side-sortables">
						<?php
						/**
						 * Allows output before Refund sidebar content.
						 *
						 * @since 3.0
						 *
						 * @param int $refund_id ID of the current Refund.
						 */
						do_action( 'edd_view_refund_details_sidebar_before', $refund->id );

						// Attributes.
						edd_refund_details_attributes( $refund );

						// Related Refunds.
						edd_refund_details_related_refunds( $refund );

						/**
						 * Allows further output after Refund sidebar content.
						 *
						 * @since 3.0
						 *
						 * @param int $refund_id ID of the current Refund.
						 */
						do_action( 'edd_view_refund_details_sidebar_after', $refund->id );
						?>
					</div>
				</div>

			</div>
		</div>
	</div>

</div><!-- /.wrap -->

<?php
}
