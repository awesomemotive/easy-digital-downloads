<?php
/**
 * Discount Validator
 *
 * @package     EDD
 * @subpackage  Classes/Discount
 * @copyright   Copyright (c) 2017, Sunny Ratilal
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.8.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EDD_Discount_Validator Class.
 *
 * @since 2.8.7
 */
class EDD_Discount_Validator {

	/**
	 * Discount object
	 *
	 * @var EDD_Discount
	 */
	private $discount;

	/**
	 * Download IDs.
	 *
	 * @var array
	 */
	private $downloads;

	/**
	 * EDD_Discount_Validator constructor.
	 *
	 * @param EDD_Discount $discount  Discount object.
	 * @param array        $downloads Download IDs.
	 */
	public function __construct( $discount = null, $downloads = array() ) {
		$this->discount  = $discount;
		$this->downloads = $downloads;
	}

	/**
	 * Parent method that calls other validation methods.
	 *
	 * @since 2.8.7
	 */
	public function is_valid() {
		if ( ! $this->discount instanceof EDD_Discount ) {
			return new WP_Error( 'invalid-arg', __( 'Discount object not valid.', 'easy-digital-downloads' ) );
		}
	}
}