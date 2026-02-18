<?php
/**
 * tests-environment-checker.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\Tests\Utils;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Utils\EnvironmentChecker;

/**
 * @coversDefaultClass \EDD\Utils\EnvironmentChecker
 */
class EnvironmentCheckerTests extends EDD_UnitTestCase {

	/**
	 * @var EnvironmentChecker
	 */
	protected $environmentChecker;

	/**
	 * Runs once before each test.
	 */
	public function setup(): void {
		$this->environmentChecker = new EnvironmentChecker();

		// Reset pass data so it can be set explicitly for each test.
		delete_option( 'edd_pass_licenses' );
		global $edd_licensed_products;
		$edd_licensed_products = null;
	}

	/**
	 * A 2.x wildcard should match version 2.11.3.
	 *
	 * @covers \EDD\Utils\EnvironmentChecker::versionNumbersMatch
	 */
	public function test_2x_wildcard_matches_version_2_11_3() {
		$this->assertTrue( $this->environmentChecker->versionNumbersMatch( '2.11.3', '2.x' ) );
		$this->assertTrue( $this->environmentChecker->versionNumbersMatch( '2.11.3', '2-x' ) );
	}

	/**
	 * A 2.11.x wildcard should match version 2.11.3.
	 *
	 * @covers \EDD\Utils\EnvironmentChecker::versionNumbersMatch
	 */
	public function test_2_11x_wildcard_matches_version_2_11_3() {
		$this->assertTrue( $this->environmentChecker->versionNumbersMatch( '2.11.3', '2.11.x' ) );
		$this->assertTrue( $this->environmentChecker->versionNumbersMatch( '2.11.3', '2-11-x' ) );
	}

	/**
	 * A 2.x wildcard should NOT match version 3.0.
	 *
	 * @covers \EDD\Utils\EnvironmentChecker::versionNumbersMatch
	 */
	public function test_2x_wildcard_doesnt_match_version_3() {
		$this->assertFalse( $this->environmentChecker->versionNumbersMatch( '3.0', '2.x' ) );
		$this->assertFalse( $this->environmentChecker->versionNumbersMatch( '3.0', '2-x' ) );
	}

	/**
	 * A 2.11.x wildcard should NOT match version 3.0.
	 *
	 * @covers \EDD\Utils\EnvironmentChecker::versionNumbersMatch
	 */
	public function test_2_11x_wildcard_doesnt_match_version_3() {
		$this->assertFalse( $this->environmentChecker->versionNumbersMatch( '3.0', '2.11.x' ) );
		$this->assertFalse( $this->environmentChecker->versionNumbersMatch( '3.0', '2-11-x' ) );
	}

	/**
	 * A 2.11.3 exact version should match version 2.11.3.
	 *
	 * @covers \EDD\Utils\EnvironmentChecker::versionNumbersMatch
	 */
	public function test_exact_version_matches() {
		$this->assertTrue( $this->environmentChecker->versionNumbersMatch( '2.11.3', '2.11.3' ) );
		$this->assertTrue( $this->environmentChecker->versionNumbersMatch( '2.11.3', '2-11-3' ) );
	}

	/**
	 * @covers \EDD\Utils\EnvironmentChecker::hasLicenseType
	 */
	public function test_site_with_no_licenses() {
		$this->assertTrue( $this->environmentChecker->meetsCondition( 'free' ) );

		$conditionsThatShouldFail = array(
			'ala-carte',
			'pass-personal',
			'pass-extended',
			'pass-professional',
			'pass-all-access',
			'pass-any',
		);

		foreach( $conditionsThatShouldFail as $condition ) {
			$this->assertFalse( $this->environmentChecker->meetsCondition( $condition ) );
		}
	}

	/**
	 * @covers \EDD\Utils\EnvironmentChecker::paymentGatewayMatch
	 */
	public function test_stripe_gateway_matches_if_stripe_gateway_enabled() {
		$this->assertTrue( $this->environmentChecker->paymentGatewayMatch( array( 'stripe', 'paypal_commerce' ), 'gateway-stripe' ) );
	}

	/**
	 * @covers \EDD\Utils\EnvironmentChecker::paymentGatewayMatch
	 */
	public function test_stripe_gateway_doesnt_match_if_stripe_gateway_not_enabled() {
		$this->assertFalse( $this->environmentChecker->paymentGatewayMatch( array( 'paypal_commerce' ), 'gateway-stripe' ) );
	}

	/**
	 * @covers \EDD\Utils\EnvironmentChecker::meetsCondition
	 * @covers \EDD\Utils\EnvironmentChecker::paymentGatewayMatch
	 */
	public function test_stripe_condition_met_if_stripe_gateway_enabled() {
		$callback = static function ( $gateways ) {
			return array(
				'stripe' => array(
					'admin_label'    => 'Stripe',
					'checkout_label' => 'Stripe',
					'supports'       => array(
						'buy_now'
					),
				),
			);
		};

		add_filter( 'edd_enabled_payment_gateways', $callback );

		$this->assertTrue( $this->environmentChecker->meetsCondition( 'gateway-stripe' ) );
		$this->assertFalse( $this->environmentChecker->meetsCondition( 'gateway-paypal_commerce' ) );

		remove_filter( 'edd_enabled_payment_gateways', $callback );
	}

	/**
	 * @covers \EDD\Utils\EnvironmentChecker::isPhpVersion
	 * @covers \EDD\Utils\EnvironmentChecker::phpVersionMatch
	 */
	public function test_php_version_exact_match() {
		$this->assertTrue( $this->environmentChecker->phpVersionMatch( '8.2.10', 'php-8-2' ) );
		$this->assertTrue( $this->environmentChecker->phpVersionMatch( '7.4.33', 'php-7-4' ) );
	}

	/**
	 * @covers \EDD\Utils\EnvironmentChecker::isPhpVersion
	 * @covers \EDD\Utils\EnvironmentChecker::phpVersionMatch
	 */
	public function test_php_version_wildcard_match() {
		$this->assertTrue( $this->environmentChecker->phpVersionMatch( '8.2.10', 'php-8-x' ) );
		$this->assertTrue( $this->environmentChecker->phpVersionMatch( '8.1.25', 'php-8-x' ) );
		$this->assertTrue( $this->environmentChecker->phpVersionMatch( '7.4.33', 'php-7-x' ) );
	}

	/**
	 * @covers \EDD\Utils\EnvironmentChecker::isPhpVersion
	 * @covers \EDD\Utils\EnvironmentChecker::phpVersionMatch
	 */
	public function test_php_version_non_match() {
		$this->assertFalse( $this->environmentChecker->phpVersionMatch( '8.2.10', 'php-7-4' ) );
		$this->assertFalse( $this->environmentChecker->phpVersionMatch( '7.4.33', 'php-8-x' ) );
	}

	/**
	 * @covers \EDD\Utils\EnvironmentChecker::meetsCondition
	 * @covers \EDD\Utils\EnvironmentChecker::isPhpVersion
	 */
	public function test_php_version_condition_via_meets_condition() {
		$majorVersion = PHP_MAJOR_VERSION;
		$minorVersion = PHP_MINOR_VERSION;

		// Current PHP version should match.
		$this->assertTrue( $this->environmentChecker->meetsCondition( "php-{$majorVersion}-{$minorVersion}" ) );
		$this->assertTrue( $this->environmentChecker->meetsCondition( "php-{$majorVersion}-x" ) );
	}

	/**
	 * @covers \EDD\Utils\EnvironmentChecker::isWordPressVersion
	 * @covers \EDD\Utils\EnvironmentChecker::wordPressVersionMatch
	 */
	public function test_wordpress_version_exact_match() {
		$this->assertTrue( $this->environmentChecker->wordPressVersionMatch( '6.4.2', 'wp-6-4' ) );
		$this->assertTrue( $this->environmentChecker->wordPressVersionMatch( '5.9.3', 'wp-5-9' ) );
	}

	/**
	 * @covers \EDD\Utils\EnvironmentChecker::isWordPressVersion
	 * @covers \EDD\Utils\EnvironmentChecker::wordPressVersionMatch
	 */
	public function test_wordpress_version_wildcard_match() {
		$this->assertTrue( $this->environmentChecker->wordPressVersionMatch( '6.4.2', 'wp-6-x' ) );
		$this->assertTrue( $this->environmentChecker->wordPressVersionMatch( '6.3.1', 'wp-6-x' ) );
		$this->assertTrue( $this->environmentChecker->wordPressVersionMatch( '5.9.3', 'wp-5-x' ) );
	}

	/**
	 * @covers \EDD\Utils\EnvironmentChecker::isWordPressVersion
	 * @covers \EDD\Utils\EnvironmentChecker::wordPressVersionMatch
	 */
	public function test_wordpress_version_non_match() {
		$this->assertFalse( $this->environmentChecker->wordPressVersionMatch( '6.4.2', 'wp-5-9' ) );
		$this->assertFalse( $this->environmentChecker->wordPressVersionMatch( '5.9.3', 'wp-6-x' ) );
	}

	/**
	 * @covers \EDD\Utils\EnvironmentChecker::meetsCondition
	 * @covers \EDD\Utils\EnvironmentChecker::isWordPressVersion
	 */
	public function test_wordpress_version_condition_via_meets_condition() {
		global $wp_version;

		$versionParts = explode( '.', $wp_version );
		$majorVersion = $versionParts[0];
		$minorVersion = isset( $versionParts[1] ) ? $versionParts[1] : '0';

		// Current WordPress version should match.
		$this->assertTrue( $this->environmentChecker->meetsCondition( "wp-{$majorVersion}-{$minorVersion}" ) );
		$this->assertTrue( $this->environmentChecker->meetsCondition( "wp-{$majorVersion}-x" ) );
	}

	/**
	 * @covers \EDD\Utils\EnvironmentChecker::isPluginCondition
	 * @covers \EDD\Utils\EnvironmentChecker::pluginIsActive
	 */
	public function test_plugin_condition_active_plugin() {
		// Add a fake plugin to active plugins.
		$activePlugins   = get_option( 'active_plugins', array() );
		$activePlugins[] = 'edd-recurring/edd-recurring.php';
		update_option( 'active_plugins', $activePlugins );

		$this->assertTrue( $this->environmentChecker->pluginIsActive( 'plugin-edd-recurring' ) );

		// Clean up.
		$activePlugins = array_filter( $activePlugins, function( $plugin ) {
			return 'edd-recurring/edd-recurring.php' !== $plugin;
		} );
		update_option( 'active_plugins', $activePlugins );
	}

	/**
	 * @covers \EDD\Utils\EnvironmentChecker::isPluginCondition
	 * @covers \EDD\Utils\EnvironmentChecker::pluginIsActive
	 */
	public function test_plugin_condition_inactive_plugin() {
		$this->assertFalse( $this->environmentChecker->pluginIsActive( 'plugin-edd-nonexistent-plugin' ) );
	}

	/**
	 * @covers \EDD\Utils\EnvironmentChecker::meetsCondition
	 * @covers \EDD\Utils\EnvironmentChecker::isPluginCondition
	 */
	public function test_plugin_condition_via_meets_condition() {
		// Add a fake plugin to active plugins.
		$activePlugins   = get_option( 'active_plugins', array() );
		$activePlugins[] = 'edd-software-licensing/edd-software-licensing.php';
		update_option( 'active_plugins', $activePlugins );

		$this->assertTrue( $this->environmentChecker->meetsCondition( 'plugin-edd-software-licensing' ) );
		$this->assertFalse( $this->environmentChecker->meetsCondition( 'plugin-edd-nonexistent' ) );

		// Clean up.
		$activePlugins = array_filter( $activePlugins, function( $plugin ) {
			return 'edd-software-licensing/edd-software-licensing.php' !== $plugin;
		} );
		update_option( 'active_plugins', $activePlugins );
	}

	/**
	 * @covers \EDD\Utils\EnvironmentChecker::meetsConditions
	 */
	public function test_meets_conditions_with_multiple_condition_types() {
		// Add a fake plugin to active plugins.
		$activePlugins   = get_option( 'active_plugins', array() );
		$activePlugins[] = 'edd-recurring/edd-recurring.php';
		update_option( 'active_plugins', $activePlugins );

		$majorVersion = PHP_MAJOR_VERSION;

		// All conditions should pass.
		$conditions = array(
			"php-{$majorVersion}-x",
			'plugin-edd-recurring',
		);
		$this->assertTrue( $this->environmentChecker->meetsConditions( $conditions ) );

		// This should fail because the plugin is not active.
		$conditions = array(
			"php-{$majorVersion}-x",
			'plugin-edd-nonexistent',
		);
		$this->assertFalse( $this->environmentChecker->meetsConditions( $conditions ) );

		// Clean up.
		$activePlugins = array_filter( $activePlugins, function( $plugin ) {
			return 'edd-recurring/edd-recurring.php' !== $plugin;
		} );
		update_option( 'active_plugins', $activePlugins );
	}

	/**
	 * @covers \EDD\Utils\EnvironmentChecker::isEddVersion
	 * @covers \EDD\Utils\EnvironmentChecker::eddVersionMatch
	 */
	public function test_edd_version_exact_match() {
		$this->assertTrue( $this->environmentChecker->eddVersionMatch( '3.3.5', 'edd-3-3' ) );
		$this->assertTrue( $this->environmentChecker->eddVersionMatch( '3.3.5', 'edd-3-3-5' ) );
		$this->assertTrue( $this->environmentChecker->eddVersionMatch( '2.11.4', 'edd-2-11' ) );
	}

	/**
	 * @covers \EDD\Utils\EnvironmentChecker::isEddVersion
	 * @covers \EDD\Utils\EnvironmentChecker::eddVersionMatch
	 */
	public function test_edd_version_wildcard_match() {
		$this->assertTrue( $this->environmentChecker->eddVersionMatch( '3.3.5', 'edd-3-x' ) );
		$this->assertTrue( $this->environmentChecker->eddVersionMatch( '3.2.0', 'edd-3-x' ) );
		$this->assertTrue( $this->environmentChecker->eddVersionMatch( '3.3.5', 'edd-3-3-x' ) );
		$this->assertTrue( $this->environmentChecker->eddVersionMatch( '2.11.4', 'edd-2-x' ) );
	}

	/**
	 * @covers \EDD\Utils\EnvironmentChecker::isEddVersion
	 * @covers \EDD\Utils\EnvironmentChecker::eddVersionMatch
	 */
	public function test_edd_version_non_match() {
		$this->assertFalse( $this->environmentChecker->eddVersionMatch( '3.3.5', 'edd-2-x' ) );
		$this->assertFalse( $this->environmentChecker->eddVersionMatch( '3.3.5', 'edd-3-2' ) );
		$this->assertFalse( $this->environmentChecker->eddVersionMatch( '2.11.4', 'edd-3-x' ) );
	}

	/**
	 * @covers \EDD\Utils\EnvironmentChecker::meetsCondition
	 * @covers \EDD\Utils\EnvironmentChecker::isEddVersion
	 */
	public function test_edd_version_condition_via_meets_condition() {
		$versionParts = explode( '.', EDD_VERSION );
		$majorVersion = $versionParts[0];
		$minorVersion = isset( $versionParts[1] ) ? $versionParts[1] : '0';

		// Current EDD version should match.
		$this->assertTrue( $this->environmentChecker->meetsCondition( "edd-{$majorVersion}-{$minorVersion}" ) );
		$this->assertTrue( $this->environmentChecker->meetsCondition( "edd-{$majorVersion}-x" ) );
	}

	/**
	 * Legacy version number format should still work.
	 *
	 * @covers \EDD\Utils\EnvironmentChecker::meetsCondition
	 * @covers \EDD\Utils\EnvironmentChecker::isVersionNumber
	 */
	public function test_legacy_version_number_still_works() {
		$versionParts = explode( '.', EDD_VERSION );
		$majorVersion = $versionParts[0];

		// Legacy format (without edd- prefix) should still work.
		$this->assertTrue( $this->environmentChecker->meetsCondition( "{$majorVersion}.x" ) );
	}

}
