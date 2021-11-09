<?php
/**
 * CollectsAccountInformation.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\Checkout\Traits;

trait CollectsAccountInformation {

	/**
	 * Retrieves account information from an array.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected function getNewAccountInformation( array $data ) {
		return [
			'user_login'   => isset( $data['edd_user_login'] )
				? preg_replace( '/\s+/', '', sanitize_user( $data['edd_user_login'], false ) )
				: false,
			'user_email'   => isset( $data['edd_email'] ) ? sanitize_email( $data['edd_email'] ) : false,
			'first_name'   => isset( $data['edd_first'] ) ? sanitize_text_field( $data['edd_first'] ) : '',
			'last_name'    => isset( $data['edd_last'] ) ? sanitize_text_field( $data['edd_last'] ) : '',
			'user_pass'    => isset( $data['edd_user_pass'] ) ? trim( $data['edd_user_pass'] ) : false,
			'pass_confirm' => isset( $data['edd_user_pass_confirm'] ) ? trim( $data['edd_user_pass_confirm'] ) : false,
		];
	}

	/**
	 * Determines whether or not this is a guest checkout.
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	protected function isGuestCheckout( array $data ) {
		return empty( $data['edd_user_login'] );
	}

}
