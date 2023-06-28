<?php

/**
 * Core class for adding event subscribers.
 *
 * @since 3.1.1
 * @package EDD
 */
namespace EDD;

class Core extends EventManagement\Subscribers {

	/**
	 * Gets the service providers for EDD.
	 *
	 * @return array
	 */
	protected function get_service_providers() {
		return array(
			new Admin\PassHandler\Ajax( $this->pass_handler ),
			new Admin\Extensions\Extension_Manager(),
			new Customers\Recalculations(),
			new Admin\PassHandler\Cron(),
		);
	}

	/**
	 * Gets the admin service providers.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	protected function get_admin_providers() {
		if ( ! is_admin() ) {
			return array();
		}

		return array(
			new Admin\PassHandler\Settings( $this->pass_handler ),
			new Admin\PassHandler\Actions( $this->pass_handler ),
			new Admin\Extensions\Menu(),
			new Admin\Settings\EmailMarketing(),
			new Admin\Settings\Invoices(),
			new Admin\Settings\Recurring(),
			new Admin\Settings\Reviews(),
			new Admin\Settings\WP_SMTP(),
			new Admin\Downloads\Meta(),
			new Admin\Onboarding\Tools(),
			new Admin\Onboarding\Wizard(),
			new Admin\Onboarding\Ajax(),
			new Licensing\Ajax(),
			new Admin\SiteHealth\Tests(),
			new Admin\SiteHealth\Information(),
		);
	}

	/**
	 * Gets providers that may be extended/replaced in lite/pro.
	 *
	 * @return array
	 */
	protected function get_replaceable_providers() {
		return array(
			new Admin\Extensions\Legacy(),
			new Admin\Promos\PromoHandler(),
		);
	}
}
