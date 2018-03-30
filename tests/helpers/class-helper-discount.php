<?php

/**
 * Class EDD_Helper_Discount.
 *
 * Helper class to create and delete a discount easily.
 */
class EDD_Helper_Discount extends WP_UnitTestCase {

	/**
	 * Delete a discount.
	 *
	 * @since 2.3
	 *
	 * @param int $discount_id ID of the discount to delete.
	 */
	public static function delete_discount( $discount_id ) {
		edd_delete_discount( $discount_id );
	}

	/**
	 * Create a simple percentage discount.
	 *
	 * @since 2.3
	 */
	public static function create_simple_percent_discount() {
		return edd_add_discount( array(
			'name'              => '20 Percent Off',
			'code'              => '20OFF',
			'status'            => 'active',
			'type'              => 'percent',
			'amount'            => '20',
			'use_count'         => 54,
			'max_uses'          => 10,
			'min_cart_price'    => 128,
			'product_condition' => 'all',
			'start_date'        => '12/12/2010 00:00:00',
			'end_date'          => '12/31/2050 23:59:59'
		) );
	}

	/**
	 * Create a simple negative percentage discount.
	 *
	 * @since 2.3
	 */
	public static function create_simple_negative_percent_discount() {
		return edd_add_discount( array(
			'name'              => 'Double Double',
			'code'              => 'DOUBLE',
			'status'            => 'active',
			'type'              => 'percent',
			'amount'            => '-100',
			'use_count'         => 54,
			'max_uses'          => 10,
			'min_cart_price'    => 128,
			'product_condition' => 'all',
			'start_date'        => '12/12/2010 00:00:00',
			'end_date'          => '12/31/2050 23:59:59'
		) );
	}

	/**
	 * Create a simple flat discount.
	 *
	 * @since 2.3
	 */
	public static function create_simple_flat_discount() {
		return edd_add_discount( array(
			'name'              => '$10 Off',
			'code'              => '10FLAT',
			'type'              => 'flat',
			'status'            => 'active',
			'amount'            => '10',
			'max_uses'          => 10,
			'use_count'         => 0,
			'min_cart_price'    => 128,
			'product_condition' => 'all',
			'start_date'        => '12/12/2010 00:00:00',
			'end_date'          => '12/31/2050 23:59:59'
		) );
	}

	/**
	 * Create an expired flat discount.
	 *
	 * @since 3.0
	 */
	public static function created_expired_flat_discount() {
		return edd_add_discount( array(
			'name'              => '$20 Off',
			'code'              => '20FLAT',
			'type'              => 'flat',
			'status'            => 'expired',
			'amount'            => '20',
			'max_uses'          => 20,
			'use_count'         => 0,
			'min_cart_price'    => 128,
			'product_condition' => 'all',
			'start_date'        => '12/12/2010 00:00:00',
			'end_date'          => '12/31/2012 23:59:59'
		) );
	}

	/**
	 * Create legacy discount code.
	 *
	 * @since 3.0
	 */
	public static function create_legacy_discount() {

		$discount_id = wp_insert_post( array(
			'post_type'   => 'edd_discount',
			'post_title'  => 'Legacy Discount',
			'post_status' => 'active'
		) );

		$meta = array(
			'code'              => 'OLD',
			'status'            => 'active',
			'uses'              => 10,
			'max_uses'          => 20,
			'amount'            => 20,
			'start'             => '01/01/2000 00:00:00',
			'expiration'        => '12/31/2050 23:59:59',
			'type'              => 'percent',
			'min_price'         => '10.50',
			'product_reqs'      => array( 57 ),
			'product_condition' => 'all',
			'excluded_products' => array( 75 ),
			'is_not_global'     => true,
			'is_single_use'     => true
		);

		remove_filter( 'add_post_metadata', '_edd_discount_update_meta_backcompat', 99 );

		foreach( $meta as $key => $value ) {
			add_post_meta( $discount_id, '_edd_discount_' . $key, $value );
		}

		add_filter( 'add_post_metadata', '_edd_discount_update_meta_backcompat', 99, 5 );

		return $discount_id;
	}
}
