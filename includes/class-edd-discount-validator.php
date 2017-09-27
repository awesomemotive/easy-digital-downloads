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
	 * User info.
	 *
	 * @var mixed string|int
	 */
	private $user;

	/**
	 * Price.
	 *
	 * @var float
	 */
	private $price;

	/**
	 * EDD_Discount_Validator constructor.
	 *
	 * @param int|EDD_Discount $discount  Discount object or discount ID.
	 * @param array            $downloads Download IDs.
	 * @param string           $user      User info.
	 * @param float            $price     Price for minimum amount validation.
	 */
	public function __construct( $discount = 0, $downloads = array(), $user = null, $price = null ) {
		$this->discount  = $discount;
		$this->downloads = $downloads;
		$this->user      = $user;
		$this->price     = (float) $price;

		$this->setup_object();
	}

	/**
	 * Setup discount object.
	 *
	 * @access private
	 */
	private function setup_object() {
		if ( ! $this->discount instanceof EDD_Discount ) {
			$this->discount = new EDD_Discount( $this->discount );
		}

		if ( null === $this->user ){
			$user = get_user_by( 'ID', get_current_user_id() );
			if ( $user ){
				$this->user = $user->user_email;
			}
		}

		if ( ! is_email( $this->user ) ){
			$this->user = null;
		}
	}

	/**
	 * Parent method that calls other validation methods.
	 *
	 * @access public
	 * @since 2.8.7
	 *
	 * @return bool $validity If the discount is valid or not.
	 */
	public function is_valid() {
		if ( empty( $this->downloads ) ) {
			return new WP_Error( 'invalid-arg', __( 'Download IDs not supplied.', 'easy-digital-downloads' ) );
		}

		$product_requirements = $this->discount->get_product_reqs();
		$excluded_products    = $this->discount->get_excluded_products();

		// Initially the discount is invalid
		$validity = false;

		// If there are no requirements/excluded products set, the discount is valid
		if ( empty( $product_requirements ) && empty( $excluded_products ) ) {
			$validity = true;
		}

		$product_requirements = array_map( 'absint', $product_requirements );
		asort( $product_requirements );
		$product_requirements = array_values( $product_requirements );

		$excluded_products = array_map( 'absint', $excluded_products );
		asort( $excluded_products );
		$excluded_products = array_values( $excluded_products );

		if ( ! $validity && ! empty( $product_requirements ) ) {
			switch ( $this->discount->get_product_condition() ) {
				case 'all' :
					$validity = true;
					foreach ( $product_requirements as $download_id ) {
						if ( ! in_array( $download_id, $this->downloads ) ) {
							$validity = false;
							break;
						}
					}
					break;
				default:
					foreach ( $product_requirements as $download_id ) {
						if ( in_array( $download_id, $this->downloads ) ) {
							$validity = true;
							break;
						}
					}
					break;
			}
		} else {
			$validity = true;
		}

		if ( ! empty( $excluded_products ) ) {
			foreach ( $excluded_products as $download_id ) {
				if ( in_array( $download_id, $this->downloads ) ) {
					$validity = false;
				}
			}
		}

		return $validity;
	}
}