<?php

namespace EDD\Discounts;

defined( 'ABSPATH' ) || exit;

/**
 * Class ItemAmount.
 *
 * @since 3.2.0
 */
class ItemAmount {

	/**
	 * The cart item.
	 *
	 * @since 3.2.0
	 * @var array
	 */
	private $item;

	/**
	 * The cart items.
	 *
	 * @since 3.2.0
	 * @var array
	 */
	private $items;

	/**
	 * The discounts.
	 *
	 * @since 3.2.0
	 * @var array
	 */
	private $discounts;

	/**
	 * The item unit price.
	 *
	 * @since 3.2.0
	 * @var float
	 */
	private $item_unit_price;

	/**
	 * ItemAmount constructor.
	 *
	 * @since 3.2.0
	 *
	 * @param array $item            The cart item.
	 * @param array $items           The cart items.
	 * @param array $discounts       The discounts.
	 * @param float $item_unit_price The item unit price.
	 */
	public function __construct( $item, $items, $discounts, $item_unit_price = false ) {
		$this->items           = array_map( array( $this, 'normalize_item' ), $items );
		$this->item            = $this->normalize_item( $item );
		$this->discounts       = $this->get_discounts( $discounts );
		$this->item_unit_price = $this->get_item_unit_price( $item_unit_price );
	}

	/**
	 * Get the discount amount.
	 *
	 * @since 3.2.0
	 *
	 * @param bool $per_discount Whether to return a breakdown of discount amounts per discount code.
	 * @return float|array
	 */
	public function get_discount_amount( $per_discount = false ) {
		global $edd_flat_discount_total;

		// If we are getting the discount amount per discount, we need to return an array of discounts and applied amounts.
		$itemized_discounts = array();

		// Return early if the item is not valid.
		if ( empty( $this->item ) || empty( $this->item['id'] ) || empty( $this->item['quantity'] ) ) {
			if ( $per_discount ) {
				return array(
					'amount'    => 0,
					'discounts' => array(),
				);
			}

			return 0;
		}

		// If there are no discounts, return 0.
		if ( empty( $this->discounts ) ) {
			if ( $per_discount ) {
				return array(
					'amount'    => 0,
					'discounts' => array(),
				);
			}
			return 0;
		}

		$item_amount     = ( $this->item_unit_price * $this->item['quantity'] );
		$discount_amount = 0;

		foreach ( $this->discounts as $discount ) {

			// Make sure the discount is not excluded from this item.
			$excluded_products = array_map( 'intval', $discount->get_excluded_products() );
			if ( in_array( $this->item['id'], $excluded_products, true ) ) {
				continue;
			}

			// Get the product requirements.
			$product_requirements = $discount->get_product_reqs();
			if ( ! empty( $product_requirements ) ) {
				$processed = false;

				// This is a product(s) specific discount.
				foreach ( $product_requirements as $requirement ) {

					$parsed_requirement = edd_parse_product_dropdown_value( $requirement );
					if ( $parsed_requirement['download_id'] === $this->item['id'] ) {

						$price_id = isset( $this->item['options']['price_id'] ) && is_numeric( $this->item['options']['price_id'] ) ? absint( $this->item['options']['price_id'] ) : null;

						// If there is no price ID on the requirement, or the requirement price ID matches the item's price ID, apply the discount.
						if ( is_null( $parsed_requirement['price_id'] ) || $parsed_requirement['price_id'] === $price_id ) {
							$discount_amount += ( $item_amount - $discount->get_discounted_amount( $item_amount ) );
							$processed        = true;

							// Store the discount amount for the item.
							$itemized_discounts[ $discount->get_code() ] = edd_format_amount(
								$discount->get_applied_discount_amount( $item_amount ),
								true,
								'',
								'data'
							);

							// Break the requirements loop since the discount is applied for the current cart item.
							break;
						}
					}
				}

				// Discount calculation is done for this discount, so continue to the next discount.
				if ( $processed || 'global' !== $discount->get_scope() ) {
					continue;
				}
			}

			// Check the category requirements.
			if ( ! $discount->is_valid_for_categories( false, array( $this->item['id'] ) ) ) {
				continue;
			}

			// This is a global cart discount.

			// Get the discount amount for a percentage discount.
			if ( 'flat' !== $discount->get_type() ) {
				$discount_amount += ( $item_amount - $discount->get_discounted_amount( $item_amount ) );

				// Store the discount amount for the item.
				$itemized_discounts[ $discount->get_code() ] = edd_format_amount(
					$discount->get_applied_discount_amount( $item_amount ),
					true,
					'',
					'data'
				);

				continue;
			}

			// Get the discount amount for a flat discount.
			$items_amount     = $this->get_items_amount( $excluded_products );
			$subtotal_percent = ! empty( $items_amount ) ? ( $item_amount / $items_amount ) : 0;

			$item_discount = $discount->get_amount() * $subtotal_percent;

			// Make adjustments on the last item.
			if ( $this->is_last_item() ) {
				$other_items_discount = $this->get_other_items_discount( $items_amount, $discount, $excluded_products );
				$adjustment           = $discount->get_amount() - ( $item_discount + $other_items_discount );
				$item_discount       += $adjustment;
			}

			$discount_amount += $item_discount;

			// Store the discount amount for the item.
			$itemized_discounts[ $discount->get_code() ] = edd_format_amount( $item_discount, true, '', 'data' );

			// Make sure the discount amount doesn't exceed the item amount.
			if ( $discount_amount > $item_amount ) {
				$discount_amount = $item_amount;
			}

			// Add the discount amount to the global flat discount total.
			$edd_flat_discount_total += $discount_amount;
		}

		$discount_amount = edd_format_amount( $discount_amount, true, '', 'data' );

		if ( $per_discount ) {
			// Make sure that the total of the itemized discounts is not greater than the total discount amount.
			$total_itemized_discounts = array_sum( $itemized_discounts );

			/**
			 * If the total of the itemized discounts is greater than the total discount amount,
			 * We need to adjust the 'last' itemized discount to make sure the sum is equal to the total discount amount.
			 */
			if ( $total_itemized_discounts > $discount_amount ) {
				// Get the last discount key.
				$last_discount_key = array_key_last( $itemized_discounts );

				// Unset the last discount.
				unset( $itemized_discounts[ $last_discount_key ] );

				// Recalculate the total of the itemized discounts.
				$total_itemized_discounts = array_sum( $itemized_discounts );

				// Now re-add the last discount key with the difference between the total discount amount and the total of the itemized discounts.
				$itemized_discounts[ $last_discount_key ] = edd_format_amount( $discount_amount - $total_itemized_discounts, true, '', 'data' );
			}

			return array(
				'amount'    => $discount_amount,
				'discounts' => $itemized_discounts,
			);
		}

		return $discount_amount;
	}

	/**
	 * Normalize the item.
	 *
	 * @since 3.2.0
	 * @param array $item The item.
	 * @return array
	 */
	private function normalize_item( $item ) {

		if ( empty( $item['id'] ) ) {
			return array();
		}

		if ( ! isset( $item['options'] ) ) {
			$item['options'] = array();

			/*
			 * Support for variable pricing when the `item_number` key is set (cart details).
			 */
			if ( isset( $item['item_number']['options'] ) ) {
				$item['options'] = $item['item_number']['options'];
			}
		}

		// Get the hash from the item number.
		if ( isset( $item['item_number']['hash'] ) ) {
			$item['hash'] = $item['item_number']['hash'];
		}

		// Generate a hash if one is not set.
		if ( ! isset( $item['hash'] ) ) {
			$item['hash'] = md5( serialize( $item ) . time() . wp_rand( 0, 1000 ) );
		}

		// Cast the product ID to an integer.
		$item['id'] = absint( $item['id'] );

		return $item;
	}

	/**
	 * Normalize the discounts.
	 *
	 * @since 3.2.0
	 *
	 * @param array $discounts The discounts.
	 * @return array
	 */
	private function get_discounts( $discounts ) {
		// Validate and normalize Discounts.
		$discounts = array_map(
			function ( $discount ) {
				// Convert a Discount code to a Discount object.
				if ( is_string( $discount ) ) {
					$discount = edd_get_discount_by_code( $discount );
				}

				if ( ! $discount instanceof \EDD_Discount ) {
					return false;
				}

				return $discount;
			},
			$discounts
		);

		return array_filter( $discounts );
	}

	/**
	 * Get the item unit price.
	 *
	 * @since 3.2.0
	 *
	 * @param float $item_unit_price The item unit price.
	 * @param array $item            The item.
	 * @return float
	 */
	private function get_item_unit_price( $item_unit_price, $item = false ) {
		if ( false !== $item_unit_price ) {
			return $item_unit_price;
		}

		if ( false === $item ) {
			$item = $this->item;
		}

		if ( empty( $item['id'] ) ) {
			return 0;
		}

		// Determine the price of the item.
		if ( edd_has_variable_prices( $item['id'] ) ) {
			// Mimics the original behavior of `\EDD_Cart::get_item_amount()` that
			// does not fallback to the first Price ID if none is provided.
			if ( ! isset( $item['options']['price_id'] ) ) {
				return 0;
			}

			return edd_get_price_option_amount( $item['id'], $item['options']['price_id'] );
		}

		return edd_get_download_price( $item['id'] );
	}

	/**
	 * Get the items amount. In order to correctly record individual item amounts, global flat rate discounts
	 * are distributed across all items.
	 * The discount amount is divided by the number of items in the cart and then a portion is evenly
	 * applied to each item.
	 *
	 * @since 3.2.0
	 *
	 * @param array $excluded_products The excluded products.
	 * @return float
	 */
	private function get_items_amount( $excluded_products ) {
		$items_amount = 0;

		foreach ( $this->items as $key => $i ) {

			$i = $this->normalize_item( $i );
			if ( in_array( $i['id'], $excluded_products, true ) ) {
				continue;
			}

			$i_amount = $this->get_item_unit_price( false, $i );

			$this->items[ $key ]['amount'] = ( $i_amount * $i['quantity'] );
			$items_amount                 += $this->items[ $key ]['amount'];
		}

		return $items_amount;
	}

	/**
	 * Check if the item is the last item.
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	private function is_last_item() {
		$last_item = end( $this->items );
		if ( ! empty( $this->item['hash'] ) && ! empty( $last_item['hash'] ) ) {
			if ( hash_equals( $this->item['hash'], $last_item['hash'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the discount for all items except the last.
	 *
	 * @since 3.2.0
	 *
	 * @param float         $items_amount      The items amount.
	 * @param \EDD_Discount $discount           The discount.
	 * @param array         $excluded_products The excluded products.
	 * @return float
	 */
	private function get_other_items_discount( $items_amount, $discount, $excluded_products ) {
		return array_reduce(
			$this->items,
			function ( $carry, $_item ) use ( $items_amount, $discount, $excluded_products ) {

				$percent = 0;
				// Calculate percent only if current item is not same as loop item and not in excluded products.
				if ( ! hash_equals( $this->item['hash'], $_item['hash'] ) && ! empty( $items_amount ) && ! in_array( $_item['id'], $excluded_products, true ) ) {
					$percent = $_item['amount'] / $items_amount;
				}
				$value = edd_format_amount( $discount->get_amount() * $percent, true, '', 'data' );

				return $carry + $value;
			},
			0
		);
	}
}
