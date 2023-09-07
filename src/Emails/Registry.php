<?php
/**
 * The email registry.
 *
 * This holds email IDs and their associated classes for the new email system introduced in EDD 3.2.0
 *
 * @since 3.2.0
 * @package EDD
 * @subpackage Emails
 */

namespace EDD\Emails;

use Exception;

final class Registry {

	/**
	 * Holds the registered emails.
	 * @since 3.2.0
	 * @var array
	 */
	private static $registered_emails = array();

	/**
	 * Registry constructor.
	 * @since 3.2.0
	 *
	 * @param string $id    The email to register. This must be a unique ID.
	 * @param string $class The fully qualified class to use for this email.
	 *
	 * @throws Exception
	 */
	public static function register( $id = '', $class = '' ) {
		try {
			// An ID and a class are required.
			if ( empty( $id ) || empty( $class ) ) {
				throw new Exception( __( 'An email ID and class must be provided.', 'easy-digital-downloads' ) );
			}

			// Verify that this ID is not already registered.
			if ( self::is_registered( $id ) ) {
				throw new Exception( __( 'The email ID provided is already registered.', 'easy-digital-downloads' ) );
			}

			// Verify that the class exists, and that it extends the EDD\Emails\Types\Email class.
			if ( ! class_exists( $class ) || ! is_subclass_of( $class, 'EDD\Emails\Types\Email' ) ) {
				throw new Exception( __( 'The email class must exist and extend the EDD\Emails\Types\Email class.', 'easy-digital-downloads' ) );
			}

			// Add the email to the registry.
			self::$registered_emails[ $id ] = $class;
		} catch( \Exception $e ) {
			wp_die( $e->getMessage() );
		}
	}

	/**
	 * Determine if an ID is regsitered.
	 *
	 * @since 3.2.0
	 * @param string $id The email ID to check.
	 * @return bool
	 */
	public static function is_registered( $id = '' ) {
		return isset( self::$registered_emails[ $id ] );
	}

	/**
	 * Returns the class for a given email ID.
	 * @since 3.2.0
	 * @param string $id The email ID to get the class for.
	 */
	public static function get( $id = '', $arguments = array() ) {
		try{
			if ( ! self::is_registered( $id ) ) {
				throw new Exception( __( 'The email ID provided is not registered.', 'easy-digital-downloads' ) );
			}

			$class      = self::$registered_emails[ $id ];

			// Now get the number of arguments needed.
			$reflection         = new \ReflectionClass( $class );
			$constructor        = $reflection->getConstructor();
			$required_arguments = $constructor->getNumberOfParameters();

			if ( count( $arguments ) !== $required_arguments ) {
				throw new Exception(
					sprintf(
						__( 'The number of arguments provided (%d) does not match the number of arguments required (%d) for %s.', 'easy-digital-downloads' ),
						count( $arguments ),
						$required_arguments,
						$class
					)
				);
			}

			return new $class( ...$arguments );
		} catch( \Exception $e ) {
			wp_die( $e->getMessage() );
		}
	}
}
