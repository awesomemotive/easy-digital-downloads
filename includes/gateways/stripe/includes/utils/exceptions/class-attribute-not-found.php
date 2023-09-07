<?php
/**
 * EDD_Stripe_Utils_Exceptions_Attribute_Not_Found exception class
 *
 * @package EDD_Stripe
 * @since   2.6.19
 */

/**
 * Implements an EDD_Stripe_Utils_Exceptions_Attribute_Not_Found exception thrown when a given
 * attribute is not found.
 *
 * @since 2.6.19
 *
 * @see OutOfBoundsException
 */
class EDD_Stripe_Utils_Exceptions_Attribute_Not_Found extends OutOfBoundsException {

	/**
	 * Retrieves an informed Attribute_Not_Found instance via late-static binding.
	 *
	 * @since 2.6.19
	 *
	 * @param string     $attribute_name Attribute resulting in the exception.
	 * @param string     $collection     Collection the attribute belongs to.
	 * @param int        $code           Optional. Exception code. Default null.
	 * @param Exception  $previous       Optional. Previous exception (used for chaining).
	 *                                   Default null.
	 * @return EDD_Stripe_Exceptions_Attribute_Not_Found Exception instance.
	 */
	public static function from_attr( $attribute_name, $collection, $code = null, $previous = null ) {
		$message = sprintf( 'The \'%1$s\' attribute does not exist for \'%2$s\'.',
			$attribute_name,
			$collection
		);

		return new static( $message, $code, $previous);
	}

}
