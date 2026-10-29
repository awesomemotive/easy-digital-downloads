<?php
/**
 * Tests for the EventManagement Subscribers service-provider bootstrap.
 */

namespace EDD\Tests\EventManagement;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\EventManagement\SubscriberInterface;

/**
 * A concrete Subscribers implementation whose provider list contains an object
 * that is not a SubscriberInterface. Passing it to EventManager::add_subscriber()
 * triggers a TypeError (a \Throwable/\Error, not an \Exception).
 */
class InvalidProviderSubscribers extends \EDD\EventManagement\Subscribers {

	protected function get_service_providers() {
		return array( new \stdClass() );
	}

	protected function get_admin_providers() {
		return array();
	}
}

/**
 * A valid subscriber that registers a known hook, used to confirm normal
 * registration is unaffected by the defensive catch.
 */
class ValidProvider implements SubscriberInterface {

	public static function get_subscribed_events() {
		return array( 'edd_test_subscriber_hook' => 'noop' );
	}

	public function noop() {}
}

/**
 * A concrete Subscribers implementation that registers a single valid provider.
 */
class ValidProviderSubscribers extends \EDD\EventManagement\Subscribers {

	protected function get_service_providers() {
		return array( new ValidProvider() );
	}

	protected function get_admin_providers() {
		return array();
	}
}

/**
 * @group edd_event_management
 * @group edd_bootstrap
 */
class Subscribers extends EDD_UnitTestCase {

	/**
	 * A provider that is not a SubscriberInterface throws a TypeError inside
	 * add_subscriber(). Because a TypeError is a \Throwable (\Error), not an
	 * \Exception, boot must catch it and skip the provider rather than fatal.
	 * This is the regression guard for issue #2572: a bare `catch ( Exception )`
	 * would let the TypeError escape and instantiation here would error out.
	 */
	public function test_invalid_provider_is_skipped_without_fatal() {
		$subscribers = new InvalidProviderSubscribers();

		$this->assertInstanceOf( \EDD\EventManagement\Subscribers::class, $subscribers );
	}

	/**
	 * The defensive catch must not interfere with valid providers: a real
	 * SubscriberInterface should still have its hooks registered during boot.
	 */
	public function test_valid_provider_is_registered() {
		new ValidProviderSubscribers();

		$this->assertNotFalse( has_action( 'edd_test_subscriber_hook' ) );
	}
}
