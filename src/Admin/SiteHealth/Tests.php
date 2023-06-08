<?php
/**
 * Site Health integration for EDD.
 */
namespace EDD\Admin\SiteHealth;

defined( 'ABSPATH' ) || exit;

use EDD\EventManagement\SubscriberInterface;

class Tests implements SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.1.2
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'site_status_tests' => 'add_tests',
		);
	}

	/**
	 * Register custom tests for EDD.
	 *
	 * @since 3.1.2
	 * @param array $tests
	 * @return array
	 */
	public function add_tests( $tests ) {
		$direct       = new Direct();
		$direct_tests = $direct->get();
		if ( ! empty( $direct_tests ) ) {
			$tests['direct'] = array_merge( $tests['direct'], $direct_tests );
		}

		return $tests;
	}
}
