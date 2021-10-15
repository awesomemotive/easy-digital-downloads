<?php
/**
 * Checkout.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\API\v3;

use EDD\Checkout\CheckoutProcessor;
use EDD\Checkout\Config;
use EDD\Checkout\Exceptions\ValidationException;
use EDD\Checkout\Validator;

class Checkout extends Endpoint {

	/**
	 * @var Validator
	 */
	protected $validator;

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @var CheckoutProcessor
	 */
	protected $checkoutProcessor;

	public function __construct( Validator $validator, CheckoutProcessor $checkoutProcessor ) {
		$this->validator         = $validator;
		$this->config            = new Config();
		$this->checkoutProcessor = $checkoutProcessor;
	}

	/**
	 * Registers the checkout endpoints.
	 */
	public function register() {
		register_rest_route(
			self::$namespace,
			'checkout/validate',
			[
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'handleValidateCheckout' ],
				'permission_callback' => '__return_true',
			]
		);

		register_rest_route(
			self::$namespace,
			'checkout',
			[
				'methods'             => [ \WP_REST_Server::CREATABLE, \WP_REST_Server::READABLE ],
				'callback'            => [ $this, 'handleCheckout' ],
				'permission_callback' => '__return_true',
			]
		);
	}

	/**
	 * Handles checkout validation.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function handleValidateCheckout( \WP_REST_Request $request ) {
		try {
			$this->validator->validate( $this->config, $request->get_params() );

			return new \WP_REST_Response( [], 200 );
		} catch ( ValidationException $e ) {
			return new \WP_REST_Response(
				[
					'message' => __( 'Validation failed.', 'easy-digital-downloads' ),
					'errors'  => $e->getErrorCollection()->toArray(),
				],
				$e->getCode()
			);
		}
	}

	public function handleCheckout( \WP_REST_Request $request ) {
		if ( 'GET' === $request->get_method() ) {
			return $this->getCheckoutState( $request );
		} else {
			return $this->processCheckout( $request );
		}
	}

	protected function getCheckoutState( \WP_REST_Request $request ) {
		return new \WP_REST_Request(
			$this->checkoutProcessor->setData( $request->get_params() )->getOrderFromSession()->toArray()
		);
	}

	protected function processCheckout( \WP_REST_Request $request ) {
		return new \WP_REST_Request();
	}
}
