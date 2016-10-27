<?php

/**
 * @group scripts
 */
class Tests_Scripts extends WP_UnitTestCase {

	/**
	 * Test if all the file hooks are working.
	 *
	 * @since 2.3.6
	 */
	public function test_file_hooks() {

		$this->assertNotFalse( has_action( 'wp_enqueue_scripts', 'edd_load_scripts' ) );
		$this->assertNotFalse( has_action( 'wp_enqueue_scripts', 'edd_register_styles' ) );
		$this->assertNotFalse( has_action( 'admin_enqueue_scripts', 'edd_load_admin_scripts' ) );
		$this->assertNotFalse( has_action( 'admin_head', 'edd_admin_downloads_icon' ) );

	}

	/**
	 * Test that all the scripts are loaded at the checkout page.
	 *
	 * @since 2.3.6
	 */
	public function test_load_scripts_checkout() {

		// Prepare test
		$this->go_to( get_permalink( edd_get_option( 'purchase_page' ) ) );
		edd_load_scripts();

		$this->assertTrue( wp_script_is( 'creditCardValidator', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'edd-checkout-global', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'edd-ajax', 'enqueued' ) );

		$this->go_to( '/' );

	}

	/**
	 * Test that the edd_register_styles() function will bail when the 'disable_styles'
	 * option is set to true.
	 *
	 * @since 2.3.6
	 */
	public function test_register_styles_bail_option() {

		// Prepare test
		$origin_disable_styles = edd_get_option( 'disable_styles', false );
		edd_update_option( 'disable_styles', true );

		// Assert
		$this->assertNull( edd_register_styles() );

		// Reset to origin
		edd_update_option( 'disable_styles', $origin_disable_styles );

	}

	/**
	 * Test that the edd_register_styles() function will enqueue the styles.
	 *
	 * @since 2.3.6
	 */
	public function test_register_styles() {

		edd_update_option( 'disable_styles', false );
		edd_register_styles();

		$this->assertTrue( wp_style_is( 'edd-styles', 'enqueued' ) );

	}

	/**
	 * Test that the edd_register_styles() function will enqueue the proper styles
	 * when page is checkout + ssl.
	 *
	 * @since 2.3.6
	 */
	public function test_register_styles_checkout_ssl() {

		// Prepare test
		$_SERVER['HTTPS'] = 'ON'; // Fake SSL
		$this->go_to( get_permalink( edd_get_option( 'purchase_page' ) ) );
		edd_update_option( 'disable_styles', false );
		edd_register_styles();

		$this->assertTrue( wp_style_is( 'dashicons', 'enqueued' ) );

		$this->go_to( '/' );
		unset( $_SERVER['HTTPS'] );

	}

	/**
	 * Test that the edd_load_admin_scripts() function will bail when not a EDD admin page.
	 *
	 * @since 2.3.6
	 */
	public function test_load_admin_scripts_bail() {

		// Prepare test
		global $pagenow;
		$origin_pagenow = $pagenow;
		$pagenow = 'dashboard';

		if ( ! function_exists( 'edd_is_admin_page' ) ) {
			include EDD_PLUGIN_DIR . 'includes/admin/admin-pages.php';
		}

		// Assert
		$this->assertNull( edd_load_admin_scripts( 'dashboard' ) );

		// Reset to origin
		$pagenow = $origin_pagenow;

	}

	/**
	 * Test that the edd_load_admin_scripts() function will enqueue the proper styles.
	 *
	 * @since 2.3.6
	 */
	public function test_load_admin_scripts() {

		if ( ! function_exists( 'edd_is_admin_page' ) ) {
			include EDD_PLUGIN_DIR . 'includes/admin/admin-pages.php';
		}

		edd_load_admin_scripts( 'settings.php' );

		$this->assertTrue( wp_style_is( 'jquery-chosen', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'wp-color-picker', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'colorbox', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'jquery-ui-css', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'thickbox', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'edd-admin', 'enqueued' ) );

		$this->assertTrue( wp_script_is( 'jquery-chosen', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'edd-admin-scripts', 'enqueued' ) );
// 		$this->assertTrue( wp_script_is( 'wp-color-picker', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'colorbox', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'jquery-ui-datepicker', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'jquery-ui-dialog', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'jquery-flot', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'media-upload', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'thickbox', 'enqueued' ) );

	}

}