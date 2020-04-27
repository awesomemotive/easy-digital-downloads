<?php
/**
 * Refund Details Sections
 *
 * @package    EDD
 * @subpackage Admin/Orders
 * @copyright  Copyright (c) 2020, Easy Digital Downloads, LLC
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      3.0
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
				<?php esc_html_e( 'View Order', 'easy-digital-downloads' ); ?>
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
	$_items = array();
	$items  = edd_get_order_items( array(
		'order_id' => $refund->id,
		'number'   => 999,
	) );

	foreach ( $items as $item ) {
		// @todo edd_get_order_item_to_json()?
		$_items[] = array(
			'id'           => esc_html( $item->id ),
			'orderId'      => esc_html( $item->order_id ),
			'productId'    => esc_html( $item->product_id ),
			'productName'  => esc_html( $item->get_order_item_name() ),
			'priceId'      => esc_html( $item->price_id ),
			'cartIndex'    => esc_html( $item->cart_index ),
			'type'         => esc_html( $item->type ),
			'status'       => esc_html( $item->status ),
			'quantity'     => esc_html( $item->quantity ),
			'amount'       => esc_html( $item->amount ),
			'subtotal'     => esc_html( $item->subtotal ),
			'discount'     => esc_html( $item->discount ),
			'tax'          => esc_html( $item->tax ),
			'total'        => esc_html( $item->total ),
			'dateCreated'  => esc_html( $item->date_created ),
			'dateModified' => esc_html( $item->date_modified ),
			'uuid'         => esc_html( $item->uuid ),
		);
	}

	wp_localize_script(
		'edd-admin-orders',
		'eddAdminOrderOverview',
		array(
			'items'        => $_items,
			'adjustments'  => array(),
			'refunds'      => array(),
			'isAdding'     => false,
			'isRefund'     => true,
			'hasQuantity'  => true === edd_item_quantities_enabled(),
			'hasTax'       => true === edd_use_taxes()
				? array(
					'rate'    => 0,
					'country' => '',
					'region'  => '',
				)
				: 0,
		)
	);

	$templates = array(
		'totals',
		'item',
		'adjustment',
		'adjustment-discount',
	);

	foreach ( $templates as $tmpl ) {
		echo '<script type="text/html" id="tmpl-edd-admin-order-' . esc_attr( $tmpl ) . '">';
		require_once EDD_PLUGIN_DIR . 'includes/admin/views/tmpl-order-' . $tmpl . '.php';
		echo '</script>';
	}
?>

<div id="edd-order-overview" class="postbox edd-edit-purchase-element edd-order-overview">
	<table id="edd-order-overview-summary" class="widefat wp-list-table edd-order-overview-summary edd-order-overview-summary--refund">
		<thead>
			<tr>
				<th class="column-name column-primary"><?php echo esc_html( edd_get_label_singular() ); ?></th>
				<th class="column-amount"><?php esc_html_e( 'Unit Price', 'easy-digital-downloads' ); ?></th>
				<?php if ( true === edd_item_quantities_enabled() ) : ?>
				<th class="column-quantity"><?php esc_html_e( 'Quantity', 'easy-digital-downloads' ); ?></th>
				<?php endif; ?>
				<th class="column-subtotal column-right"><?php esc_html_e( 'Amount', 'easy-digital-downloads' ); ?></th>
			</tr>
		</thead>
	</table>
</div>

<?php
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
