<?php

/**
 * @group plugin_compatibility
 */
class Tests_Plugin_Compatibility extends EDD_UnitTestCase {

	/**
	 * Test that the filter exists of the function.
	 *
	 * @since 2.3
	 */
	public function test_file_hooks() {

		$this->assertNotFalse( has_action( 'load-edit.php', 'edd_remove_post_types_order' ) );
		$this->assertNotFalse( has_action( 'template_redirect', 'edd_disable_jetpack_og_on_checkout' ) );
		$this->assertNotFalse( has_filter( 'edd_settings_misc', 'edd_append_no_cache_param' ) );
		$this->assertNotFalse( has_filter( 'edd_downloads_content', 'edd_qtranslate_content' ) );
		$this->assertNotFalse( has_filter( 'edd_downloads_excerpt', 'edd_qtranslate_content' ) );
		$this->assertNotFalse( has_action( 'template_redirect', 'edd_disable_woo_ssl_on_checkout' ) );
		$this->assertNotFalse( has_action( 'edd_email_send_before', 'edd_disable_mandrill_nl2br' ) );
		$this->assertNotFalse( has_action( 'template_redirect', 'edd_disable_404_redirected_redirect' ) );

	}

	/**
	 * Test that the 'CPTOrderPosts' filter is removed.
	 *
	 * @since 2.3
	 */
	public function test_remove_post_types_order() {

		edd_remove_post_types_order();
		$this->assertFalse( has_filter( 'posts_orderby', 'CPTOrderPosts' ) );

	}

	/**
	 * Test that the JetPack og tags are removed.
	 *
	 * @since 2.3
	 */
	public function test_disable_jetpack_og_on_checkout() {

		$this->go_to( get_permalink( edd_get_option( 'purchase_page' ) ) );
		edd_disable_jetpack_og_on_checkout();
		$this->assertFalse( has_action( 'wp_head', 'jetpack_og_tags' ) );

	}

	/**
	 * Test that the edd_is_caching_plugin_active() return false when no caching is installed.
	 *
	 * @since 2.3
	 */
	public function test_is_caching_plugin_active_false() {

		$this->assertFalse( edd_is_caching_plugin_active() );

	}

	/**
	 * Test that edd_is_chaching_plugin_active() return true when W3TC is active.
	 *
	 * @since 2.3
	 */
	public function test_is_caching_plugin_active_true() {

		define( 'W3TC', true );
		$this->assertTrue( edd_is_caching_plugin_active() );

	}

	/**
	 * Test that a extra setting is added when W3TC is activated.
	 *
	 * @since 2.3
	 */
	public function test_append_no_cache_param() {

		$settings = edd_append_no_cache_param( $settings = array() );

		$this->assertEquals( $settings, array( array(
			'id'   => 'no_cache_checkout',
			'name' => __('No Caching on Checkout?','easy-digital-downloads' ),
			'desc' => __('Check this box in order to append a ?nocache parameter to the checkout URL to prevent caching plugins from caching the page.','easy-digital-downloads' ),
			'type' => 'checkbox'
		) ) );

	}

	/**
	 * Test the qTranslate function.
	 *
	 * @since 2.3
	 */
	public function test_qtranslate_content() {

		define( 'QT_LANGUAGE', true );
		$content = edd_qtranslate_content( $content = 'This is some test content' );
		$this->assertEquals( $content, 'This is some test content' );

	}

	/**
	 * Test that the Woo SSL action is removed from the template_redirect hook.
	 *
	 * @since 2.3
	 */
	public function test_disable_woo_ssl_on_checkout() {

		$this->go_to( get_permalink( edd_get_option( 'purchase_page' ) ) );
		add_filter( 'edd_is_ssl_enforced', '__return_true' );

		edd_disable_woo_ssl_on_checkout();
		$this->assertFalse( has_action( 'template_redirect', array( 'WC_HTTPS', 'unforce_https_template_redirect' ) ) );

	}

	/**
	 * Test the Mandrill compatibility function.
	 *
	 * @since 2.3
	 */
	public function test_disable_mandrill_nl2br() {

		edd_disable_mandrill_nl2br();
		$this->assertNotFalse( has_action( 'mandrill_nl2br', '__return_false' ) );

	}

	/**
	 * Test that the edd_disable_404_redirected_redirect() functions returns when WBZ404_VERSION is not defined.
	 *
	 * @since 2.3
	 */
	public function test_disable_404_redirected_redirect_return() {

		$this->assertNull( edd_disable_404_redirected_redirect() );

	}

	/**
	 * Test the edd_disable_404_redirected_redirect function.
	 *
	 * @since 2.3
	 */
	public function test_disable_404_redirected_redirect() {

		$this->go_to( get_permalink( edd_get_option( 'success_page' ) ) );
		define( 'WBZ404_VERSION', '1.3.2' );
		edd_disable_404_redirected_redirect();

		$this->assertFalse( has_action( 'template_redirect', 'wbz404_process404' ) );

	}

	public function test_say_what_aliases() {

		global $wp_filter;
		$this->assertarrayHasKey( 'edd_say_what_domain_aliases', $wp_filter['say_what_domain_aliases'][10] );

		$say_what_aliases = apply_filters( 'say_what_domain_aliases', array() );
		$this->assertarrayHasKey( 'easy-digital-downloads', $say_what_aliases );
		$this->assertTrue( in_array( 'edd', $say_what_aliases['easy-digital-downloads'] ) );

	}


}

/**
 * Function required to test the qTranslate compatibility function.
 *
 * @since 2.3
 */
if ( ! function_exists( 'qtrans_useCurrentLanguageIfNotFoundShowAvailable' ) ) {
	function qtrans_useCurrentLanguageIfNotFoundShowAvailable( $content ) {
		return $content;
	}
}
