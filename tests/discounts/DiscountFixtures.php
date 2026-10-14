<?php
/**
 * Discount fixtures shared by discount and gateway tests.
 *
 * @package     EDD\Tests\Discounts
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.2
 */

namespace EDD\Tests\Discounts;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Builds discounts and orders that carry them.
 *
 * @since 3.7.2
 */
trait DiscountFixtures {

	/**
	 * Empties the claims cache, which outlives each test while order IDs repeat across test classes.
	 *
	 * @return void
	 */
	protected function forget_cached_claims() {
		$held = new \ReflectionProperty( \EDD\Discounts\Claims::class, 'held' );
		$held->setAccessible( true );
		$held->setValue( null, array() );
	}

	/**
	 * Creates an active percentage discount.
	 *
	 * @param array $args Overrides.
	 * @return \EDD_Discount
	 */
	protected function create_discount( $args = array() ) {
		return static::edd()->discount->create_and_get(
			wp_parse_args(
				$args,
				array(
					'name'              => 'Ninety Off',
					'code'              => 'NINETY' . wp_rand(),
					'status'            => 'active',
					'type'              => 'percent',
					'amount'            => '90',
					'product_condition' => 'all',
					'start_date'        => '2010-01-01 00:00:00',
					'end_date'          => '2050-12-31 23:59:59',
				)
			)
		);
	}

	/**
	 * Creates a discount whose only use is already taken.
	 *
	 * @return \EDD_Discount
	 */
	protected function create_used_up_discount() {
		return $this->create_discount(
			array(
				'max_uses'  => 1,
				'use_count' => 1,
			)
		);
	}

	/**
	 * Creates an order carrying a discount adjustment, without claiming a use of it.
	 *
	 * @param \EDD_Discount $discount The discount.
	 * @param array         $args     Order arguments.
	 * @return \EDD\Orders\Order
	 */
	protected function create_order_with_discount( $discount, $args = array() ) {
		$order_id = static::edd()->order->create( $args );
		$this->add_discount_adjustment( $order_id, $discount );

		$order = edd_get_order( $order_id );
		$this->assertCount( 1, $order->get_discounts() );

		return $order;
	}

	/**
	 * Gets the order data a gateway passes to `edd_build_order()` for a one-item checkout.
	 *
	 * @param \EDD_Discount|null $discount The discount to apply, if any.
	 * @param string             $email    The buyer's email address.
	 * @param string             $gateway  The gateway.
	 * @return array
	 */
	protected function get_checkout_order_data( $discount, $email, $gateway ) {
		return array(
			'price'        => 10.0,
			'user_email'   => $email,
			'purchase_key' => md5( $gateway . wp_rand() ),
			'currency'     => 'USD',
			'downloads'    => array(
				array(
					'id'       => 1,
					'quantity' => 1,
				),
			),
			'user_info'    => array(
				'id'         => 0,
				'email'      => $email,
				'first_name' => 'Claim',
				'last_name'  => 'Tester',
				'discount'   => $discount ? $discount->code : 'none',
				'address'    => array(),
			),
			'cart_details' => array(
				array(
					'name'        => 'Test Download',
					'id'          => 1,
					'item_number' => array(
						'id'       => 1,
						'quantity' => 1,
					),
					'item_price'  => 20.0,
					'quantity'    => 1,
					'discount'    => 10.0,
					'subtotal'    => 20.0,
					'tax'         => 0.0,
					'fees'        => array(),
					'price'       => 10.0,
				),
			),
			'gateway'      => $gateway,
			'status'       => 'pending',
		);
	}

	/**
	 * Gets the most recent order placed with an email address.
	 *
	 * @param string $email The buyer's email address.
	 * @return \EDD\Orders\Order|null
	 */
	protected function get_latest_order_for( $email ) {
		$orders = edd_get_orders(
			array(
				'email'   => $email,
				'number'  => 1,
				'orderby' => 'id',
				'order'   => 'DESC',
			)
		);

		return $orders ? $orders[0] : null;
	}

	/**
	 * Completes an order the way a store admin does from the order screen.
	 *
	 * @param int $order_id The order ID.
	 * @return void
	 */
	protected function complete_as_store_admin( $order_id ) {
		global $current_screen;

		$original_screen  = $current_screen;
		$original_user_id = get_current_user_id();
		wp_set_current_user( static::factory()->user->create( array( 'role' => 'administrator' ) ) );
		set_current_screen( 'dashboard' );

		edd_update_order_status( $order_id, 'complete' );

		$current_screen = $original_screen; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		wp_set_current_user( $original_user_id );
	}

	/**
	 * Adds a discount adjustment to an order.
	 *
	 * @param int           $order_id The order ID.
	 * @param \EDD_Discount $discount The discount.
	 * @return void
	 */
	protected function add_discount_adjustment( $order_id, $discount ) {
		static::edd()->order_adjustment->create(
			array(
				'object_id'   => $order_id,
				'object_type' => 'order',
				'type_id'     => $discount->id,
				'type'        => 'discount',
				'description' => $discount->code,
				'subtotal'    => 90,
				'tax'         => 0,
				'total'       => 90,
			)
		);
	}
}
