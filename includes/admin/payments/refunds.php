<?php
/**
 * Refund Details Sections
 *
 * @package     EDD
 * @subpackage  Admin/Orders
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Header ******************************************************************/

/**
 * Outputs Refund header region/notice of Refund vs. Order.
 *
 * @since 3.0
 *
 * @param \EDD\Orders\Order $refund Current Refund.
 */
function edd_refund_details_notice( $refund ) {
	$order_url = edd_get_admin_url(
		array(
			'id'        => $refund->parent,
			'page'      => 'edd-payment-history',
			'view'      => 'view-order-details',
		)
	);
?>

<div class="edit-post-editor-regions__header">
	<div class="edit-post-header notice-warning" style="border-left-width: 4px; border-left-style: solid;">

		<div class="edit-post-header__settings">
			<a href="<?php echo esc_url( $order_url ); ?>" class="button button-secondary" autofocus>
				<?php esc_html_e( 'View Full Order', 'easy-digital-downloads' ); ?>
			</a>
		</div>

		<div class="edit-post-header__toolbar">
			<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( 'A refund is a read-only record to help balance your store&#39;s books.', 'easy-digital-downloads' ); ?>"></span>&nbsp;
			<?php esc_html_e( 'You are viewing a refund record.', 'easy-digital-downloads' ); ?>
		</div>

	</div>
</div>

<?php
}

/** Main **********************************************************************/

/**
 * Outputs the Refund Items box.
 *
 * @since 3.0
 *
 * @param EDD\Orders\Order $refund Current Refund.
 */
function edd_refund_details_items( $refund ) {

	// Load list table if not already loaded
	if ( ! class_exists( '\\EDD\\Admin\\Order_Refund_Items_Table' ) ) {
		require_once 'class-order-refund-items-table.php';
	}

	// Query for items
	$refund_items = new EDD\Admin\Order_Refund_Items_Table();
	$refund_items->prepare_items();
?>

<div id="edd-order-items" class="postbox edd-edit-purchase-element">
	<h3 class="hndle">
		<span><?php esc_html_e( 'Refunded Items', 'easy-digital-downloads' ); ?></span>
	</h3>

	<div class="edd-order-children-wrapper edd-order-refund-items <?php echo 'child-count-' . count( $refund_items->items ); ?>">
		<?php $refund_items->display(); ?>
	</div>
</div>

<?php
	/**
	 * Allows
	 *
	 * @since 3.0.0
	 *
	 * @param int $refund_ID ID of the current Refund.
	 */
	do_action( 'edd_view_order_details_files_after', $refund->id );
}

/**
 * Outputs Refund Notes box.
 *
 * @since 3.0
 *
 * @param \EDD\Orders\Order $refund Current Refund.
 */
function edd_refund_details_notes( $refund ) {
?>

<div id="edd-order-items" class="postbox edd-edit-purchase-element">
	<h3 class="hndle">
		<span><?php esc_html_e( 'Refund Notes', 'easy-digital-downloads' ); ?></span>
	</h3>

	<div class="inside">
		<?php edd_order_details_notes( $refund ); ?>
	</div>
</div>

<?php
}

/**
 * Outputs Refund Attribuets box.
 *
 * @since 3.0
 *
 * @param \EDD\Orders\Order $refund Current Refund.
 */
function edd_refund_details_attributes( $refund ) {
	$refund_date = edd_get_edd_timezone_equivalent_date_from_utc( EDD()->utils->date( $refund->date_created, 'utc', true ) );
?>

<div class="postbox">
	<h3 class="hndle">
		<span><?php esc_html_e( 'Refund Attributes', 'easy-digital-downloads' ); ?></span>
	</h3>

	<div class="inside">
		<?php
		echo esc_html(
			sprintf(
				/* translators: %14s Refund date. %2$s Refund time. */
				__( 'Refund issued on %1$s at %2$s (%3$s)', 'easy-digital-downloads' ),
				$refund_date->format( get_option( 'date_format' ) ),
				$refund_date->format( get_option( 'time_format' ) ),
				edd_get_timezone_abbr()
			)
		);
		?>
	</div>
</div>

<?php
}
