<?php
/**
 * Order helper for the Square integration.
 *
 * @package     EDD\Gateways\Square\Helpers
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Helpers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Gateways\Square\Helpers\Api;
use EDD\Gateways\Square\Helpers\Setting;
use EDD\Gateways\Square\Helpers\Currency;
use EDD\Vendor\Square\Models\Order as SquareOrder;
use EDD\Vendor\Square\Models\OrderSource;
use EDD\Vendor\Square\Models\CreateOrderRequest;
use EDD\Vendor\Square\Models\UpdateOrderRequest;
use EDD\Vendor\Square\Models\Money;
use EDD\Vendor\Square\Models\OrderLineItem;
use EDD\Vendor\Square\Models\OrderLineItemModifier;
use EDD\Vendor\Square\Models\OrderLineItemAppliedDiscount;
use EDD\Vendor\Square\Models\OrderLineItemDiscount;
use EDD\Vendor\Square\Models\OrderLineItemDiscountScope;
use EDD\Vendor\Square\Models\OrderLineItemAppliedTax;
use EDD\Vendor\Square\Models\OrderLineItemTax;
use EDD\Vendor\Square\Models\OrderLineItemTaxScope;
use EDD\Vendor\Square\Models\OrderLineItemTaxType;

/**
 * Order helper for the Square integration.
 *
 * @since 3.4.0
 */
class Order {

	/**
	 * The discounts.
	 *
	 * @var array
	 */
	private static $discounts = array();

	/**
	 * The line items.
	 *
	 * @var array
	 */
	private static $line_items = array();

	/**
	 * The taxes.
	 *
	 * @var array
	 */
	private static $taxes = array();

	/**
	 * The tax rate.
	 *
	 * @var float
	 */
	private static $tax_rate = 0.00;

	/**
	 * Build the order request.
	 *
	 * @since 3.4.0
	 * @param array $purchase_data The purchase data.
	 * @param array $args The arguments. Additional data for the order.
	 *
	 * @return CreateOrderRequest
	 */
	public static function build_order_request( $purchase_data, $args ) {
		/**
		 * Taxes and discounts for Square are two-sided.
		 *
		 * We have to add an array OrderLineItemDiscount and OrderLineItemTax objects to the order, with the details
		 * and a type of LINE_ITEM.
		 *
		 * Each Discount/Tax item also get's the unique ID of the line item association to the line item itself.
		 *
		 * We create detailed references at the order level for discounts and taxes,
		 * and then reference them in the line item with the unique ID.
		 */

		// Build any global negative fees as discounts. Actual discount codes are handled in the line items.
		self::$discounts = self::build_discounts( $purchase_data, $args );

		/**
		 * We build out tax data on line items, but we do need to store the tax rate for the order.
		 */
		if ( ! empty( $purchase_data['tax_rate'] ) ) {
			self::$tax_rate = $purchase_data['tax_rate'];
		}

		self::$line_items = self::build_line_items( $purchase_data, $args );

		$order_source = new OrderSource();
		$order_source->setName( 'EDD' );

		$order = new SquareOrder( Setting::get( 'location_id' ) );
		$order->setSource( $order_source );
		$order->setLineItems( self::$line_items );

		if ( ! empty( self::$discounts ) ) {
			$order->setDiscounts( self::$discounts );
		}

		if ( ! empty( self::$taxes ) ) {
			$order->setTaxes( self::$taxes );
		}

		$order->setCustomerId( $args['customer_id'] );
		$order->setMetadata( array( 'edd_version' => EDD_VERSION ) );

		$request = new CreateOrderRequest();
		$request->setOrder( $order );
		$request->setIdempotencyKey( Api::get_idempotency_key( 'square_create_order_' ) );

		/**
		 * Filter a create order request object.
		 *
		 * @since 3.4.0
		 *
		 * @param CreateOrderRequest $request Create order request object.
		 * @param array              $purchase_data Purchase data.
		 * @param array              $args          The additional arguments.
		 * @param string             $class         The class name.
		 */
		return apply_filters( 'edd_square_api_prepare_create_order_request', $request, $purchase_data, $args, __CLASS__ );
	}

	/**
	 * Get an order.
	 *
	 * @since 3.4.0
	 * @param string $order_id The order ID.
	 *
	 * @return SquareOrder|\Exception
	 * @throws \Exception Throws an exception if there is an error.
	 */
	public static function get_order( $order_id ) {
		$order = Api::client()->getOrdersApi()->retrieveOrder( $order_id );

		if ( ! $order->isSuccess() ) {
			foreach ( $order->getErrors() as $error ) {
				edd_debug_log( 'Square error: ' . $error->getDetail() );
				throw new \Exception( esc_html__( 'Error 1101: There was an error processing your order. Please try again.', 'easy-digital-downloads' ) );
			}
		}

		return $order->getResult()->getOrder();
	}

	/**
	 * Update an order.
	 *
	 * @since 3.4.0
	 * @param SquareOrder $order The order.
	 * @param array       $args  The arguments.
	 *
	 * @return SquareOrder|\Exception
	 * @throws \Exception Throws an exception if there is an error.
	 */
	public static function update_order( $order, $args ) {
		$updated_order = new SquareOrder( $order->getLocationId() );
		foreach ( $args as $key => $value ) {
			// Check if the key method exists, format of set<Key>, with ucfirst.
			$method = 'set' . ucfirst( $key );
			if ( method_exists( $order, $method ) ) {
				$updated_order->$method( $value );
			}
		}

		$request = new UpdateOrderRequest();
		$request->setIdempotencyKey( Api::get_idempotency_key( 'square_update_order_' ) );
		$request->setOrder( $updated_order );

		$response = Api::client()->getOrdersApi()->updateOrder( $order->getId(), $request );

		if ( ! $response->isSuccess() ) {
			foreach ( $response->getErrors() as $error ) {
				edd_debug_log( 'Square error: ' . $error->getDetail() );
				throw new \Exception( esc_html__( 'Error 1101: There was an error processing your order. Please try again.', 'easy-digital-downloads' ) );
			}
		}

		return $response->getResult()->getOrder();
	}

	/**
	 * Create an order.
	 *
	 * @since 3.4.0
	 * @param CreateOrderRequest $request The request.
	 *
	 * @return Order|\Exception
	 * @throws \Exception Throws an exception if there is an error.
	 */
	public static function create_order( $request ) {
		$response = Api::client()->getOrdersApi()->createOrder( $request );

		if ( ! $response->isSuccess() ) {
			foreach ( $response->getErrors() as $error ) {
				edd_debug_log( 'Square error: ' . $error->getDetail() );
			}

			throw new \Exception(
				sprintf(
					/* translators: %s is a reference code for the error, to help with customer support */
					__( 'There was an error creating your order record. Please try again, or contact support. Reference: %s', 'easy-digital-downloads' ),
					'SQ1003'
				)
			);
		}

		return $response->getResult()->getOrder();
	}

	/**
	 * Build the discounts.
	 *
	 * @since 3.4.0
	 * @param array $purchase_data The purchase data.
	 * @param array $args          The arguments.
	 *
	 * @return array
	 */
	private static function build_discounts( $purchase_data, $args ) {
		// Actual discounts are handled in the line items.
		$discounts = array();

		// We have to handle negative fees as discounts.
		foreach ( $purchase_data['fees'] as $key => $fee ) {
			if ( $fee['amount'] < 0 ) {
				if ( empty( $fee['download_id'] ) ) {
					$discount = new OrderLineItemDiscount();
					$discount->setUid( $key );
					$discount->setName( $fee['label'] );
					$discount->setScope( OrderLineItemDiscountScope::ORDER );

					$discount_amount = new Money();
					$discount_amount->setAmount(
						Currency::is_zero_decimal_currency( $args['currency'] ) ? round( $fee['amount'] ) : round( $fee['amount'] * 100 )
					);
					$discount->setAmountMoney( $discount_amount );

					$discounts[] = $discount;
				}
			}
		}

		return $discounts;
	}

	/**
	 * Build the line items.
	 *
	 * @since 3.4.0
	 * @param array $purchase_data The purchase data.
	 *
	 * @return array
	 */
	private static function build_line_items( $purchase_data, $args ) {
		$line_items = array();

		// Build the line items for the products.
		$key = 1;
		foreach ( (array) $purchase_data['cart_details'] as $item ) {
			$line_items[] = self::build_line_item( $key, $item, $args['currency'] );
			++$key;
		}

		// Now build the line items for the fee type of 'fee'.
		if ( ! empty( $purchase_data['fees'] ) ) {
			foreach ( $purchase_data['fees'] as $fee ) {
				// Skip if the fee is associated with a download.
				if ( ! empty( $fee['download_id'] ) ) {
					continue;
				}

				$line_items[] = self::build_fee_line_item( $key, $fee, $args['currency'] );
				++$key;
			}
		}

		return $line_items;
	}

	/**
	 * Build the line item.
	 *
	 * @since 3.4.0
	 * @param int    $key      The key.
	 * @param array  $item     The item.
	 * @param string $currency The currency.
	 *
	 * @return OrderLineItem
	 */
	private static function build_line_item( $key, $item, $currency ) {
		$amount           = Currency::is_zero_decimal_currency( $currency ) ? round( $item['item_price'] ) : round( $item['item_price'] * 100 );
		$base_price_money = new Money();
		$base_price_money->setAmount( $amount );
		$base_price_money->setCurrency( Currency::get_currency( $currency ) );

		$line_item = new OrderLineItem( $key );
		$line_item->setQuantity( $item['quantity'] );
		$line_item->setName( $item['name'] );
		$line_item->setBasePriceMoney( $base_price_money );

		if ( isset( $item['item_number']['options']['price_id'] ) ) {
			$line_item->setVariationName( edd_get_price_option_name( $item['id'], $item['item_number']['options']['price_id'] ) );
		}

		// Add Item Discounts.
		$applied_discounts = array();

		if ( ! empty( $item['applied_discounts'] ) ) {
			foreach ( $item['applied_discounts'] as $code => $applied_discount_amount ) {
				$discount = edd_get_discount_by_code( $code );
				if ( ! $discount ) {
					continue;
				}

				$applied_discount_hash = $code . $item['item_number']['hash'];
				$applied_discounts[]   = new OrderLineItemAppliedDiscount( $applied_discount_hash );

				$line_item_discount = new OrderLineItemDiscount();
				$line_item_discount->setUid( $applied_discount_hash );
				$line_item_discount->setName( $discount->get_name() );
				$line_item_discount->setScope( OrderLineItemDiscountScope::LINE_ITEM );

				$discount_amount = new Money();
				$discount_amount->setAmount(
					Currency::is_zero_decimal_currency( $currency ) ? round( $applied_discount_amount ) : round( $applied_discount_amount * 100 )
				);
				$discount_amount->setCurrency( Currency::get_currency( $currency ) );
				$line_item_discount->setAmountMoney( $discount_amount );

				self::$discounts[] = $line_item_discount;
			}
		}

		// Add Item Fees.
		$modifications = array();

		if ( ! empty( $item['fees'] ) ) {
			foreach ( $item['fees'] as $key => $fee ) {
				if ( $fee['download_id'] === $item['id'] ) {
					// If the fee amount is negative we have to treat it like a discount.
					if ( $fee['amount'] < 0 ) {
						$applied_discounts[] = new OrderLineItemAppliedDiscount( $key );

						$line_item_discount = new OrderLineItemDiscount();
						$line_item_discount->setUid( $key );
						$line_item_discount->setName( $fee['label'] );
						$line_item_discount->setScope( OrderLineItemDiscountScope::LINE_ITEM );

						$discount_amount = new Money();
						$discount_amount->setAmount(
							// Square expects discounts to be a positive amount.
							absint( Currency::is_zero_decimal_currency( $currency ) ? round( $fee['amount'] ) : round( $fee['amount'] * 100 ) )
						);
						$discount_amount->setCurrency( Currency::get_currency( $currency ) );
						$line_item_discount->setAmountMoney( $discount_amount );

						self::$discounts[] = $line_item_discount;
					} else {
						// Handle positive fees.
						$modification = new OrderLineItemModifier();
						$modification->setUid( $key );
						$modification->setName( $fee['label'] );

						$amount              = Currency::is_zero_decimal_currency( $currency ) ? round( $fee['amount'] ) : round( $fee['amount'] * 100 );
						$modification_amount = new Money();
						$modification_amount->setAmount( $amount );
						$modification_amount->setCurrency( Currency::get_currency( $currency ) );

						$modification->setBasePriceMoney( $modification_amount );

						$modifications[] = $modification;
					}
				}
			}
		}

		// Add Item Taxes.
		$applied_taxes = array();

		if ( ! empty( $item['tax'] ) ) {
			$applied_taxes[] = new OrderLineItemAppliedTax( 'tax_rate_' . $item['item_number']['hash'] );

			$line_item_tax = new OrderLineItemTax();
			$line_item_tax->setUid( 'tax_rate_' . $item['item_number']['hash'] );
			$line_item_tax->setType( OrderLineItemTaxType::ADDITIVE );
			$line_item_tax->setName( 'Tax' );
			$line_item_tax->setPercentage( self::$tax_rate * 100 );
			$line_item_tax->setScope( OrderLineItemTaxScope::LINE_ITEM );

			$tax_amount = new Money();
			$tax_amount->setAmount(
				Currency::is_zero_decimal_currency( $currency ) ? round( $item['tax'] ) : round( $item['tax'] * 100 )
			);
			$tax_amount->setCurrency( Currency::get_currency( $currency ) );
			$line_item_tax->setAppliedMoney( $tax_amount );

			self::$taxes[] = $line_item_tax;
		}

		if ( ! empty( $applied_discounts ) ) {
			$line_item->setAppliedDiscounts( $applied_discounts );
		}

		if ( ! empty( $modifications ) ) {
			$line_item->setModifiers( $modifications );
		}

		if ( ! empty( $applied_taxes ) ) {
			$line_item->setAppliedTaxes( $applied_taxes );
		}

		return $line_item;
	}

	/**
	 * Build the fee line item.
	 *
	 * @since 3.4.0
	 * @param int    $key      The key.
	 * @param array  $item     The item.
	 * @param string $currency The currency.
	 *
	 * @return OrderLineItem
	 */
	private static function build_fee_line_item( $key, $item, $currency ) {
		$amount           = Currency::is_zero_decimal_currency( $currency ) ? round( $item['amount'] ) : round( $item['amount'] * 100 );
		$base_price_money = new Money();
		$base_price_money->setAmount( $amount );
		$base_price_money->setCurrency( Currency::get_currency( $currency ) );

		$line_item = new OrderLineItem( $key );
		$line_item->setName( $item['label'] );
		$line_item->setQuantity( 1 );
		$line_item->setBasePriceMoney( $base_price_money );

		// If the `no_tax` flag is `false`, we need to add a tax to the line item.
		if ( ! $item['no_tax'] ) {
			$applied_taxes[] = new OrderLineItemAppliedTax( 'tax_rate_' . $key );

			$line_item_tax = new OrderLineItemTax();
			$line_item_tax->setUid( 'tax_rate_' . $key );
			$line_item_tax->setType( OrderLineItemTaxType::ADDITIVE );
			$line_item_tax->setName( 'Tax' );
			$line_item_tax->setPercentage( self::$tax_rate * 100 );
			$line_item_tax->setScope( OrderLineItemTaxScope::LINE_ITEM );

			$fee_tax_amount = edd_calculate_tax( $item['amount'], '', '', true, self::$tax_rate );
			$tax_amount     = new Money();
			$tax_amount->setAmount(
				Currency::is_zero_decimal_currency( $currency ) ? round( $fee_tax_amount ) : round( $fee_tax_amount * 100 )
			);
			$tax_amount->setCurrency( Currency::get_currency( $currency ) );
			$line_item_tax->setAppliedMoney( $tax_amount );

			self::$taxes[] = $line_item_tax;

			$line_item->setAppliedTaxes( $applied_taxes );
		}

		return $line_item;
	}
}
