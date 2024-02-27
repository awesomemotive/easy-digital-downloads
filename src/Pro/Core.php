<?php

/**
 * Base class for adding pro event subscribers.
 *
 * @package EDD
 */
namespace EDD\Pro;

class Core extends \EDD\Core {

	/**
	 * Gets the service providers for EDD.
	 *
	 * @return array
	 */
	protected function get_service_providers() {
		return array_merge( parent::get_service_providers(), $this->get_pro_providers() );
	}

	/**
	 * Gets the admin service providers.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	protected function get_admin_providers() {
		return $this->get_pro_admin_providers();
	}

	/**
	 * Gets providers that may be extended/replaced in lite/pro.
	 *
	 * @return array
	 */
	protected function get_replaceable_providers() {
		$replaced_providers = array(
			'Admin\Extensions\Legacy'   => new Admin\Extensions\Legacy(),
			'Admin\Promos\PromoHandler' => new Admin\Promos\PromoHandler(),
		);

		if ( edd_is_inactive_pro() ) {
			$replaced_providers['Admin\Discounts\Generate'] = new Inactive\Admin\Discounts\Generate();
		} else {
			$replaced_providers['Admin\Discounts\Generate'] = new Admin\Discounts\Generate();
		}

		/**
		 * Gets the replaceable provider class names.
		 *
		 * We can't just call the parent::get_replaceable_providers() method because
		 * those will actually instantiate new instances of the ones we want to replace, and trigger
		 * any hooks/filters.
		 */
		$registered_replaceable_providers = $this->get_replaceable_core_provider_classes();

		// Iterate over the registered replaceable providers.
		foreach ( $registered_replaceable_providers as $replceable_provider => $provider_class_name ) {
			// If a registered replaceable provider isn't replaced here, instantiate it from the registered core class.
			if ( ! array_key_exists( $replceable_provider, $replaced_providers ) ) {
				$replaced_providers[ $replceable_provider ] = new $provider_class_name();
			}
		}

		return $replaced_providers;
	}

	/**
	 * Gets the pro providers.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	private function get_pro_providers() {
		return array(
			new Admin\PassHandler\Ajax( $this->pass_handler ),
			new Checkout\GeoIP(),
			new Licensing\Update(),
			new Translations\Translate(),
		);
	}

	/**
	 * Gets the pro admin providers.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	private function get_pro_admin_providers() {
		if ( ! is_admin() ) {
			return array();
		}
		$providers = parent::get_admin_providers();

		return array_merge(
			$providers,
			array(
				new Admin\Duplicator\Controls(),
				new Admin\Duplicator\Worker(),
				new Admin\Settings\Handler(),
			)
		);
	}
}
