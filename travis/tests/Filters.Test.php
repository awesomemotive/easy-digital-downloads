<?php
/**
 * Test Filters
 */

class Test_Easy_Digital_Downloads_Filters extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function testTheContentFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_before_download_content', $wp_filter['the_content'][10]);
		$this->assertArrayHasKey('edd_after_download_content', $wp_filter['the_content'][10]);
		$this->assertArrayHasKey('edd_filter_success_page_content', $wp_filter['the_content'][10]);
		$this->assertArrayHasKey('edd_microdata_wrapper', $wp_filter['the_content'][10]);
		$this->assertArrayHasKey('edd_microdata_title', $wp_filter['the_title'][10]);
	}

	public function testWPHeadFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_version_in_header', $wp_filter['wp_head'][10]);
	}

	public function testTemplateRedirectFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_disable_jetpack_og_on_checkout', $wp_filter['template_redirect'][10]);
		$this->assertArrayHasKey('edd_block_attachments', $wp_filter['template_redirect'][10]);
		$this->assertArrayHasKey('edd_process_cart_endpoints', $wp_filter['template_redirect'][100]);
	}

	public function testInitFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_get_actions', $wp_filter['init'][10]);
		$this->assertArrayHasKey('edd_post_actions', $wp_filter['init'][10]);
		$this->assertArrayHasKey('edd_add_rewrite_endpoints', $wp_filter['init'][10]);
		$this->assertArrayHasKey('edd_no_gateway_error', $wp_filter['init'][10]);
		$this->assertArrayHasKey('edd_listen_for_paypal_ipn', $wp_filter['init'][10]);
		$this->assertArrayHasKey('edd_setup_download_taxonomies', $wp_filter['init'][10]);
		$this->assertArrayHasKey('edd_register_post_type_statuses', $wp_filter['init'][10]);
		$this->assertArrayHasKey('edd_setup_edd_post_types', $wp_filter['init'][100]);
		$this->assertArrayHasKey('edd_process_download', $wp_filter['init'][100]);
	}

	public function testAdminInitFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_register_settings', $wp_filter['admin_init'][10]);
		$this->assertArrayHasKey('edd_presstrends', $wp_filter['admin_init'][10]);
	}

	public function testDeletePostFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_remove_download_logs_on_delete', $wp_filter['delete_post'][10]);
	}

	public function testAdminEnqueueScriptsFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_load_admin_scripts', $wp_filter['admin_enqueue_scripts'][100]);
	}

	public function testUploadMimeFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_allowed_mime_types', $wp_filter['upload_mimes'][10]);
	}

	public function testWidgetsInitFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_register_widgets', $wp_filter['widgets_init'][10]);
	}

	public function testWPEnqueueScriptsFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_load_scripts', $wp_filter['wp_enqueue_scripts'][10]);
		$this->assertArrayHasKey('edd_register_styles', $wp_filter['wp_enqueue_scripts'][10]);
	}

	public function testAjaxFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_ajax_remove_from_cart', $wp_filter['wp_ajax_edd_remove_from_cart'][10]);
		$this->assertArrayHasKey('edd_ajax_remove_from_cart', $wp_filter['wp_ajax_nopriv_edd_remove_from_cart'][10]);
		$this->assertArrayHasKey('edd_ajax_add_to_cart', $wp_filter['wp_ajax_edd_add_to_cart'][10]);
		$this->assertArrayHasKey('edd_ajax_add_to_cart', $wp_filter['wp_ajax_nopriv_edd_add_to_cart'][10]);
		$this->assertArrayHasKey('edd_ajax_apply_discount', $wp_filter['wp_ajax_edd_apply_discount'][10]);
		$this->assertArrayHasKey('edd_ajax_apply_discount', $wp_filter['wp_ajax_nopriv_edd_apply_discount'][10]);
		$this->assertArrayHasKey('edd_load_checkout_login_fields', $wp_filter['wp_ajax_nopriv_checkout_login'][10]);
		$this->assertArrayHasKey('edd_load_checkout_register_fields', $wp_filter['wp_ajax_nopriv_checkout_register'][10]);
		$this->assertArrayHasKey('edd_ajax_get_download_title', $wp_filter['wp_ajax_edd_get_download_title'][10]);
		$this->assertArrayHasKey('edd_ajax_get_download_title', $wp_filter['wp_ajax_nopriv_edd_get_download_title'][10]);
		$this->assertArrayHasKey('edd_ajax_opt_into_local_taxes', $wp_filter['wp_ajax_edd_local_tax_opt_in'][10]);
		$this->assertArrayHasKey('edd_ajax_opt_into_local_taxes', $wp_filter['wp_ajax_nopriv_edd_local_tax_opt_in'][10]);
		$this->assertArrayHasKey('edd_ajax_opt_out_local_taxes', $wp_filter['wp_ajax_edd_local_tax_opt_out'][10]);
		$this->assertArrayHasKey('edd_ajax_opt_out_local_taxes', $wp_filter['wp_ajax_nopriv_edd_local_tax_opt_out'][10]);
		$this->assertArrayHasKey('edd_check_for_download_price_variations', $wp_filter['wp_ajax_edd_check_for_download_price_variations'][10]);
	}
}