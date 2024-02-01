<?php

namespace EDD\Tests\SiteHealth;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\SiteHealth\Direct as DirectTest;

class Direct extends EDD_UnitTestCase {

	/**
	 * The Direct class instance.
	 *
	 * @var DirectTest
	 */
	private static $test;

	/**
	 * Setup the test class.
	 */
	public static function wpSetUpBeforeClass() {
		self::$test = new DirectTest();
	}

	/**
	 * @covers \EDD\Admin\SiteHealth\Direct::get_test_missing_purchase_page()
	 */
	public function test_get_test_missing_purchase_page() {
		$expected = array(
			'label'       => 'You have a checkout page set',
			'status'      => 'good',
			'badge'       => array(
				'label' => 'Easy Digital Downloads',
				'color' => 'blue',
			),
			'description' => '<p>Your checkout page is set up and ready to process orders.</p>',
			'actions'     => '',
			'test'        => 'edd_missing_purchase_page',
		);

		$result = self::$test->get_test_missing_purchase_page();

		$this->assertSame( $expected, $result );
	}

	/**
	 * @covers \EDD\Admin\SiteHealth\Direct::get_test_missing_purchase_page()
	 */
	public function test_get_test_missing_purchase_page_not_set() {
		$purchase_page = edd_get_option( 'purchase_page', false );
		$button_url    = edd_get_admin_url(
			array(
				'page'    => 'edd-settings',
				'tab'     => 'general',
				'section' => 'pages',
			)
		);

		edd_update_option( 'purchase_page', 0 );

		$expected = array(
			'label'       => 'Your checkout page is missing',
			'status'      => 'critical',
			'badge'       => array(
				'label' => 'Easy Digital Downloads',
				'color' => 'red',
			),
			'description' => '<p>Easy Digital Downloads requires a specific checkout page to be set to easily handle user interactions.</p>',
			'actions'     => '<a class="button button-primary" href="' . esc_url( $button_url ) . '">Fix the Checkout Page</a>',
			'test'        => 'edd_missing_purchase_page',
		);

		$result = self::$test->get_test_missing_purchase_page();

		$this->assertSame( $expected, $result );

		edd_update_option( 'purchase_page', $purchase_page );
	}

	/**
	 * @covers \EDD\Admin\SiteHealth\Direct::get_test_missing_purchase_page()
	 */
	public function test_get_test_missing_purchase_page_using_shortcode() {
		$purchase_page = edd_get_option( 'purchase_page', false );
		$button_url    = admin_url( 'post.php?post=' . $purchase_page . '&action=edit' );

		// Get the purchase page content, so we can reset it later.
		$purchase_page_content = get_post( $purchase_page )->post_content;

		// Update the purchase page content to use the legacy shortcode.
		wp_update_post(
			array(
				'ID'           => $purchase_page,
				'post_content' => '[download_checkout]',
			)
		);

		$expected = array(
			'label'       => 'Your checkout page is using the legacy shortcode',
			'status'      => 'recommended',
			'badge'       => array(
				'label' => 'Easy Digital Downloads',
				'color' => 'orange',
			),
			'description' => wpautop( 'Your checkout page is configured; however, it is currently using the legacy <code>[download_checkout]</code> shortcode. We recommend changing your checkout to use the EDD Checkout Block.</p>' ),
			'actions'     => '<a class="button button-primary" href="' . esc_url( $button_url ) . '">Edit Checkout Page</a>',
			'test'        => 'edd_missing_purchase_page',
		);

		$result = self::$test->get_test_missing_purchase_page();

		$this->assertSame( $expected, $result );

		// Reset the purchase page content.
		wp_update_post(
			array(
				'ID'           => $purchase_page,
				'post_content' => $purchase_page_content,
			)
		);
	}

	/**
	 * @covers \EDD\Admin\SiteHealth\Direct::get_test_gateways_enabled()
	 */
	public function test_get_test_gateways_enabled() {
		$enabled_gateways = edd_get_option( 'gateways', array() );

		edd_update_option( 'gateways', array( 'manual' ) );

		$expected = array(
			'label'       => 'You have at least one gateway enabled',
			'status'      => 'good',
			'badge'       => array(
				'label' => 'Easy Digital Downloads',
				'color' => 'blue',
			),
			'description' => '<p>Fantastic! You have enabled a gateway and can accept orders.</p>',
			'actions'     => '',
			'test'        => 'edd_gateways_enabled',
		);

		$result = self::$test->get_test_gateways_enabled();

		$this->assertSame( $expected, $result );

		// Reset the gateways.
		edd_update_option( 'gateways', $enabled_gateways );
	}

	/**
	 * @covers \EDD\Admin\SiteHealth\Direct::get_test_gateways_enabled()
	 */
	public function test_get_test_gateways_enabled_no_gateways_live_mode() {
		$enabled_gateways = edd_get_option( 'gateways', array() );
		$button_url       = edd_get_admin_url(
			array(
				'page' => 'edd-settings',
				'tab'  => 'gateways',
			)
		);

		// Remove all gateways.
		edd_update_option( 'gateways', array() );
		add_filter( 'edd_is_test_mode', '__return_false' );

		$expected = array(
			'label'       => 'Your store is not accepting payments',
			'status'      => 'critical',
			'badge'       => array(
				'label' => 'Easy Digital Downloads',
				'color' => 'red',
			),
			'description' =>
				'<p>To process orders that require payment, you must have a gateway enabled.</p>' .
				'<p>A gateway is a service, such as PayPal or Stripe, that allows your store to accept payments. ' .
				'Stores that offer multiple ways for their customers to pay see higher conversion rates.</p>',
			'actions'     => '<a class="button button-primary" href="' . esc_url( $button_url ) . '">Configure a Gateway</a>',
			'test'        => 'edd_gateways_enabled',
		);

		$result = self::$test->get_test_gateways_enabled();

		$this->assertSame( $expected, $result );

		// Reset the gateways.
		edd_update_option( 'gateways', $enabled_gateways );
		remove_filter( 'edd_is_test_mode', '__return_false' );
	}

	/**
	 * @covers \EDD\Admin\SiteHealth\Direct::get_test_gateways_enabled()
	 */
	public function test_get_test_gateways_enabled_no_gateways_test_mode() {
		$enabled_gateways = edd_get_option( 'gateways', array() );
		$button_url       = edd_get_admin_url(
			array(
				'page' => 'edd-settings',
				'tab'  => 'gateways',
			)
		);

		// Remove all gateways.
		edd_update_option( 'gateways', array() );
		add_filter( 'edd_is_test_mode', '__return_true' );

		$expected = array(
			'label'       => 'Your store is not accepting payments',
			'status'      => 'recommended',
			'badge'       => array(
				'label' => 'Easy Digital Downloads',
				'color' => 'gray',
			),
			'description' =>
				'<p>To process orders that require payment, you must have a gateway enabled.</p>' .
				'<p>A gateway is a service, such as PayPal or Stripe, that allows your store to accept payments. ' .
				'Stores that offer multiple ways for their customers to pay see higher conversion rates.</p>',
			'actions'     => '<a class="button button-primary" href="' . esc_url( $button_url ) . '">Configure a Gateway</a>',
			'test'        => 'edd_gateways_enabled',
		);

		$result = self::$test->get_test_gateways_enabled();

		$this->assertSame( $expected, $result );

		// Reset the gateways.
		edd_update_option( 'gateways', $enabled_gateways );
		remove_filter( 'edd_is_test_mode', '__return_true' );
	}
}
