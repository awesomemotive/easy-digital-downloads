<?php
namespace EDD\Tests\Cart\Preview;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Cart\Preview\Assets;

/**
 * Cart Preview Assets Tests
 *
 * @group edd_cart
 * @group edd_cart_preview
 * @group edd_cart_preview_assets
 */
class PreviewAssets extends EDD_UnitTestCase {

	/**
	 * Assets instance.
	 *
	 * @var Assets
	 */
	protected $assets;

	/**
	 * Download fixture.
	 *
	 * @var \EDD_Download
	 */
	protected static $download;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		// Create a simple download.
		$post_id = self::factory()->post->create( array(
			'post_title'   => 'Test Download',
			'post_type'    => 'download',
			'post_status'  => 'publish',
			'post_content' => '[purchase_link id="1"]',
		) );

		update_post_meta( $post_id, 'edd_price', '20.00' );
		update_post_meta( $post_id, '_edd_product_type', 'default' );

		self::$download = edd_get_download( $post_id );
	}

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->assets = new Assets();

		// Clear cart.
		edd_empty_cart();

		// Reset settings.
		edd_update_option( 'enable_cart_preview', false );
		edd_update_option( 'redirect_on_add', false );

		// Reset admin state.
		set_current_screen( 'front' );
	}

	/**
	 * Tear down after each test.
	 */
	public function tearDown(): void {
		parent::tearDown();
		edd_empty_cart();

		// Dequeue and deregister script to prevent state leakage between tests.
		wp_dequeue_script( 'edd-cart-preview' );
		wp_deregister_script( 'edd-cart-preview' );
	}

	/**
	 * Test should_load returns false when cart preview is disabled.
	 *
	 * @covers \EDD\Cart\Preview\Assets::should_load
	 */
	public function test_should_load_returns_false_when_disabled() {
		edd_update_option( 'enable_cart_preview', false );

		$this->assertFalse( $this->assets->should_load() );
	}

	/**
	 * Test should_load returns false when in admin.
	 *
	 * @covers \EDD\Cart\Preview\Assets::should_load
	 */
	public function test_should_load_returns_false_in_admin() {
		edd_update_option( 'enable_cart_preview', true );
		set_current_screen( 'edit-post' );

		$this->assertFalse( $this->assets->should_load() );
	}

	/**
	 * Test should_load returns true when cart has items.
	 *
	 * @covers \EDD\Cart\Preview\Assets::should_load
	 */
	public function test_should_load_returns_true_with_cart_items() {
		edd_update_option( 'enable_cart_preview', true );
		edd_add_to_cart( self::$download->ID );

		$this->assertTrue( $this->assets->should_load() );
	}

	/**
	 * Test should_load returns true on download post type.
	 *
	 * @covers \EDD\Cart\Preview\Assets::should_load
	 */
	public function test_should_load_returns_true_on_download_post() {
		edd_update_option( 'enable_cart_preview', true );

		// Simulate visiting a download single page using go_to.
		$this->go_to( get_permalink( self::$download->ID ) );

		$this->assertTrue( $this->assets->should_load() );
	}

	/**
	 * Test should_load returns true when page has buy button block.
	 *
	 * @covers \EDD\Cart\Preview\Assets::should_load
	 */
	public function test_should_load_returns_true_with_buy_button_block() {
		edd_update_option( 'enable_cart_preview', true );

		// Create a post with the buy button block.
		$post_id = self::factory()->post->create( array(
			'post_title'   => 'Test Page',
			'post_type'    => 'page',
			'post_content' => '<!-- wp:edd/buy-button /-->',
		) );

		// Simulate visiting the page.
		$this->go_to( get_permalink( $post_id ) );

		$this->assertTrue( $this->assets->should_load() );
	}

	/**
	 * Test should_load returns true when page has downloads block.
	 *
	 * @covers \EDD\Cart\Preview\Assets::should_load
	 */
	public function test_should_load_returns_true_with_downloads_block() {
		edd_update_option( 'enable_cart_preview', true );

		// Create a post with the downloads block.
		$post_id = self::factory()->post->create( array(
			'post_title'   => 'Test Page',
			'post_type'    => 'page',
			'post_content' => '<!-- wp:edd/downloads /-->',
		) );

		// Simulate visiting the page.
		$this->go_to( get_permalink( $post_id ) );

		$this->assertTrue( $this->assets->should_load() );
	}

	/**
	 * Test should_load returns true when page has mini cart block.
	 *
	 * @covers \EDD\Cart\Preview\Assets::should_load
	 */
	public function test_should_load_returns_true_with_mini_cart_block() {
		edd_update_option( 'enable_cart_preview', true );

		// Create a post with the cart block.
		$post_id = self::factory()->post->create( array(
			'post_title'   => 'Test Page',
			'post_type'    => 'page',
			'post_content' => '<!-- wp:edd/cart /-->',
		) );

		// Simulate visiting the page.
		$this->go_to( get_permalink( $post_id ) );

		$this->assertTrue( $this->assets->should_load() );
	}

	/**
	 * Test should_load returns true when page has purchase_link shortcode.
	 *
	 * @covers \EDD\Cart\Preview\Assets::should_load
	 */
	public function test_should_load_returns_true_with_purchase_link_shortcode() {
		edd_update_option( 'enable_cart_preview', true );

		// Create a post with the purchase_link shortcode.
		$post_id = self::factory()->post->create( array(
			'post_title'   => 'Test Page',
			'post_type'    => 'page',
			'post_content' => '[purchase_link id="1"]',
		) );

		// Simulate visiting the page.
		$this->go_to( get_permalink( $post_id ) );

		$this->assertTrue( $this->assets->should_load() );
	}

	/**
	 * Test should_load returns true when page has edd shortcode.
	 *
	 * @covers \EDD\Cart\Preview\Assets::should_load
	 */
	public function test_should_load_returns_true_with_edd_shortcode() {
		edd_update_option( 'enable_cart_preview', true );

		// Create a post with an EDD shortcode.
		$post_id = self::factory()->post->create( array(
			'post_title'   => 'Test Page',
			'post_type'    => 'page',
			'post_content' => '[edd_downloads]',
		) );

		// Simulate visiting the page.
		$this->go_to( get_permalink( $post_id ) );

		$this->assertTrue( $this->assets->should_load() );
	}

	/**
	 * Test should_load filter.
	 *
	 * @covers \EDD\Cart\Preview\Assets::should_load
	 */
	public function test_should_load_filter() {
		edd_update_option( 'enable_cart_preview', true );

		add_filter( 'edd_cart_preview_should_load', '__return_true' );

		$this->assertTrue( $this->assets->should_load() );

		remove_filter( 'edd_cart_preview_should_load', '__return_true' );
	}

	/**
	 * Test enqueue does nothing when should_load is false.
	 *
	 * @covers \EDD\Cart\Preview\Assets::enqueue
	 */
	public function test_enqueue_does_nothing_when_should_load_false() {
		edd_update_option( 'enable_cart_preview', false );

		$this->assets->enqueue();

		$this->assertFalse( wp_script_is( 'edd-cart-preview', 'enqueued' ) );
	}

	/**
	 * Test enqueue registers script when should_load is true.
	 *
	 * @covers \EDD\Cart\Preview\Assets::enqueue
	 */
	public function test_enqueue_registers_script_when_should_load_true() {
		edd_update_option( 'enable_cart_preview', true );
		edd_add_to_cart( self::$download->ID );

		$this->assets->enqueue();

		$this->assertTrue( wp_script_is( 'edd-cart-preview', 'enqueued' ) );
	}

	/**
	 * Test enqueue localizes script with config.
	 *
	 * @covers \EDD\Cart\Preview\Assets::enqueue
	 */
	public function test_enqueue_localizes_script() {
		edd_update_option( 'enable_cart_preview', true );
		edd_add_to_cart( self::$download->ID );

		$this->assets->enqueue();

		global $wp_scripts;
		$script_data = $wp_scripts->get_data( 'edd-cart-preview', 'data' );

		$this->assertNotEmpty( $script_data );
		$this->assertStringContainsString( 'eddCartPreviewConfig', $script_data );
	}

	/**
	 * Test enqueue fires action.
	 *
	 * @covers \EDD\Cart\Preview\Assets::enqueue
	 */
	public function test_enqueue_fires_action() {
		edd_update_option( 'enable_cart_preview', true );
		edd_add_to_cart( self::$download->ID );

		$action_fired = false;
		add_action( 'edd_cart_preview_assets_enqueued', function() use ( &$action_fired ) {
			$action_fired = true;
		} );

		$this->assets->enqueue();

		$this->assertTrue( $action_fired );
	}

	/**
	 * Test configuration includes required keys.
	 *
	 * @covers \EDD\Cart\Preview\Assets::enqueue
	 */
	public function test_config_includes_required_keys() {
		edd_update_option( 'enable_cart_preview', true );
		edd_add_to_cart( self::$download->ID );

		$this->assets->enqueue();

		global $wp_scripts;
		$script_data = $wp_scripts->get_data( 'edd-cart-preview', 'data' );

		// Verify config is present.
		$this->assertStringContainsString( 'eddCartPreviewConfig', $script_data );

		// Check for required keys in the output.
		$required_keys = array(
			'apiBase',
			'timestamp',
			'token',
			'nonce',
			'checkoutUrl',
			'quantitiesEnabled',
			'currency',
			'currencySymbol',
			'autoOpenOnAdd',
			'i18n',
			'buttonColors',
			'debug',
		);

		foreach ( $required_keys as $key ) {
			$this->assertStringContainsString( $key, $script_data, "Config should contain key: {$key}" );
		}

		// Try to parse and verify structure if possible.
		if ( preg_match( '/var eddCartPreviewConfig = ({.+});/s', $script_data, $matches ) ) {
			$config = json_decode( $matches[1], true );
			if ( $config ) {
				foreach ( $required_keys as $key ) {
					$this->assertArrayHasKey( $key, $config, "Parsed config should have key: {$key}" );
				}
			}
		}
	}

	/**
	 * Test configuration autoOpenOnAdd respects redirect_on_add setting.
	 *
	 * @covers \EDD\Cart\Preview\Assets::enqueue
	 */
	public function test_config_auto_open_respects_redirect_setting() {
		edd_update_option( 'enable_cart_preview', true );
		edd_update_option( 'redirect_on_add', true );
		edd_add_to_cart( self::$download->ID );

		$this->assets->enqueue();

		global $wp_scripts;
		$script_data = $wp_scripts->get_data( 'edd-cart-preview', 'data' );

		// The config should be present and contain autoOpenOnAdd set to false or falsy value.
		$this->assertStringContainsString( 'eddCartPreviewConfig', $script_data );
		$this->assertStringContainsString( 'autoOpenOnAdd', $script_data );

		// Extract and parse the config if possible.
		if ( preg_match( '/var eddCartPreviewConfig = ({.+});/s', $script_data, $matches ) ) {
			$config = json_decode( $matches[1], true );
			if ( $config && isset( $config['autoOpenOnAdd'] ) ) {
				// Should be falsy when redirect_on_add is true.
				$this->assertFalse( (bool) $config['autoOpenOnAdd'] );
			}
		}
	}

	/**
	 * Test configuration filter.
	 *
	 * @covers \EDD\Cart\Preview\Assets::enqueue
	 */
	public function test_config_filter() {
		edd_update_option( 'enable_cart_preview', true );
		edd_add_to_cart( self::$download->ID );

		add_filter( 'edd_cart_preview_config', function( $config ) {
			$config['custom_field'] = 'custom_value';
			return $config;
		} );

		$this->assets->enqueue();

		global $wp_scripts;
		$script_data = $wp_scripts->get_data( 'edd-cart-preview', 'data' );

		// Verify the filter was applied by checking for the custom field in the output.
		$this->assertStringContainsString( 'custom_field', $script_data );
		$this->assertStringContainsString( 'custom_value', $script_data );

		// Try to parse and verify if possible.
		if ( preg_match( '/var eddCartPreviewConfig = ({.+});/s', $script_data, $matches ) ) {
			$config = json_decode( $matches[1], true );
			if ( $config ) {
				$this->assertArrayHasKey( 'custom_field', $config );
				$this->assertEquals( 'custom_value', $config['custom_field'] );
			}
		}
	}

	/**
	 * Test should_load returns false when no qualifying content.
	 *
	 * @covers \EDD\Cart\Preview\Assets::should_load
	 */
	public function test_should_load_returns_false_without_qualifying_content() {
		edd_update_option( 'enable_cart_preview', true );

		// Create a plain post with no EDD content.
		$post_id = self::factory()->post->create( array(
			'post_title'   => 'Test Page',
			'post_type'    => 'page',
			'post_content' => 'Just some regular content.',
		) );

		// Simulate visiting the page.
		$this->go_to( get_permalink( $post_id ) );

		$this->assertFalse( $this->assets->should_load() );
	}
}
