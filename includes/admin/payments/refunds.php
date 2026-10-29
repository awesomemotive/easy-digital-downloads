<?php
/**
 * Refund Details Sections
 *
 * @package    EDD\Admin\Orders
 * @copyright  Copyright (c) 2021, Sandhills Development, LLC
 * @license    https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since      3.0
 */

use EDD\Admin\Orders\Overview;
use EDD\Orders\Order;
use EDD\Utils\Convert;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/** Header ******************************************************************/

/**
 * Outputs Refund header region/notice of Refund vs. Order.
 *
 * @since 3.0
 *
 * @param Order $refund Current Refund.
 */
function edd_refund_details_notice( $refund ) {
	$order_url = edd_get_admin_url(
		array(
			'id'   => absint( $refund->parent ),
			'page' => 'edd-payment-history',
			'view' => 'view-order-details',
		)
	);
	?>

<div class="edit-post-editor-regions__header">
	<div class="edit-post-header notice-warning" style="border-left-width: 4px; border-left-style: solid;">

		<div class="edit-post-header__settings">
			<a href="<?php echo esc_url( $order_url ); ?>" class="button button-secondary" autofocus>
				<?php esc_html_e( 'View Original Order', 'easy-digital-downloads' ); ?>
			</a>
		</div>

		<div class="edit-post-header__toolbar">
			<?php
			esc_html_e( 'You are viewing a refund record.', 'easy-digital-downloads' );
			$tooltip = new EDD\HTML\Tooltip(
				array(
					'content' => __( 'A refund is a read-only record to help balance your store&#39;s books.', 'easy-digital-downloads' ),
				)
			);
			$tooltip->output();
			?>
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
 * @param Order $refund Current Refund.
 */
function edd_refund_details_items( $refund ) {
	$_items       = array();
	$_adjustments = array();

	$items = edd_get_order_items(
		array(
			'order_id' => $refund->id,
			'number'   => 999,
		)
	);

	foreach ( $items as $item ) {
		$item_adjustments = array();

		$adjustments = edd_get_order_adjustments(
			array(
				'object_id'   => $item->id,
				'number'      => 999,
				'object_type' => 'order_item',
				'type'        => array(
					'discount',
					'credit',
					'fee',
				),
			)
		);

		foreach ( $adjustments as $adjustment ) {
			// @todo edd_get_order_adjustment_to_json()?
			$adjustment_args = array(
				'id'           => esc_html( $adjustment->id ),
				'objectId'     => esc_html( $adjustment->object_id ),
				'objectType'   => esc_html( $adjustment->object_type ),
				'typeId'       => esc_html( $adjustment->type_id ),
				// Escaped by the view on render.
				'type'         => $adjustment->type,
				'description'  => Convert::decode_entities( $adjustment->description ),
				'subtotal'     => esc_html( $adjustment->subtotal ),
				'tax'          => esc_html( $adjustment->tax ),
				'total'        => esc_html( $adjustment->total ),
				'dateCreated'  => esc_html( $adjustment->date_created ),
				'dateModified' => esc_html( $adjustment->date_modified ),
				'uuid'         => esc_html( $adjustment->uuid ),
			);

			$item_adjustments[] = $adjustment_args;
			$_adjustments[]     = $adjustment_args;
		}

		// @todo edd_get_order_item_to_json()?
		$_items[] = array(
			'id'           => esc_html( $item->id ),
			'orderId'      => esc_html( $item->order_id ),
			'productId'    => esc_html( $item->product_id ),
			// Escaped by the view on render.
			'productName'  => Convert::decode_entities( $item->get_order_item_name() ),
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

	$adjustments = edd_get_order_adjustments(
		array(
			'object_id'   => $refund->id,
			'number'      => 999,
			'object_type' => 'order',
			'type'        => array(
				'discount',
				'credit',
				'fee',
			),
		)
	);

	foreach ( $adjustments as $adjustment ) {
		// @todo edd_get_order_adjustment_to_json()?
		$_adjustments[] = array(
			'id'           => esc_html( $adjustment->id ),
			'objectId'     => esc_html( $adjustment->object_id ),
			'objectType'   => esc_html( $adjustment->object_type ),
			'typeId'       => esc_html( $adjustment->type_id ),
			// Escaped by the view on render.
			'type'         => $adjustment->type,
			'description'  => Convert::decode_entities( $adjustment->description ),
			'subtotal'     => esc_html( $adjustment->subtotal ),
			'tax'          => esc_html( $adjustment->tax ),
			'total'        => esc_html( $adjustment->total ),
			'dateCreated'  => esc_html( $adjustment->date_created ),
			'dateModified' => esc_html( $adjustment->date_modified ),
			'uuid'         => esc_html( $adjustment->uuid ),
		);
	}

	$has_tax  = 'none';
	$tax_rate = $refund->id ? $refund->get_tax_rate() : false;
	$location = array(
		'rate'    => $tax_rate,
		'country' => '',
		'region'  => '',
	);
	if ( $tax_rate ) {
		$has_tax         = $location;
		$has_tax['rate'] = $tax_rate;
		if ( $refund->tax_rate_id ) {
			$tax_rate_object = $refund->get_tax_rate_object();
			if ( $tax_rate_object ) {
				$has_tax['country'] = $tax_rate_object->name;
				$has_tax['region']  = $tax_rate_object->description;
			}
		}
	}

	wp_localize_script(
		'edd-admin-orders',
		'eddAdminOrderOverview',
		array(
			'items'       => $_items,
			'adjustments' => $_adjustments,
			'refunds'     => array(),
			'isAdding'    => false,
			'isRefund'    => true,
			'hasQuantity' => true,
			'hasTax'      => $has_tax,
			'order'       => array(
				'currency'       => $refund->currency,
				'currencySymbol' => html_entity_decode( edd_currency_symbol( $refund->currency ) ),
				'subtotal'       => $refund->subtotal,
				'discount'       => $refund->discount,
				'tax'            => $refund->tax,
				'total'          => $refund->total,
			),
			'urls'        => Overview::get_urls(),
			'i18n'        => Overview::get_i18n(),
		)
	);

	?>
	<div id="edd-order-overview" class="postbox edd-edit-purchase-element edd-order-overview"></div>
	<?php
}

/**
 * Outputs Refund Notes box.
 *
 * @since 3.0
 *
 * @param Order $refund Current Refund.
 */
function edd_refund_details_notes( $refund ) {
	?>
	<div id="edd-order-items" class="postbox edd-edit-purchase-element">
		<h2 class="hndle">
			<?php esc_html_e( 'Refund Notes', 'easy-digital-downloads' ); ?>
		</h2>

		<div class="inside">
			<?php edd_order_details_notes( $refund ); ?>
		</div>
	</div>
	<?php
}

/**
 * Outputs "Refund Details" box.
 *
 * @since 3.0
 *
 * @param Order $refund Current Refund.
 */
function edd_refund_details_attributes( $refund ) {
	$refund_date = edd_get_edd_timezone_equivalent_date_from_utc( EDD()->utils->date( $refund->date_created, 'utc', true ) );

	$trash_url = wp_nonce_url(
		edd_get_admin_url(
			array(
				'edd-action'  => 'trash_order',
				'purchase_id' => absint( $refund->id ),
				'order_type'  => 'refund',
				'page'        => 'edd-payment-history',
			)
		),
		'edd_payment_nonce'
	);

	$order_url = edd_get_admin_url(
		array(
			'id'   => absint( $refund->parent ),
			'page' => 'edd-payment-history',
			'view' => 'view-order-details',
		)
	);

	$order = edd_get_order( $refund->parent );
	?>

<div class="postbox">
	<h2 class="hndle">
		<?php esc_html_e( 'Refund Attributes', 'easy-digital-downloads' ); ?>
	</h2>

	<div class="edd-admin-box-inside">
		<time datetime="<?php echo esc_attr( EDD()->utils->date( $refund->date_created, null, true )->toDateTimeString() ); ?>" style="line-height: normal">
			<?php echo edd_date_i18n( $refund->date_created, 'M. d, Y' ) . '<br />' . edd_date_i18n( strtotime( $refund->date_created ), 'H:i' ); ?> <?php echo esc_html( edd_get_timezone_abbr() ); ?>
		</time>

		<br /><br />

		<a href="<?php echo esc_url( $trash_url ); ?>" class="edd-delete-payment edd-delete">
			<?php esc_html_e( 'Move to Trash', 'easy-digital-downloads' ); ?>
		</a>
	</div>

	<div class="edd-admin-box-inside edd-admin-box-inside--row">
		<div class="edd-form-group">
			<span class="edd-form-group__label">
				<?php esc_html_e( 'Original Order', 'easy-digital-downloads' ); ?>
			</span>
			<div class="edd-form-group__control">
				<a href="<?php echo esc_url( $order_url ); ?>"><?php echo esc_html( $order->number ); ?></a>
			</div>
		</div>
	</div>
</div>

	<?php
}

/**
 * Outputs related Refunds.
 *
 * @since 3.0
 *
 * @param Order $refund
 */
function edd_refund_details_related_refunds( $refund ) {
	$refunds = array_filter(
		edd_get_order_refunds( $refund->parent ),
		function ( $related_refund ) use ( $refund ) {
			return $related_refund->id !== $refund->id;
		}
	);

	if ( empty( $refunds ) ) {
		return;
	}
	?>

<div class="postbox edd-order-data">
	<h2 class="hndle">
		<?php esc_html_e( 'Related Refunds', 'easy-digital-downloads' ); ?>
	</h2>

	<?php
	foreach ( $refunds as $refund ) :
		$refund_url = edd_get_admin_url(
			array(
				'page' => 'edd-payment-history',
				'view' => 'view-refund-details',
				'id'   => absint( $refund->id ),
			)
		);
		?>
		<div class="edd-admin-box-inside">
			<div class="edd-form-group">
				<a href="<?php echo esc_url( $refund_url ); ?>" class="edd-form-group__label">
					<?php echo esc_html( $refund->number ); ?>
				</a>
				<div class="edd-form-group__control">
					<time datetime="<?php echo esc_attr( EDD()->utils->date( $refund->date_created, null, true )->toDateTimeString() ); ?>" style="line-height: normal">
						<?php echo edd_date_i18n( $refund->date_created, 'M. d, Y' ) . '<br />' . edd_date_i18n( strtotime( $refund->date_created ), 'H:i' ); ?> <?php echo esc_html( edd_get_timezone_abbr() ); ?>
					</time>
				</div>
		</div>
	<?php endforeach; ?>
</div>

	<?php
}
