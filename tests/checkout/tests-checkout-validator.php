<?php
namespace EDD\Tests\Checkout;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Checkout Validator tests.
 * Currently only tests the `edd_is_checkout` function.
 */
class Validator extends EDD_UnitTestCase {

	public function tearDown(): void {
		// Reset the block-template global so a simulated template never leaks into other tests.
		unset( $GLOBALS['_wp_current_template_content'] );

		parent::tearDown();
	}

	public function test_edd_is_checkout_setting() {
		$checkout_page = edd_get_option( 'purchase_page' );

		$this->go_to( get_permalink( $checkout_page ) );

		$this->assertTrue( edd_is_checkout() );
	}

	public function test_edd_is_checkout_shortcode() {
		$post_id = $this->factory->post->create( array(
			'post_title'   => 'Test Page',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => '[download_checkout]',
		) );

		$this->go_to( get_permalink( $post_id ) );

		do_action( 'template_redirect' ); // Necessary to trigger correct actions

		$this->assertTrue( edd_is_checkout() );
	}

	public function test_edd_is_checkout_block() {
		$post_id = $this->factory->post->create( array(
			'post_title'   => 'Test Page',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => '<!-- wp:edd/checkout /-->',
		) );
		$this->go_to( get_permalink( $post_id ) );

		do_action( 'template_redirect' ); // Necessary to trigger correct actions

		$this->assertTrue( edd_is_checkout() );
	}

	public function test_edd_is_checkout_fail() {
		$post_id = $this->factory->post->create( array(
			'post_title'   => 'Test Page 2',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => 'Test Page',
		) );

		$this->go_to( get_permalink( $post_id ) );

		do_action( 'template_redirect' ); // Necessary to trigger correct actions

		$this->assertFalse( edd_is_checkout() );
	}

	public function test_edd_is_checkout_ajax_is_true_shortcode() {
		$post_id = $this->factory->post->create( array(
			'post_title'   => 'Test Page',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => '[download_checkout]',
		) );
		$_POST['current_page'] = $post_id;
		add_filter( 'wp_doing_ajax', '__return_true' );

		$this->assertTrue( edd_is_checkout() );

		unset( $_POST['current_page'] );
		remove_filter( 'wp_doing_ajax', '__return_true' );
	}

	public function test_edd_is_checkout_ajax_is_true_block() {
		$post_id = $this->factory->post->create( array(
			'post_title'   => 'Test Page',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => '<!-- wp:edd/checkout /-->',
		) );
		$_POST['current_page'] = $post_id;
		add_filter( 'wp_doing_ajax', '__return_true' );

		$this->assertTrue( edd_is_checkout() );

		unset( $_POST['current_page'] );
		remove_filter( 'wp_doing_ajax', '__return_true' );
	}

	/**
	 * Test that has_checkout returns false when post_id is 0.
	 * This tests the scenario where get_queried_object_id() would return 0.
	 */
	public function test_edd_is_checkout_with_zero_queried_object_id() {
		// Use reflection to access the private has_checkout method
		$reflection = new \ReflectionClass( 'EDD\Checkout\Validator' );
		$method = $reflection->getMethod( 'has_checkout' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		// Test with post_id = 0 (should return false)
		$result = $method->invokeArgs( null, array( 0 ) );
		$this->assertFalse( $result, 'has_checkout should return false when post_id is 0' );

		// Test with post_id = false (should return false)
		$result = $method->invokeArgs( null, array( false ) );
		$this->assertFalse( $result, 'has_checkout should return false when post_id is false' );

		// Test with post_id = null (should return false)
		$result = $method->invokeArgs( null, array( null ) );
		$this->assertFalse( $result, 'has_checkout should return false when post_id is null' );

		// Test with post_id = empty string (should return false)
		$result = $method->invokeArgs( null, array( '' ) );
		$this->assertFalse( $result, 'has_checkout should return false when post_id is empty string' );

		// Create a valid post to verify the method works with valid IDs
		$post_id = $this->factory->post->create( array(
			'post_title'   => 'Test Post',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => '[download_checkout]',
		) );

		// Test with valid post_id that has checkout shortcode (should return true)
		$result = $method->invokeArgs( null, array( $post_id ) );
		$this->assertTrue( $result, 'has_checkout should return true when post has checkout shortcode' );

		// Create a post without checkout content
		$regular_post_id = $this->factory->post->create( array(
			'post_title'   => 'Regular Post',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => 'Regular content',
		) );

		// Test with valid post_id that doesn't have checkout content (should return false)
		$result = $method->invokeArgs( null, array( $regular_post_id ) );
		$this->assertFalse( $result, 'has_checkout should return false when post has no checkout content' );
	}

	/**
	 * Test that has_checkout returns true with checkout block content.
	 */
	public function test_has_checkout_with_block_content() {
		// Use reflection to access the private has_checkout method
		$reflection = new \ReflectionClass( 'EDD\Checkout\Validator' );
		$method = $reflection->getMethod( 'has_checkout' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		// Create a post with checkout block
		$block_post_id = $this->factory->post->create( array(
			'post_title'   => 'Checkout Block Page',
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => '<!-- wp:edd/checkout /-->',
		) );

		// Test with valid post_id that has checkout block (should return true)
		$result = $method->invokeArgs( null, array( $block_post_id ) );
		$this->assertTrue( $result, 'has_checkout should return true when post has checkout block' );
	}

	public function test_get_checkout_type_default_is_block() {
		$this->assertEquals( 'block', \EDD\Checkout\Validator::get_checkout_type() );
	}

	public function test_get_checkout_type_unknown_when_no_purchase_page() {
		$original = edd_get_option( 'purchase_page' );
		edd_delete_option( 'purchase_page' );

		$this->assertEquals( 'unknown', \EDD\Checkout\Validator::get_checkout_type() );

		edd_update_option( 'purchase_page', $original );
	}

	public function test_get_checkout_type_shortcode() {
		$page_id = $this->factory->post->create(
			array(
				'post_type'    => 'page',
				'post_content' => '[download_checkout]',
			)
		);

		$this->assertEquals( 'shortcode', \EDD\Checkout\Validator::get_checkout_type( $page_id ) );

		wp_delete_post( $page_id );
	}

	public function test_get_checkout_type_block() {
		$page_id = $this->factory->post->create(
			array(
				'post_type'    => 'page',
				'post_content' => '<!-- wp:edd/checkout /-->',
			)
		);

		$this->assertEquals( 'block', \EDD\Checkout\Validator::get_checkout_type( $page_id ) );

		wp_delete_post( $page_id );
	}

	public function test_get_checkout_type_undetermined_when_no_recognized_checkout() {
		$page_id = $this->factory->post->create(
			array(
				'post_type'    => 'page',
				'post_content' => '<p>Just some content, no checkout.</p>',
			)
		);

		$this->assertEquals( 'undetermined', \EDD\Checkout\Validator::get_checkout_type( $page_id ) );

		wp_delete_post( $page_id );
	}

	public function test_get_checkout_type_elementor() {
		// The stub redeclares \Elementor\Plugin; only load it when real Elementor is
		// absent, otherwise a full suite run with real Elementor fatals on redeclare.
		if ( class_exists( '\Elementor\Plugin', false ) ) {
			$this->markTestSkipped( 'Real Elementor is active; this test exercises the Elementor stub only.' );
		}

		require_once EDD_PLUGIN_DIR . 'tests/helpers/stubs/elementor.php';

		$page_id = $this->factory->post->create(
			array(
				'post_type'    => 'page',
				'post_content' => '',
			)
		);

		// Set up Elementor stub with an edd-checkout widget on the page.
		\Elementor\Plugin::init();
		\Elementor\Plugin::$instance->documents->register(
			$page_id,
			new \Elementor\Document(
				true,
				array(
					array(
						'elType'     => 'widget',
						'widgetType' => 'edd-checkout',
						'settings'   => array(),
					),
				)
			)
		);

		$this->assertEquals( 'elementor', \EDD\Checkout\Validator::get_checkout_type( $page_id ) );

		\Elementor\Plugin::reset();
		wp_delete_post( $page_id );
	}

	/**
	 * The inner-blocks checkout makes per-section coverage decisions (the cart's
	 * discount-form suppression, the UserDetails fallback) by asking has_block()
	 * for a specific inner block. So the second argument must detect each inner
	 * block independently, not just the parent edd/checkout block.
	 */
	public function test_has_block_detects_personal_info_inner_block() {
		$post_id = $this->factory->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '<!-- wp:edd/checkout --><!-- wp:edd/checkout-personal-info /--><!-- /wp:edd/checkout -->',
			)
		);

		$this->assertTrue( \EDD\Checkout\Validator::has_block( $post_id, 'edd/checkout-personal-info' ) );
	}

	/**
	 * A page can contain the parent checkout and some inner blocks but omit others.
	 * has_block() must report the absent block as missing so the fallback logic runs.
	 */
	public function test_has_block_returns_false_when_inner_block_absent() {
		$post_id = $this->factory->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '<!-- wp:edd/checkout --><!-- wp:edd/checkout-payment-info /--><!-- /wp:edd/checkout -->',
			)
		);

		$this->assertFalse( \EDD\Checkout\Validator::has_block( $post_id, 'edd/checkout-personal-info' ) );
	}

	/**
	 * Detecting one inner block must not imply the presence of another.
	 */
	public function test_has_block_detects_inner_blocks_independently() {
		$post_id = $this->factory->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '<!-- wp:edd/checkout --><!-- wp:edd/checkout-discount-form /--><!-- /wp:edd/checkout -->',
			)
		);

		$this->assertTrue( \EDD\Checkout\Validator::has_block( $post_id, 'edd/checkout-discount-form' ) );
		$this->assertFalse( \EDD\Checkout\Validator::has_block( $post_id, 'edd/checkout-personal-info' ) );
	}

	/**
	 * The default second argument must still resolve the parent checkout block,
	 * so existing callers that pass only a post ID keep working.
	 */
	public function test_has_block_default_argument_resolves_parent_checkout() {
		$post_id = $this->factory->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '<!-- wp:edd/checkout /-->',
			)
		);

		$this->assertTrue( \EDD\Checkout\Validator::has_block( $post_id ) );
		$this->assertFalse( \EDD\Checkout\Validator::has_block( $post_id, 'edd/checkout-personal-info' ) );
	}

	/**
	 * During AJAX (e.g. gateway switches) the page is identified by the posted
	 * current_page, with no post ID argument. Inner-block detection must work there too.
	 */
	public function test_has_block_detects_inner_block_via_ajax_current_page() {
		$post_id               = $this->factory->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '<!-- wp:edd/checkout --><!-- wp:edd/checkout-discount-form /--><!-- /wp:edd/checkout -->',
			)
		);
		$_POST['current_page'] = $post_id;
		add_filter( 'wp_doing_ajax', '__return_true' );

		$this->assertTrue( \EDD\Checkout\Validator::has_block( null, 'edd/checkout-discount-form' ) );

		unset( $_POST['current_page'] );
		remove_filter( 'wp_doing_ajax', '__return_true' );
	}

	/**
	 * A page can use a full-site-editing template (assigned via Page Attributes) that
	 * defines the checkout layout directly in the template rather than in the page's own
	 * content. WordPress exposes the resolved template's markup via the
	 * $_wp_current_template_content global while rendering that request, so has_block()
	 * must fall back to checking it when the page's own content doesn't have the block.
	 */
	public function test_has_block_detects_inner_block_in_current_template() {
		$post_id = $this->factory->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '<!-- wp:edd/checkout /-->',
			)
		);
		$this->go_to( get_permalink( $post_id ) );

		$GLOBALS['_wp_current_template_content'] = '<!-- wp:edd/checkout --><!-- wp:edd/checkout-payment-info /--><!-- /wp:edd/checkout -->';

		$this->assertTrue( \EDD\Checkout\Validator::has_block( null, 'edd/checkout-payment-info' ) );
	}

	public function test_has_block_returns_false_when_absent_from_current_template() {
		$post_id = $this->factory->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '<!-- wp:edd/checkout /-->',
			)
		);
		$this->go_to( get_permalink( $post_id ) );

		$GLOBALS['_wp_current_template_content'] = '<!-- wp:edd/checkout --><!-- wp:edd/checkout-payment-info /--><!-- /wp:edd/checkout -->';

		$this->assertFalse( \EDD\Checkout\Validator::has_block( null, 'edd/checkout-personal-info' ) );
	}

	/**
	 * An explicit post ID is an unambiguous request to check that specific post, so it
	 * must take priority over the current-template fallback rather than being combined
	 * with it.
	 */
	public function test_has_block_explicit_post_id_ignores_current_template() {
		$post_id = $this->factory->post->create(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '<!-- wp:edd/checkout /-->',
			)
		);

		$GLOBALS['_wp_current_template_content'] = '<!-- wp:edd/checkout --><!-- wp:edd/checkout-payment-info /--><!-- /wp:edd/checkout -->';

		$this->assertFalse( \EDD\Checkout\Validator::has_block( $post_id, 'edd/checkout-payment-info' ) );
	}
}
