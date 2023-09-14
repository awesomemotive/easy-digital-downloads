<?php

namespace EDD\Tests\Helpers;

class Licenses {

	public static function get_pass_license_data( $args = array() ) {
		$license_data = wp_parse_args(
			$args,
			array(
				'success'          => true,
				'license'          => 'valid',
				'item_id'          => 1783595,
				'item_name'        => '',
				'license_limit'    => 1,
				'site_count'       => 2,
				'expires'          => 'lifetime',
				'activations_left' => 'unlimited',
				'payment_id'       => 7642331,
				'customer_name'    => 'John Doe',
				'customer_email'   => 'john@edd.local',
				'price_id'         => 0,
				'pass_id'          => 1464807,
			)
		);

		return (object) $license_data;
	}

	public static function get_pro_license( $args = array() ) {
		$license_key = 'daksjfg98q3kjhJ3K4Q2354';
		update_site_option( 'edd_pro_license_key', $license_key );

		$pass_handler = new \EDD\Admin\PassHandler\Handler();
		$pass_handler->update_pro_license( self::get_pass_license_data( $args ) );

		return $pass_handler->get_pro_license();
	}

	public static function delete_pro_license() {
		delete_site_option( 'edd_pro_license_key' );
		delete_site_option( 'edd_pro_license' );
	}

	public static function get_stripe_license_data( $args = array() ) {
		$license_data = wp_parse_args(
			$args,
			array(
				'success'          => true,
				'license'          => 'valid',
				'item_id'          => 167,
				'item_name'        => 'Stripe Pro Payment Gateway',
				'license_limit'    => 1,
				'site_count'       => 1,
				'expires'          => 'lifetime',
				'activations_left' => 0,
				'payment_id'       => 7642331,
				'customer_name'    => 'John Doe',
				'customer_email'   => 'john@edd.local',
				'price_id'         => 1,
			)
		);

		return (object) $license_data;
	}

	public static function get_stripe_license( $args = array() ) {
		$product_name = 'Stripe Pro Payment Gateway';
		$license_key  = 'bgvear89p7ty4qbrjkc4';

		edd_update_option( self::get_stripe_option_name(), $license_key );
		$license = new \EDD\Licensing\License( $product_name );
		$license->save( self::get_stripe_license_data( $args ) );

		return $license->get();
	}

	public static function delete_stripe_license() {
		edd_delete_option( self::get_stripe_option_name() );
		delete_option( 'edd_stripe_pro_payment_gateway_license_active' );
	}

	private static function get_stripe_option_name() {
		$product_name = 'Stripe Pro Payment Gateway';
		$shortname    = 'edd_' . preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $product_name ) ) );

		return "{$shortname}_license_key";
	}
}
