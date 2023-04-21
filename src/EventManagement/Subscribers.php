<?php
/**
 * Class to handle registering and adding service providers for EDD.
 *
 * @since 3.1.1
 * @package EDD
 */
namespace EDD\EventManagement;

abstract class Subscribers {

	/**
	 * The pass handler.
	 *
	 * @since 3.1.1
	 * @var EDD\Admin\PassHandler\Handler
	 */
	protected $pass_handler;

	public function __construct() {
		$this->pass_handler = new \EDD\Admin\PassHandler\Handler();
		$this->add_service_providers();
	}

	/**
	 * Add registered service providers.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function add_service_providers() {
		$events = new EventManager();

		if ( ! $events instanceof EventManager ) {
			return;
		}

		$service_providers = array_merge(
			$this->get_service_providers(),
			$this->get_admin_providers(),
			$this->get_replaceable_providers()
		);

		// Attach subscribers.
		foreach ( $service_providers as $service_provider ) {
			try {
				$events->add_subscriber( $service_provider );
			} catch ( Exception $e ) {
				// Do not subscribe.
			}
		}
	}

	/**
	 * Gets providers that may be extended/replaced in lite/pro.
	 *
	 * @return array
	 */
	protected function get_replaceable_providers() {
		return array();
	}

	/**
	 * Gets the service providers for EDD.
	 *
	 * @return array
	 */
	abstract protected function get_service_providers();

	/**
	 * Gets the admin service providers for EDD.
	 *
	 * @return array
	 */
	abstract protected function get_admin_providers();
}
