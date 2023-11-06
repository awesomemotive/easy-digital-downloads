<?php
/**
 * Core class for adding event subscribers.
 *
 * @since 3.1.1
 * @package EDD
 */

namespace EDD;

defined( 'ABSPATH' ) || exit;

/**
 * Class Core
 *
 * @since 3.1.1
 * @package EDD
 */
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
			new Downloads\Services(),
			new Orders\DeferredActions(),
			new Emails\Triggers(),
			new Globals\Polyfills\Loader(),
			new Integrations\Registry(),

			// Upgrades.
			new Upgrades\Orders\MigrateAfterActionsDate(),
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

		$providers = array(
			new Admin\Styles(),
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
			new Admin\Pointers(),
			new Admin\Settings\Sanitize(),
			new Admin\Downloads\Metabox(),
			new Admin\Promos\Footer\Loader(),
			new Admin\Promos\About(),
			new Admin\Settings\Pointers(),
		);

		return $providers;
	}

	/**
	 * Gets providers that may be extended/replaced in lite/pro.
	 *
	 * @return array
	 */
	protected function get_replaceable_providers() {
		return array(
			'Admin\Extensions\Legacy'   => new Admin\Extensions\Legacy(),
			'Admin\Promos\PromoHandler' => new Admin\Promos\PromoHandler(),
			'Admin\Discounts\Generate'  => new Admin\Discounts\Generate(),
		);
	}

	/**
	 * Gets the replaceable provider class names.
	 *
	 * This allows us to conditionally load the pro version of a provider.
	 *
	 * @since 3.2.0
	 * @return array
	 */
	protected function get_replaceable_core_provider_classes() {
		return array(
			'Admin\Extensions\Legacy'   => 'EDD\Admin\Extensions\Legacy',
			'Admin\Promos\PromoHandler' => 'EDD\Admin\Promos\PromoHandler',
			'Admin\Discounts\Generate'  => 'EDD\Admin\Discounts\Generate',
		);
	}
}
