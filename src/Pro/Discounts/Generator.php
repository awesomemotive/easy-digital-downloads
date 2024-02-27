<?php

namespace EDD\Pro\Discounts;

class Generator {

	/**
	 * Letters to use for generating discount codes.
	 *
	 * @var string
	 * @since 3.2.0
	 */
	private static $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

	/**
	 * Numbers to use for generating discount codes.
	 *
	 * @var string
	 * @since 3.2.0
	 */
	private static $numbers = '0123456789';

	/**
	 * Maximum number of attempts to generate a unique discount code.
	 *
	 * @var int
	 * @since 3.2.0
	 */
	private static $max_attempts = 100;

	/**
	 * Generate a discount code with given type and length
	 *
	 * @since 3.2.0
	 *
	 * @param string $prefix Prefix to prepend to code
	 * @param string $type   Type of code to generate. 'hash', 'letters', or 'numbers'
	 * @param int    $limit  Length of code to generate
	 *
	 * @return string $code Generated code
	 */
	public static function generate( $prefix = '', $type = 'hash', $limit = 6 ) {
		// Store if the code exists or not. Default to 'true' so we try once.
		$code_exists = true;

		// Make sure the limit is at least 6 characters.
		$limit = max( 6, $limit );

		// limit the characters to max 50 as we save only 50 characters in the database
		$total_length = strlen( $prefix ) + $limit;
		if ( $total_length > 50 ) {
			$limit = 50 - strlen( $prefix );
		}

		do {

			if ( 'hash' === $type ) {
				// Generate a salt.
				$salt = md5( time() . wp_rand() );
				if ( strlen( $salt ) < $limit ) {
					$salt .= md5( wp_rand() . time() );
				}

				// Return the portion of the salt of the length requested and uppercase it.
				$code = strtoupper( substr( $salt, 1, $limit ) );
			} else {
				if ( 'letters' === $type ) {
					$characters = self::$letters;
				} else {
					$characters = self::$numbers;
				}

				$code = '';

				for ( $i = 0; $i < $limit; $i++ ) {
					$code .= $characters[ wp_rand( 0, strlen( $characters ) - 1 ) ];
				}
			}

			$generated_code = empty( $prefix ) ? $code : $prefix . $code;
			$code_exists    = self::check_if_code_exists( $generated_code );

		} while ( true === $code_exists && self::$max_attempts-- > 0 );

		/**
		 * If we've tried the max number of times, and still can't find a unique code, return false.
		 *
		 * After the max attempts the $code_exists will be true, so we need to check that.
		 */
		if ( true === $code_exists ) {
			$generated_code = false;
		}

		return $generated_code;
	}

	/**
	 * Check if a discount code already exists for this generated code.
	 *
	 * @since 3.2.0
	 *
	 * @param string $code Discount code to check
	 *
	 * @return bool $code_exists Whether or not the code exists
	 */
	private static function check_if_code_exists( $code ) {
		$code_exists = edd_get_discount_id_by_code( $code );
		return $code_exists instanceof \EDD_Discount ? true : false;
	}
}
