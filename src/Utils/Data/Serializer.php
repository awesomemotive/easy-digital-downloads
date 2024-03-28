<?php

namespace EDD\Utils\Data;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Serializer class.
 *
 * @since 3.2.10
 */
class Serializer {

	/**
	 * Unserialize data.
	 *
	 * @since 3.2.10
	 * @param string $data Serialized data.
	 * @return false|array|object
	 */
	public static function maybe_unserialize( $data ) {

		$unserialized_data = maybe_unserialize( $data );
		if ( false === $unserialized_data ) {
			$fixed_data        = self::fix_possible_serialization( $data );
			$unserialized_data = self::unserialize( $fixed_data );

			// If the data is still not unserialized, log the error.
			if ( false === $unserialized_data && ! empty( $data ) ) {
				edd_debug_log( 'Failed to unserialize data: ' . $data, true );
			}
		}

		return $unserialized_data;
	}

	/**
	 * Attempt to fix corrupted serialized data.
	 *
	 * @since 3.2.10 (Copied from data migrator class)
	 * @param mixed $data Data to fix.
	 * @return mixed
	 */
	public static function fix_possible_serialization( $data ) {
		if ( ! is_array( $data ) && is_string( $data ) ) {
			$data = substr_replace( $data, 'a', 0, 1 );
		}

		return $data;
	}

	/**
	 * Unserialize data.
	 *
	 * @since 3.2.10
	 * @param string $data Serialized data.
	 * @return mixed
	 */
	public static function unserialize( $data ) {
		if ( is_serialized( $data ) ) {
			return @unserialize( trim( $data ), array( 'allowed_classes' => false ) );
		}

		return $data;
	}

	/**
	 * Fixes corrupted serialized data.
	 *
	 * @since 3.2.10
	 * @param string $data Serialized data.
	 * @return string
	 */
	public static function fix_corrupted_serialized_data( $data ) {
		return preg_replace_callback(
			'!s:\d+:"(.*?)";!s',
			function ( $m ) {
				return 's:' . mb_strlen( $m[1] ) . ':"' . $m[1] . '";';
			},
			$data
		);
	}
}
