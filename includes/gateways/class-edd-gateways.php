<?php
/**
 * EDD Gateways Handler.
 *
 * @package     EDD
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2017, Sunny Ratilal
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.7
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Gateways Class.
 *
 * @since   2.7
 * @version 1.0
 */
class EDD_Gateways {
	/**
	 * Registered Gateways.
	 *
	 * @access public
	 * @since  2.7
	 * @var    array
	 */
	public $available_gateways = array();

	/**
	 * Enabled Gateways.
	 *
	 * @access public
	 * @since  2.7
	 * @var    array
	 */
	public $enabled_gateways = array();

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since  2.7
	 */
	public function __construct() {
		$this->available_gateways = $this->get_gateways();
		$this->enabled_gateways   = $this->get_enabled_gateways();
	}

	/**
	 * Retrieve a gateway by its ID.
	 *
	 * @access public
	 * @since  2.7
	 *
	 * @param string $id Gateway ID.
	 * @return mixed array|false Array of gateway details, false otherwise.
	 */
	public function get_gateway( $id  '' ) {
		if ( empty( $id ) ) {
			return false;
		}

		if ( isset( $this->available_gateways[ $key ] ) ) {
			return $this->available_gateways[ $id ];
		}

		return false;
	}

	/**
	 * Retrieve all the registered gateways.
	 *
	 * @access private
	 * @since  2.7
	 *
	 * @return array Registered gateways.
	 */
	public function get_gateways() {
		// Default, built-in gateways
		$gateways = array(
			'paypal' => array(
				'admin_label'    => __( 'PayPal Standard', 'easy-digital-downloads' ),
				'checkout_label' => __( 'PayPal', 'easy-digital-downloads' ),
				'supports'       => array( 'buy_now' ),
				'class'          => 'EDD_Gateway_PayPal'
			),
			'manual' => array(
				'admin_label'    => __( 'Test Payment', 'easy-digital-downloads' ),
				'checkout_label' => __( 'Test Payment', 'easy-digital-downloads' ),
				'class'          => 'EDD_Gateway_Manual'
			),
		);

		/**
		 * Filter the available payment gateways.
		 *
		 * @since 2.7
		 *
		 * @param array $gateways Registered payment gateways.
		 */
		return apply_filters( 'edd_payment_gateways', $gateways );
	}

	/**
	 * Retrieve the enabled payment gateways.
	 *
	 * @access private
	 * @since  2.7
	 *
	 * @return array Enabled gateways.
	 */
	private function get_enabled_gateways() {
		$gateways = $this->get_gateways();
		$enabled  = (array) edd_get_option( 'gateways', false );

		$gateway_list = array();

		foreach ( $gateways as $key => $gateway ) {
			if ( isset( $enabled[ $key ] ) && $enabled[ $key ] == 1 ) {
				$gateway_list[ $key ] = $gateway;
			}
		}

		if ( true === $sort ) {
			// Reorder our gateways so the default is first
			$default_gateway_id = edd_get_default_gateway();

			if ( edd_is_gateway_active( $default_gateway_id ) ) {
				$default_gateway = array( $default_gateway_id => $gateway_list[ $default_gateway_id ] );
				unset( $gateway_list[ $default_gateway_id ] );

				$gateway_list = array_merge( $default_gateway, $gateway_list );
			}
		}

		/**
		 * Filter the enabled payment gateways.
		 *
		 * @since 2.7
		 *
		 * @param array $gateways Enabled gateways.
		 */
		return apply_filters( 'edd_enabled_payment_gateways', $gateways );
	}

	/**
	 * Determine if a gateway is enabled
	 *
	 * @access public
	 * @since  2.7
	 *
	 * @param string $id Gateway ID.
	 * @return bool Is the gateway enabled?
	 */
	public function is_gateway_enabled( $id = '' ) {
		return isset( $this->enabled_gateways[ $id ] );
	}
}