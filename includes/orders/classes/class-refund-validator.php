<?php
/**
 * Refund Validator
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     3.0
 */

namespace EDD\Orders;

use EDD\Utils\Exception;
use EDD\Utils\Exceptions\Invalid_Argument;

class Refund_Validator {

	/**
	 * Original order being refunded.
	 *
	 * @var Order
	 */
	protected $order;

	/**
	 * All fees associated with the original order. Includes both order-level and order-item-level.
	 *
	 * @var Order_Adjustment[]
	 */
	protected $order_fees;

	/**
	 * Array of order item IDs and amounts to refund. If empty, then all items will be refunded.
	 *
	 * @var array
	 */
	protected $order_items_to_refund;

	/**
	 * Array of fee IDs and amounts to refund. If empty, then no fees are refunded.
	 *
	 * @var array
	 */
	protected $fees_to_refund;

	/**
	 * Final subtotal for the refund. Includes all selected order items and fees.
	 *
	 * @var float
	 */
	public $subtotal = 0.00;

	/**
	 * Final tax amount to refund. Includes all selected order items and fees.
	 *
	 * @var float
	 */
	public $tax = 0.00;

	/**
	 * Final total for the refund (subtotal + tax). Includes all selected order items and fees.
	 *
	 * @var float
	 */
	public $total = 0.00;

	/**
	 * Refund_Validator constructor.
	 *
	 * @param Order        $order
	 * @param array|string $order_items
	 * @param array|string $fees
	 *
	 * @throws \Exception
	 */
	public function __construct( Order $order, $order_items = 'all', $fees = 'all' ) {
		$this->order                 = $order;
		$this->order_fees            = $this->order->get_fees();
		$this->order_items_to_refund = $this->validate_and_format_order_items( $order_items );
		$this->fees_to_refund        = $this->validate_and_format_fees( $fees );
	}

	/**
	 * Validates the supplied order items and does a little formatting.
	 * If `all` is supplied, then all items eligible for refund are included.
	 *
	 * @param array|string $order_items
	 *
	 * @return array
	 * @throws Invalid_Argument
	 */
	private function validate_and_format_order_items( $order_items ) {
		$keyed_order_items = array();

		if ( 'all' === $order_items ) {
			$order_items = $this->get_all_refundable_order_items();
		}

		if ( ! empty( $order_items ) && is_array( $order_items ) ) {
			$order_item_ids = wp_list_pluck( $this->order->items, 'id' );

			foreach ( $order_items as $order_item_data ) {
				// order_item_id must be supplied and in the list attached to the original order.
				if ( empty( $order_item_data['order_item_id'] ) || ! in_array( $order_item_data['order_item_id'], $order_item_ids ) ) {
					throw Invalid_Argument::from( 'order_item_id', __METHOD__ );
				}

				$this->validate_required_fields( $order_item_data, __METHOD__ );

				/*
				 * For now we're going to assume the order item will be fully refunded. We'll adjust this later if it
				 * ends up being a partial refund.
				 */
				$order_item_data['status'] = 'refunded';

				// Set the array key to be the order item ID for easier lookups as we go.
				$keyed_order_items[ intval( $order_item_data['order_item_id'] ) ] = $order_item_data;
			}
		}

		return $keyed_order_items;
	}

	/**
	 * Returns an array of all order items that can be refunded.
	 * This is used if `all` is supplied for order items.
	 *
	 * @since 3.0
	 * @return array
	 */
	private function get_all_refundable_order_items() {
		$order_items_to_refund = array();

		foreach ( $this->order->items as $item ) {
			if ( 'refunded' !== $item->status ) {
				$order_items_to_refund[] = array_merge( array(
					'order_item_id' => $item->id
				), $item->get_refundable_amounts() );
			}
		}

		return $order_items_to_refund;
	}

	/**
	 * Validates the supplied fees and does a little formatting.
	 * If `all` is supplied, then all fees eligible for refund are included.
	 *
	 * @param array|string $fees
	 *
	 * @return array
	 * @throws Invalid_Argument
	 */
	private function validate_and_format_fees( $fees ) {
		$keyed_fees = array();

		if ( 'all' === $fees ) {
			$fees = $this->get_all_refundable_fees();
		}

		if ( ! empty( $fees ) && is_array( $fees ) ) {
			$fee_ids = wp_list_pluck( $this->order_fees, 'id' );

			foreach ( $fees as $fee_data ) {
				// fee_id must be supplied and in the list attached to the original order/items.
				if ( empty( $fee_data['fee_id'] ) || ! array_key_exists( $fee_data['fee_id'], $fee_ids ) ) {
					throw Invalid_Argument::from( 'fee_id', __METHOD__ );
				}

				$this->validate_required_fields( $fee_data, __METHOD__ );

				/*
				 * For now we're going to assume the fee will be fully refunded. We'll adjust this later if it
				 * ends up being a partial refund.
				 */
				$fee_data['status'] = 'refunded';

				// Set the array key to be the fee ID for easier lookups as we go.
				$keyed_fees[ intval( $fee_data['fee_id'] ) ] = $fee_data;
			}
		}

		return $keyed_fees;
	}

	/**
	 * Returns an array of all fees that can be refunded.
	 * This is used if `all` is supplied for fees.
	 *
	 * @since 3.0
	 * @return array
	 */
	private function get_all_refundable_fees() {
		$fees_to_refund = array();

		foreach ( $this->order_fees as $fee ) {
			if ( 'refunded' !== $fee->status ) {
				$fees_to_refund[] = array_merge( array(
					'fee_id' => $fee->id
				), $fee->get_refundable_amounts() );
			}
		}

		return $fees_to_refund;
	}

	/**
	 * Validates required fields for both order items and taxes.
	 *
	 * @param array  $input   Input to be validated.
	 * @param string $context Context, for error message.
	 *
	 * @since 3.0
	 * @throws Invalid_Argument
	 */
	private function validate_required_fields( $input, $context ) {
		// subtotal and total are both required.
		$required_fields = array( 'subtotal', 'total' );
		if ( edd_use_taxes() ) {
			$required_fields[] = 'tax';
		}

		foreach ( $required_fields as $required_field ) {
			if ( ! isset( $input[ $required_field ] ) ) {
				throw Invalid_Argument::from( $required_field, $context );
			}
		}
	}

	/**
	 * Validates final amounts and calculates refund total.
	 *
	 * @throws \Exception
	 */
	public function validate_and_calculate_totals() {
		$this->validate_order_item_amounts();
		$this->validate_fee_amounts();

		// Refund amount cannot be 0
		if ( $this->total <= 0 ) {
			throw new Exception( sprintf(
				/* Translators: %s - 0.00 formatted in store currency */
				__( 'The refund amount must be greater than %s.', 'easy-digital-downloads' ),
				edd_currency_filter( 0.00 )
			) );
		}

		// Overall refund total cannot be over total refundable amount.
		$order_total = edd_get_order_total( $this->order->id );
		if ( $this->total > $order_total ) {
			throw new Exception( sprintf(
				/* Translators: %s - maximum refund amount as formatted currency */
				__( 'The maximum refund amount is %s.', 'easy-digital-downloads' ),
				edd_currency_filter( $order_total )
			) );
		}
	}

	/**
	 * Validates the order item amounts.
	 *
	 * @throws \Exception
	 */
	private function validate_order_item_amounts() {
		foreach ( $this->order->items as $item ) {
			if ( ! array_key_exists( $item->id, $this->order_items_to_refund ) ) {
				continue;
			}

			$amount_to_refund = wp_parse_args( $this->order_items_to_refund[ $item->id ], array(
				'subtotal' => $item->subtotal,
				'tax'      => $item->tax,
				'total'    => $item->total
			) );

			$this->order_items_to_refund[ $item->id ]['original_item_status'] = $this->validate_item_and_add_totals( $item, $amount_to_refund );
		}
	}

	/**
	 * Validates the fee amounts.
	 *
	 * @throws \Exception
	 */
	private function validate_fee_amounts() {
		foreach ( $this->order_fees as $fee ) {
			if ( ! array_key_exists( $fee->id, $this->fees_to_refund ) ) {
				continue;
			}

			$amount_to_refund = wp_parse_args( $this->fees_to_refund[ $fee->id ], array(
				'subtotal' => $fee->subtotal,
				'tax'      => $fee->tax,
				'total'    => $fee->total
			) );

			$this->fees_to_refund[ $fee->id ]['original_item_status'] = $this->validate_item_and_add_totals( $fee, $amount_to_refund );
		}
	}

	/**
	 * Validates the amount attempting to be refunded against the total that can be refunded.
	 *
	 * The refund amount for each item cannot exceed the original amount minus what's already been refunded.
	 * Note: quantity is not checked because you might process multiple partial refunds for the same order item.
	 *
	 * @param Order_Item|Order_Adjustment $original_item     Original item being refunded.
	 * @param array                       $amounts_to_refund Amounts *attempting* to be refunded. These will be
	 *                                                       matched against the maximums.
	 *
	 * @return string Either `refunded` if this is a complete refund, or `partially_refunded` if it's a partial.
	 *                This should be the new status for the original item.
	 * @throws Exception
	 */
	private function validate_item_and_add_totals( $original_item, $amounts_to_refund ) {
		$item_status = 'refunded';

		$maximum_refundable_amounts = $original_item->get_refundable_amounts();

		foreach ( array( 'subtotal', 'tax', 'total' ) as $column_name ) {
			// Hopefully this should never happen, but just in case!
			if ( ! array_key_exists( $column_name, $maximum_refundable_amounts ) ) {
				throw new Exception( sprintf(
				/* Translators: %s is the type of amount being refunded (e.g. "subtotal" or "tax"). Not translatable at this time. */
					__( 'An unexpected error occurred while validating the maximum %s amount.', 'easy-digital-downloads' ),
					$column_name
				) );
			}

			// This is our fallback.
			$attempted_amount = isset( $original_item->{$column_name} ) ? $original_item->{$column_name} : 0.00;

			// But grab from specified amounts if available. It should always be available.
			if ( isset( $amounts_to_refund[ $column_name ] ) ) {
				$attempted_amount = $amounts_to_refund[ $column_name ];
			}

			if ( $attempted_amount > $maximum_refundable_amounts[ $column_name ] ) {
				throw new Exception( sprintf(
				/* Translators: %s - type of amount being refunded; %d - item ID number; %s - maximum amount allowed for refund. */
					__( 'The maximum refund %s for item #%d is %s.', 'easy-digital-downloads' ),
					$column_name,
					$original_item->id,
					edd_currency_filter( $maximum_refundable_amounts[ $column_name ] )
				) );
			}

			if ( 'total' === $column_name && $attempted_amount < $maximum_refundable_amounts['total'] ) {
				$item_status = 'partially_refunded';
			}

			$this->{$column_name} += $attempted_amount;
		}

		return $item_status;
	}

	/**
	 * Returns an array of order items to refund.
	 *
	 * @since 3.0
	 * @return array
	 */
	public function get_refunded_order_items() {
		$order_items = array();

		foreach ( $this->order->items as $item ) {
			if ( array_key_exists( $item->id, $this->order_items_to_refund ) ) {
				$order_items[] = $this->set_common_item_args( wp_parse_args( $this->order_items_to_refund[ $item->id ], $item->to_array() ) );
			}
		}

		return $order_items;
	}

	/**
	 * Returns an array of all fees to refund.
	 *
	 * @since 3.0
	 * @return array
	 */
	public function get_refunded_fees() {
		$order_item_fees = array();

		foreach ( $this->order_fees as $fee ) {
			if ( array_key_exists( $fee->id, $this->order_items_to_refund ) ) {
				$order_item_fees[] = $this->set_common_item_args( wp_parse_args( $this->order_items_to_refund[ $fee->id ], $fee->to_array() ) );
			}
		}

		return $order_item_fees;
	}

	/**
	 * Sets common arguments for refunded order items and fees.
	 *
	 * @param array $new_args
	 *
	 * @since 3.0
	 * @return array
	 */
	private function set_common_item_args( $new_args ) {
		// Set the `parent` to the original item ID.
		if ( isset( $new_args['id'] ) ) {
			$new_args['parent'] = $new_args['id'];
		}

		// Strip out the keys we don't want.
		$keys_to_remove = array( 'id', 'order_id', 'date_created', 'date_modified', 'uuid' );
		$new_args = array_diff_key( $new_args, array_flip( $keys_to_remove ) );

		// Status is always `complete`.
		$new_args['status'] = 'complete';

		return $new_args;
	}

}

