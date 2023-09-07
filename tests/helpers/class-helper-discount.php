<?php
namespace EDD\Tests\Helpers;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Class EDD_Helper_Discount.
 *
 * Helper class to create and delete a discount easily.
 */
class EDD_Helper_Discount extends EDD_UnitTestCase {

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
	public static function create_simple_percent_discount( $args = array() ) {
		return edd_add_discount(
			wp_parse_args(
				$args,
				array(
					'name'              => '20 Percent Off',
					'code'              => '20OFF',
					'status'            => 'active',
					'type'              => 'percent',
					'amount'            => '20',
					'use_count'         => 0,
					'max_uses'          => 10,
					'min_charge_amount' => 128,
					'product_condition' => 'all',
					'start_date'        => date( 'm/d/Y', time() ) . ' 00:00:00',
					'end_date'          => date( 'm/d/Y', time() ) . ' 23:59:59',
				)
			)
		);
	}

	/**
	 * Create a simple percentage discount.
	 *
	 * @since 2.3
	 */
	public static function create_simple_percent_discount_nodates_nouses( $args = array() ) {
		return edd_add_discount(
			wp_parse_args(
				$args,
				array(
					'name'              => '20 Percent Off NO DATES NO USES',
					'code'              => '20OFF',
					'status'            => 'active',
					'type'              => 'percent',
					'amount'            => '20',
					'use_count'         => 0,
					'max_uses'          => 0,
					'min_charge_amount' => 0,
					'product_condition' => 'all',
				)
			)
		);
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
			'min_charge_amount' => 128,
			'product_condition' => 'all',
			'start_date'        => '2010-12-12 00:00:00',
			'end_date'          => '2050-12-31 23:59:59'
		) );
	}

	/**
	 * Create a simple flat discount.
	 *
	 * @since 2.3
	 */
	public static function create_simple_flat_discount( $args = array() ) {
		return edd_add_discount(
			wp_parse_args(
				$args,
				array(
					'name'              => '$10 Off',
					'code'              => '10FLAT',
					'type'              => 'flat',
					'status'            => 'active',
					'amount'            => '10',
					'max_uses'          => 10,
					'use_count'         => 0,
					'min_charge_amount' => 128,
					'product_condition' => 'all',
					'start_date'        => date( 'm/d/Y', time() ) . ' 00:00:00',
					'end_date'          => date( 'm/d/Y', time() ) . ' 23:59:59',
				)
			)
		);
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
			'min_charge_amount' => 128,
			'product_condition' => 'all',
			'start_date'        => '2010-12-12 00:00:00',
			'end_date'          => '2050-12-31 23:59:59'
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
			'start'             => '2000-01-01 00:00:00',
			'expiration'        => '2051-12-31 23:59:59',
			'type'              => 'percent',
			'min_price'         => '10.50',
			'product_reqs'      => array( 57 ),
			'product_condition' => 'all',
			'excluded_products' => array( 75 ),
			'is_not_global'     => true,
			'is_single_use'     => true
		);

		remove_filter( 'add_post_metadata', array( 'EDD\Compat\Discount', 'update_post_metadata' ), 99 );

		foreach( $meta as $key => $value ) {
			add_post_meta( $discount_id, '_edd_discount_' . $key, $value );
		}

		$compat = new EDD\Compat\Discount();

		add_filter( 'add_post_metadata', array( $compat, 'update_post_metadata' ), 99, 5 );

		return $discount_id;
	}
}
