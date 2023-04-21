<?php

/**
 * Core class for adding event subscribers.
 *
 * @since 3.1.1
 * @package EDD
 */
namespace EDD\Lite;

class Core extends \EDD\Core {

	/**
	 * Gets the service providers for EDD.
	 *
	 * @return array
	 */
	protected function get_service_providers() {
		return array_merge( parent::get_service_providers(), $this->get_lite_providers() );
	}

	/**
	 * Gets the admin service providers.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	protected function get_admin_providers() {
		return array_merge( parent::get_admin_providers(), $this->get_lite_admin_providers() );
	}

	/**
	 * Gets the lite service providers.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	private function get_lite_providers() {
		return array(
			new Admin\PassHandler\Connect( $this->pass_handler ),
		);
	}

	/**
	 * Gets the lite admin providers.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	private function get_lite_admin_providers() {
		if ( ! is_admin() ) {
			return array();
		}

		return array(
			new Admin\Menu(),
			new Admin\PassHandler\Pointer(),
		);
	}
}
