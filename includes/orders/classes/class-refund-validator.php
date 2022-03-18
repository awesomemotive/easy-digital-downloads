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
	 * All fees and credits associated with the original order. Includes both order-level and order-item-level.
	 *
	 * @var Order_Adjustment[]
	 */
	protected $order_adjustments;

	/**
	 * Array of order item IDs and amounts to refund. If empty, then all items will be refunded.
	 *
	 * @var array
	 */
	protected $order_items_to_refund;

	/**
	 * Array of adjustment IDs and amounts to refund. If empty, then no adjustments are refunded.
	 *
	 * @var array
	 */
	protected $adjustments_to_refund;

	/**
	 * Final subtotal for the refund. Includes all selected order items and adjustments.
	 *
	 * @var float
	 */
	public $subtotal = 0.00;

	/**
	 * Final tax amount to refund. Includes all selected order items and adjustments.
	 *
	 * @var float
	 */
	public $tax = 0.00;

	/**
	 * Final total for the refund (subtotal + tax). Includes all selected order items and adjustments.
	 *
	 * @var float
	 */
	public $total = 0.00;

	/**
	 * Refund_Validator constructor.
	 *
	 * @param Order        $order
	 * @param array|string $order_items
	 * @param array|string $adjustments
	 *
	 * @throws \Exception
	 */
	public function __construct( Order $order, $order_items = 'all', $adjustments = 'all' ) {
		$this->order                 = $order;
		$this->order_adjustments     = $this->get_order_adjustments();
		$this->order_items_to_refund = $this->validate_and_format_order_items( $order_items );
		$this->adjustments_to_refund = $this->validate_and_format_adjustments( $adjustments );
	}

	/**
	 * Returns all refund-eligible adjustments associated with the order.
	 * Note that this doesn't exclude items that have already reached their refund max; it just
	 * returns all objects that could possibly be refunded. (Essentially `discount` adjustments
	 * are excluded.)
	 *
	 * @since 3.0
	 * @return Order_Adjustment[]
	 */
	private function get_order_adjustments() {
		$fees    = $this->order->get_fees();
		$credits = edd_get_order_adjustments( array(
			'object_id'   => $this->order->id,
			'object_type' => 'order',
			'type'        => 'credit'
		) );

		return array_merge( $fees, $credits );
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

				if ( ! isset( $order_item_data['total'] ) ) {
					$order_item_data['total'] = $order_item_data['subtotal'] + $order_item_data['tax'];
				}

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
					'order_item_id' => $item->id,
					'quantity'      => $item->quantity,
				), $item->get_refundable_amounts() );
			}
		}

		return $order_items_to_refund;
	}

	/**
	 * Validates the supplied adjustments and does a little formatting.
	 * If `all` is supplied, then all adjustments eligible for refund are included.
	 *
	 * @param array|string $adjustments
	 *
	 * @return array
	 * @throws Invalid_Argument
	 */
	private function validate_and_format_adjustments( $adjustments ) {
		$keyed_adjustments = array();

		if ( 'all' === $adjustments ) {
			$adjustments = $this->get_all_refundable_adjustments();
		}

		if ( ! empty( $adjustments ) && is_array( $adjustments ) ) {
			$adjustment_ids = wp_list_pluck( $this->order_adjustments, 'id' );

			foreach ( $adjustments as $adjustment_data ) {
				// adjustment_id must be supplied and in the list attached to the original order/items.
				if ( empty( $adjustment_data['adjustment_id'] ) || ! in_array( $adjustment_data['adjustment_id'], $adjustment_ids ) ) {
					throw Invalid_Argument::from( 'adjustment_id', __METHOD__ );
				}

				$this->validate_required_fields( $adjustment_data, __METHOD__ );

				if ( ! isset( $adjustment_data['total'] ) ) {
					$adjustment_data['total'] = $adjustment_data['subtotal'] + $adjustment_data['tax'];
				}

				// Set the array key to be the adjustment ID for easier lookups as we go.
				$keyed_adjustments[ intval( $adjustment_data['adjustment_id'] ) ] = $adjustment_data;
			}
		}

		return $keyed_adjustments;
	}

	/**
	 * Returns an array of all adjustments that can be refunded.
	 * This is used if `all` is supplied for adjustments.
	 *
	 * @since 3.0
	 * @return array
	 */
	private function get_all_refundable_adjustments() {
		$adjustments_to_refund = array();

		foreach ( $this->order_adjustments as $adjustment ) {
			if ( 'refunded' !== $adjustment->status ) {
				$adjustments_to_refund[] = array_merge( array(
					'adjustment_id' => $adjustment->id
				), $adjustment->get_refundable_amounts() );
			}
		}

		return $adjustments_to_refund;
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
		$required_fields = array( 'subtotal' );
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
		$this->validate_adjustment_amounts();

		// Some items or adjustments have to be selected to refund.
		if ( empty( $this->order_items_to_refund ) && empty( $this->adjustments_to_refund ) ) {
			throw new Exception(
				__( 'No items have been selected to refund.', 'easy-digital-downloads' )
			);
		}

		// Refund amount cannot be 0
		if ( $this->total <= 0 ) {
			throw new Exception( sprintf(
				/* Translators: %s - 0.00 formatted in store currency */
				__( 'The refund amount must be greater than %s.', 'easy-digital-downloads' ),
				edd_currency_filter( edd_format_amount( 0.00 ) )
			) );
		}

		// Overall refund total cannot be over total refundable amount.
		$order_total = edd_get_order_total( $this->order->id );
		if ( $this->is_over_refund_amount( $this->total, $order_total ) ) {
			throw new Exception( sprintf(
				/* Translators: %s - maximum refund amount as formatted currency */
				__( 'The maximum refund amount is %s.', 'easy-digital-downloads' ),
				edd_currency_filter( edd_format_amount( $order_total ) )
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
	 * Validates the adjustment amounts.
	 *
	 * @throws \Exception
	 */
	private function validate_adjustment_amounts() {
		foreach ( $this->order_adjustments as $adjustment ) {
			if ( ! array_key_exists( $adjustment->id, $this->adjustments_to_refund ) ) {
				continue;
			}

			$amount_to_refund = wp_parse_args( $this->adjustments_to_refund[ $adjustment->id ], array(
				'subtotal' => $adjustment->subtotal,
				'tax'      => $adjustment->tax,
				'total'    => $adjustment->total
			) );

			$this->adjustments_to_refund[ $adjustment->id ]['original_item_status'] = $this->validate_item_and_add_totals( $adjustment, $amount_to_refund );
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
			$maximum_amount   = $maximum_refundable_amounts[ $column_name ];

			// Only order items are included in the subtotal.
			if ( ! $original_item instanceof Order_Item && 'subtotal' === $column_name ) {
				continue;
			}

			// But grab from specified amounts if available. It should always be available.
			if ( isset( $amounts_to_refund[ $column_name ] ) ) {
				$attempted_amount = $amounts_to_refund[ $column_name ];
			}

			if ( $this->is_over_refund_amount( $attempted_amount, $maximum_amount ) ) {
				if ( $original_item instanceof Order_Item ) {
					$error_message = sprintf(
						/*
						 * Translators:
						 * %1$s - type of amount being refunded (subtotal, tax, or total);
						 * %1$s - product name;
						 * %3$s - maximum amount allowed for refund
						 */
						__( 'The maximum refund %1$s for the product "%2$s" is %3$s.', 'easy-digital-downloads' ),
						$column_name,
						$original_item->product_name,
						edd_currency_filter( $maximum_refundable_amounts[ $column_name ] )
					);
				} else {
					$error_message = sprintf(
						/*
						 * Translators:
						 * %1$s - type of amount being refunded (subtotal, tax, or total);
						 * %1$s - adjustment description;
						 * %3$s - maximum amount allowed for refund
						 */
						__( 'The maximum refund %s for the adjustment "%s" is %s.', 'easy-digital-downloads' ),
						$column_name,
						$original_item->description,
						edd_currency_filter( $maximum_refundable_amounts[ $column_name ] )
					);
				}

				throw new Exception( $error_message );
			}

			if ( 'total' === $column_name && $attempted_amount < $maximum_refundable_amounts['total'] ) {
				$item_status = 'partially_refunded';
			}

			// If this is an adjustment, and it's _credit_, negate the amount because credit _reduces_ the total.
			if ( $original_item instanceof Order_Adjustment && 'credit' === $original_item->type ) {
				$attempted_amount = edd_negate_amount( $attempted_amount );
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
				$defaults = $allowed_keys = $item->to_array();

				if ( array_key_exists( 'original_item_status', $this->order_items_to_refund[ $item->id ] ) ) {
					$allowed_keys['original_item_status'] = $this->order_items_to_refund[ $item->id ]['original_item_status'];
				}

				$args          = array_intersect_key( $this->order_items_to_refund[ $item->id ], $allowed_keys );
				$order_items[] = $this->set_common_item_args( wp_parse_args( $args, $defaults ) );
			}
		}

		return $order_items;
	}

	/**
	 * Returns an array of all adjustments to refund.
	 *
	 * @since 3.0
	 * @return array
	 */
	public function get_refunded_adjustments() {
		$order_item_adjustments = array();

		foreach ( $this->order_adjustments as $adjustment ) {
			if ( array_key_exists( $adjustment->id, $this->adjustments_to_refund ) ) {
				$defaults = $allowed_keys = $adjustment->to_array();

				if ( array_key_exists( 'original_item_status', $this->adjustments_to_refund[ $adjustment->id ] ) ) {
					$allowed_keys['original_item_status'] = $this->adjustments_to_refund[ $adjustment->id ]['original_item_status'];
				}

				$args                     = array_intersect_key( $this->adjustments_to_refund[ $adjustment->id ], $defaults );
				$order_item_adjustments[] = $this->set_common_item_args( wp_parse_args( $args, $defaults ) );
			}
		}

		return $order_item_adjustments;
	}

	/**
	 * Sets common arguments for refunded order items and adjustments.
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

		// Negate amounts.
		if ( array_key_exists( 'quantity', $new_args ) ) {
			$new_args['quantity'] = edd_negate_int( $new_args['quantity'] );
		}
		foreach ( array( 'subtotal', 'tax', 'total' ) as $field_to_negate ) {
			if ( array_key_exists( $field_to_negate, $new_args ) ) {
				$new_args[ $field_to_negate ] = edd_negate_amount( $new_args[ $field_to_negate ] );
			}
		}

		// Strip out the keys we don't want.
		$keys_to_remove = array( 'id', 'order_id', 'discount', 'date_created', 'date_modified', 'uuid' );
		$new_args = array_diff_key( $new_args, array_flip( $keys_to_remove ) );

		// Status is always `complete`.
		$new_args['status'] = 'complete';

		return $new_args;
	}

	/**
	 * Checks if the attempted refund amount is over the maximum allowed refund amount.
	 *
	 * @since 3.0
	 * @param float $attempted_amount The amount to refund.
	 * @param float $maximum_amount   The maximum amount which can be refunded.
	 * @return boolean
	 */
	private function is_over_refund_amount( $attempted_amount, $maximum_amount ) {
		return edd_sanitize_amount( $attempted_amount ) > edd_sanitize_amount( $maximum_amount );
	}
}

