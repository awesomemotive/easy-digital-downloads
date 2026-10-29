<?php
/**
 * Data localized for the order overview components.
 *
 * @package     EDD\Admin\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

namespace EDD\Admin\Orders;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Utils\Convert;

/**
 * Data localized for the order overview components.
 *
 * @since 3.7.3
 */
class Overview {

	/**
	 * Gets the admin URLs the order overview components link to.
	 *
	 * @since 3.7.3
	 *
	 * @return array
	 */
	public static function get_urls() {
		return array(
			'editDownload' => add_query_arg( array( 'action' => 'edit' ), admin_url( 'post.php' ) ),
			'editDiscount' => edd_get_admin_url(
				array(
					'page' => 'edd-discounts',
					'view' => 'edit_discount',
				)
			),
			'viewRefund'   => edd_get_admin_url(
				array(
					'page' => 'edd-payment-history',
					'view' => 'view-refund-details',
				)
			),
		);
	}

	/**
	 * Gets the strings the order overview components render.
	 *
	 * React escapes on render, so these are not escaped here.
	 *
	 * @since 3.7.3
	 *
	 * @return array
	 */
	public static function get_i18n() {
		$singular = edd_get_label_singular();

		return array(
			'downloadSingular'        => $singular,
			'unitPrice'               => __( 'Unit Price', 'easy-digital-downloads' ),
			'quantity'                => __( 'Quantity', 'easy-digital-downloads' ),
			'amount'                  => __( 'Amount', 'easy-digital-downloads' ),
			'subtotal'                => __( 'Subtotal', 'easy-digital-downloads' ),
			'tax'                     => __( 'Tax', 'easy-digital-downloads' ),
			'total'                   => __( 'Total', 'easy-digital-downloads' ),
			'noOrderItems'            => __( 'No order items', 'easy-digital-downloads' ),
			'removeItem'              => __( 'Remove item', 'easy-digital-downloads' ),
			'removeDiscount'          => __( 'Remove discount', 'easy-digital-downloads' ),
			/* translators: %s: Adjustment type (discount, fee, etc) */
			'removeAdjustment'        => __( 'Remove %s', 'easy-digital-downloads' ),
			'discount'                => __( 'Discount', 'easy-digital-downloads' ),
			'fee'                     => __( 'Fee', 'easy-digital-downloads' ),
			'credit'                  => __( 'Credit', 'easy-digital-downloads' ),
			'orderFee'                => __( 'Order Fee', 'easy-digital-downloads' ),
			'orderCredit'             => __( 'Order Credit', 'easy-digital-downloads' ),
			'refund'                  => __( 'Refund', 'easy-digital-downloads' ),
			/* translators: %s: Tax rate as a percentage */
			'taxRateLabel'            => __( 'Tax Rate: %s', 'easy-digital-downloads' ),
			/* translators: %1$s: Tax rate */
			'taxRateUpdated'          => __( 'The tax rate has been updated to %1$s. Existing automatically calculated amounts have not been updated.', 'easy-digital-downloads' ),
			'amountsNotRecalculated'  => __( 'The order amounts could not be updated. Try your last change again before saving the order.', 'easy-digital-downloads' ),
			'amountsUpdating'         => __( 'The order amounts are still updating. Save the order once they finish.', 'easy-digital-downloads' ),
			'updateAmounts'           => __( 'Update Amounts', 'easy-digital-downloads' ),
			'dismissNotice'           => __( 'Dismiss this notice.', 'easy-digital-downloads' ),
			'manualAdjustmentNote'    => __( '† Some amounts have been manually adjusted.', 'easy-digital-downloads' ),
			'showMoreDetails'         => __( 'Show more details', 'easy-digital-downloads' ),
			/* translators: %s: Download singular label */
			'copyDownloadLinks'       => sprintf( __( 'Copy %s Links', 'easy-digital-downloads' ), $singular ),
			'noFileLinks'             => __( 'No file links available', 'easy-digital-downloads' ),
			'addDownloadTitle'        => __( 'Add Item', 'easy-digital-downloads' ),
			/* translators: %s: Download singular label */
			'addDownload'             => sprintf( _x( 'Add %s', 'Add a download to an order', 'easy-digital-downloads' ), $singular ),
			'searchDownload'          => __( 'Search for a download', 'easy-digital-downloads' ),
			'autoCalculate'           => __( 'Automatically calculate amounts', 'easy-digital-downloads' ),
			'noTaxRate'               => __( 'No tax rate has been set.', 'easy-digital-downloads' ),
			'taxFromAddress'          => __( "Tax rates are defined by the customer's billing address.", 'easy-digital-downloads' ),
			'setAddress'              => __( 'Set an address', 'easy-digital-downloads' ),
			/* translators: %s: Download singular label */
			'duplicateDownload'       => sprintf( __( 'This %s already exists in the Order. Please remove it before adding it again.', 'easy-digital-downloads' ), $singular ),
			'addDiscount'             => __( 'Add Discount', 'easy-digital-downloads' ),
			'chooseDiscount'          => __( 'Choose a discount', 'easy-digital-downloads' ),
			'discountAlreadyApplied'  => __( 'This Discount already applied to the Order.', 'easy-digital-downloads' ),
			'addAdjustment'           => _x( 'Add Adjustment', 'Apply an adjustment to an order', 'easy-digital-downloads' ),
			'type'                    => __( 'Type', 'easy-digital-downloads' ),
			'applyTo'                 => __( 'Apply to', 'easy-digital-downloads' ),
			'entireOrder'             => __( 'Entire order', 'easy-digital-downloads' ),
			'applyTaxToFee'           => __( 'Apply tax to fee', 'easy-digital-downloads' ),
			'description'             => __( 'Description', 'easy-digital-downloads' ),
			'cannotModify'            => __( 'Order items cannot be modified.', 'easy-digital-downloads' ),
			'issueRefundTip'          => __( 'Issue a refund to adjust the net total for this order.', 'easy-digital-downloads' ),
			'refundWindowOverrideTip' => __( 'The refund window for this Order has passed; however, you have the ability to override this.', 'easy-digital-downloads' ),
			'refundWindowPassedTip'   => __( 'The refund window for this Order has passed.', 'easy-digital-downloads' ),
			'initializeRefund'        => __( 'Initialize Refund', 'easy-digital-downloads' ),
			'amazonLockTip'           => __( 'Amazon orders must be refunded at the gateway.', 'easy-digital-downloads' ),
			'amazonRefundNotice'      => __( 'Orders placed through the Amazon gateway must be refunded through Amazon. The order status can then be updated manually.', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Gets the discounts listed in the Add Discount dialog before the admin searches.
	 *
	 * Only discounts that can be applied now are listed, matching the dialog's search.
	 *
	 * @since 3.7.3
	 *
	 * @return array
	 */
	public static function get_initial_discounts() {
		if ( true !== edd_has_active_discounts() ) {
			return array();
		}

		$discounts = edd_get_discounts(
			array(
				'number'  => 25,
				'status'  => 'active',
				'orderby' => 'code',
				'order'   => 'ASC',
			)
		);
		if ( empty( $discounts ) ) {
			return array();
		}

		$valid_discounts = array_filter(
			$discounts,
			function ( $discount ) {
				return edd_validate_discount( $discount->id );
			}
		);

		return array_values(
			array_map(
				function ( $discount ) {
					return array(
						'id'   => (int) $discount->id,
						'code' => Convert::decode_entities( $discount->code ),
						'name' => Convert::decode_entities( $discount->name ),
					);
				},
				$valid_discounts
			)
		);
	}

	/**
	 * Gets the newest downloads listed in the Add Download dialog before the admin searches.
	 *
	 * A variable-priced download is listed once per price.
	 *
	 * @since 3.7.3
	 *
	 * @return array
	 */
	public static function get_initial_downloads() {
		$download_ids = get_posts(
			array(
				'fields'         => 'ids',
				'orderby'        => 'date',
				'order'          => 'DESC',
				'post_type'      => 'download',
				'posts_per_page' => 25,
				'post_status'    => array( 'publish', 'draft', 'private', 'future' ),
			)
		);

		$downloads = array();
		foreach ( $download_ids as $download_id ) {
			$prices = edd_get_variable_prices( $download_id );
			if ( empty( $prices ) ) {
				$downloads[] = array(
					'id'   => $download_id,
					'name' => Convert::decode_entities( edd_get_download_name( $download_id ) ),
				);
				continue;
			}

			foreach ( array_keys( $prices ) as $price_id ) {
				$name = edd_get_download_name( $download_id, $price_id );
				if ( ! empty( $name ) ) {
					$downloads[] = array(
						'id'   => $download_id . '_' . $price_id,
						'name' => Convert::decode_entities( $name ),
					);
				}
			}
		}

		return $downloads;
	}
}
