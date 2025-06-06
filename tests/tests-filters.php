<?php
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * @group edd_filters
 */
class Tests_Filters extends EDD_UnitTestCase {

	public function test_the_content() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_before_download_content', $wp_filter['the_content'][10] );
		$this->assertArrayHasKey( 'edd_after_download_content', $wp_filter['the_content'][10] );
		$this->assertArrayHasKey( 'edd_filter_success_page_content', $wp_filter['the_content'][99999] );
	}

	public function test_wp_head() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_version_in_header', $wp_filter['wp_head'][10] );
	}

	public function test_template_redirect() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_disable_jetpack_og_on_checkout', $wp_filter['template_redirect'][10] );
		$this->assertArrayHasKey( 'edd_block_attachments', $wp_filter['template_redirect'][10] );
		$this->assertArrayHasKey( 'edd_process_cart_endpoints', $wp_filter['template_redirect'][100] );
	}

	public function test_init() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_get_actions', $wp_filter['init'][10] );
		$this->assertArrayHasKey( 'edd_post_actions', $wp_filter['init'][10] );
		$this->assertArrayHasKey( 'edd_add_rewrite_endpoints', $wp_filter['init'][10] );
		$this->assertArrayHasKey( 'edd_no_gateway_error', $wp_filter['edd_before_checkout_cart'][5] );
		$this->assertArrayHasKey( 'edd_listen_for_paypal_ipn', $wp_filter['init'][10] );
		$this->assertArrayHasKey( 'edd_setup_download_taxonomies', $wp_filter['init'][0] );
		$this->assertArrayHasKey( 'edd_setup_edd_post_types', $wp_filter['init'][1] );
		$this->assertArrayHasKey( 'edd_process_download', $wp_filter['init'][100] );
	}

	public function test_admin_init() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_register_settings', $wp_filter['admin_init'][10] );
	}

	public function test_delete_post() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_remove_download_logs_on_delete', $wp_filter['delete_post'][10] );
	}

	public function test_admin_enqueue_scripts() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_enqueue_admin_scripts', $wp_filter['admin_enqueue_scripts'][10] );
	}

	public function test_admin_enqueue_styles() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_enqueue_admin_styles', $wp_filter['admin_enqueue_scripts'][10] );
	}

	public function test_upload_mimes() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_allowed_mime_types', $wp_filter['upload_mimes'][10] );
	}

	public function test_widgets_init() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_register_widgets', $wp_filter['widgets_init'][10] );
	}

	public function test_wp_enqueue_scripts() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_load_scripts',   $wp_filter['wp_enqueue_scripts'][10] );
		$this->assertArrayHasKey( 'edd_enqueue_styles', $wp_filter['wp_enqueue_scripts'][10] );
	}

	public function test_ajax() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_ajax_remove_from_cart', $wp_filter['wp_ajax_edd_remove_from_cart'][10] );
		$this->assertArrayHasKey( 'edd_ajax_remove_from_cart', $wp_filter['wp_ajax_nopriv_edd_remove_from_cart'][10] );
		$this->assertArrayHasKey( 'edd_ajax_add_to_cart', $wp_filter['wp_ajax_edd_add_to_cart'][10] );
		$this->assertArrayHasKey( 'edd_ajax_add_to_cart', $wp_filter['wp_ajax_nopriv_edd_add_to_cart'][10] );
		$this->assertArrayHasKey( 'edd_ajax_apply_discount', $wp_filter['wp_ajax_edd_apply_discount'][10] );
		$this->assertArrayHasKey( 'edd_ajax_apply_discount', $wp_filter['wp_ajax_nopriv_edd_apply_discount'][10] );
		$this->assertArrayHasKey( 'edd_load_checkout_login_fields', $wp_filter['wp_ajax_nopriv_checkout_login'][10] );
		$this->assertArrayHasKey( 'edd_load_checkout_register_fields', $wp_filter['wp_ajax_nopriv_checkout_register'][10] );
		$this->assertArrayHasKey( 'edd_ajax_get_download_title', $wp_filter['wp_ajax_edd_get_download_title'][10] );
		$this->assertArrayHasKey( 'edd_ajax_get_download_title', $wp_filter['wp_ajax_nopriv_edd_get_download_title'][10] );
		$this->assertArrayHasKey( 'edd_check_for_download_price_variations', $wp_filter['wp_ajax_edd_check_for_download_price_variations'][10] );
		$this->assertArrayHasKey( 'edd_load_ajax_gateway', $wp_filter['wp_ajax_edd_load_gateway'][10] );
		$this->assertArrayHasKey( 'edd_load_ajax_gateway', $wp_filter['wp_ajax_nopriv_edd_load_gateway'][10] );
		$this->assertArrayHasKey( 'edd_print_errors', $wp_filter['edd_ajax_checkout_errors'][10] );
		$this->assertArrayHasKey( 'edd_process_purchase_form', $wp_filter['wp_ajax_edd_process_checkout'][10] );
		$this->assertArrayHasKey( 'edd_process_purchase_form', $wp_filter['wp_ajax_nopriv_edd_process_checkout'][10] );
	}

	public function test_edd_after_download_content() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_append_purchase_link', $wp_filter['edd_after_download_content'][10] );
		$this->assertArrayHasKey( 'edd_show_added_to_cart_messages', $wp_filter['edd_after_download_content'][10] );
	}

	public function test_edd_purchase_link_top() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_purchase_variable_pricing', $wp_filter['edd_purchase_link_top'][10] );
		$this->assertArrayHasKey( 'edd_download_purchase_form_quantity_field', $wp_filter['edd_purchase_link_top'][10] );
	}

	public function test_edd_after_price_option() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_variable_price_quantity_field', $wp_filter['edd_after_price_option'][10] );
	}

	public function test_edd_downloads_excerpt() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_downloads_default_excerpt', $wp_filter['edd_downloads_excerpt'][10] );
	}

	public function test_edd_downloads_content() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_downloads_default_content', $wp_filter['edd_downloads_content'][10] );
	}

	public function test_edd_purchase_form() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_show_purchase_form', $wp_filter['edd_purchase_form'][10] );
	}

	public function test_edd_purchase_form_after_user_info() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_user_info_fields', $wp_filter['edd_purchase_form_after_user_info'][10] );
	}

	public function test_edd_cc_form() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_get_cc_form', $wp_filter['edd_cc_form'][10] );
	}

	public function test_edd_after_cc_fields() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_default_cc_address_fields', $wp_filter['edd_after_cc_fields'][10] );
	}

	public function test_edd_purchase_form_register_fields() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_get_register_fields', $wp_filter['edd_purchase_form_register_fields'][10] );
	}

	public function test_edd_purchase_form_login_fields() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_get_login_fields', $wp_filter['edd_purchase_form_login_fields'][10] );
	}

	public function test_edd_payment_mode_select() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_payment_mode_select', $wp_filter['edd_payment_mode_select'][10] );
	}

	public function test_edd_purchase_form_before_cc_form() {
		global $wp_filter;
		// No actions connected to edd_purchase_form_before_cc_form by default
		$this->assertTrue( true );
		//$this->assertArrayHasKey( 'edd_discount_field', $wp_filter['edd_purchase_form_before_cc_form'][10] );
	}

	public function test_edd_purchase_form_after_cc_form() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_checkout_tax_fields', $wp_filter['edd_purchase_form_after_cc_form'][999] );
		$this->assertArrayHasKey( 'edd_checkout_submit', $wp_filter['edd_purchase_form_after_cc_form'][9999] );
	}

	public function test_edd_purchase_form_before_submit() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_print_errors', $wp_filter['edd_purchase_form_before_submit'][10] );
		$this->assertArrayHasKey( 'edd_checkout_final_total', $wp_filter['edd_purchase_form_before_submit'][999] );
	}

	public function test_edd_checkout_form_top() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_discount_field', $wp_filter['edd_checkout_form_top'][-1] );
		$this->assertArrayHasKey( 'edd_show_payment_icons', $wp_filter['edd_checkout_form_top'][10] );
	}

	public function test_edd_empty_cart() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_empty_checkout_cart', $wp_filter['edd_cart_empty'][10] );
	}

	public function test_edd_add_to_cart() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_process_add_to_cart', $wp_filter['edd_add_to_cart'][10] );
	}

	public function test_edd_remove() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_process_remove_from_cart', $wp_filter['edd_remove'][10] );
	}

	public function test_edd_purchase_collection() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_process_collection_purchase', $wp_filter['edd_purchase_collection'][10] );
	}

	public function test_edd_format_amount_decimals() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_currency_decimal_filter', $wp_filter['edd_format_amount_decimals'][10] );
	}

	public function test_edd_paypal_cc_form() {
		global $wp_filter;
		$this->assertArrayHasKey( '__return_false', $wp_filter['edd_paypal_cc_form'][10] );
	}

	public function test_edd_gateway_paypal() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_process_paypal_purchase', $wp_filter['edd_gateway_paypal'][10] );
	}

	public function test_edd_verify_paypal_ipn() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_process_paypal_ipn', $wp_filter['edd_verify_paypal_ipn'][10] );
	}

	public function test_edd_paypal_web_accept() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_process_paypal_web_accept_and_cart', $wp_filter['edd_paypal_web_accept'][10] );
	}

	public function test_edd_paypal_link_transaction_id() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_paypal_link_transaction_id', $wp_filter['edd_payment_details_transaction_id-paypal'][10] );
	}

	public function test_edd_manual_cc_form() {
		global $wp_filter;
		$this->assertArrayHasKey( '__return_false', $wp_filter['edd_manual_cc_form'][10] );
	}

	public function test_edd_gateway_manual() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_manual_payment', $wp_filter['edd_gateway_manual'][10] );
	}

	public function test_edd_remove_cart_discount() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_remove_cart_discount', $wp_filter['edd_remove_cart_discount'][10] );
	}

	public function test_edd_update_payment_status() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_complete_purchase', $wp_filter['edd_update_payment_status'][100] );
		$this->assertArrayHasKey( 'edd_record_order_status_change', $wp_filter['edd_transition_order_status'][100] );
	}

	public function test_edd_cleanup_file_symlinks() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_cleanup_file_symlinks', $wp_filter['edd_cleanup_file_symlinks'][10] );
	}

	public function test_edd_download_price() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_format_amount', $wp_filter['edd_download_price'][10] );
		$this->assertArrayHasKey( 'edd_currency_filter', $wp_filter['edd_download_price'][20] );
	}

	public function test_admin_head() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_admin_downloads_icon', $wp_filter['admin_head'][10] );
	}

	public function test_enter_title_here() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_change_default_title', $wp_filter['enter_title_here'][10] );
	}

	public function test_post_updated_messages() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_updated_messages', $wp_filter['post_updated_messages'][10] );
	}

	public function test_bulk_post_updated_messages() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_bulk_updated_messages', $wp_filter['bulk_post_updated_messages'][10] );
	}

	public function test_load_edit_php() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_remove_post_types_order', $wp_filter['load-edit.php'][10] );
	}

	public function test_edd_settings_misc() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_append_no_cache_param', $wp_filter['edd_settings_misc'][-1] );
	}

	public function test_edd_view_receipt() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_render_receipt_in_browser', $wp_filter['edd_view_receipt'][10] );
	}

	public function test_edd_purchase() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_process_purchase_form', $wp_filter['edd_purchase'][10] );
	}

	public function test_edd_user_login() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_process_login_form', $wp_filter['edd_user_login'][10] );
	}

	public function test_edd_edit_user_profile() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_process_profile_editor_updates', $wp_filter['edd_edit_user_profile'][10] );
	}

	public function test_post_class() {
		global $wp_filter;
		$this->assertArrayHasKey( 'edd_responsive_download_post_class', $wp_filter['post_class'][999] );
	}

}
