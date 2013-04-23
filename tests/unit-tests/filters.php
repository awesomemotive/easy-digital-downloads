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
		$this->assertArrayHasKey('edd_load_ajax_gateway', $wp_filter['wp_ajax_edd_load_gateway'][10]);
		$this->assertArrayHasKey('edd_load_ajax_gateway', $wp_filter['wp_ajax_nopriv_edd_load_gateway'][10]);
		$this->assertArrayHasKey('edd_print_errors', $wp_filter['edd_ajax_checkout_errors'][10]);
		$this->assertArrayHasKey('edd_process_purchase_form', $wp_filter['wp_ajax_edd_process_checkout'][10]);
		$this->assertArrayHasKey('edd_process_purchase_form', $wp_filter['wp_ajax_nopriv_edd_process_checkout'][10]);
	}

	public function testAfterDownloadContentFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_append_purchase_link', $wp_filter['edd_after_download_content'][10]);
		$this->assertArrayHasKey('edd_show_has_purchased_item_message', $wp_filter['edd_after_download_content'][10]);
		$this->assertArrayHasKey('edd_show_added_to_cart_messages', $wp_filter['edd_after_download_content'][10]);
	}

	public function testPurchaseLinkTopFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_purchase_variable_pricing', $wp_filter['edd_purchase_link_top'][10]);
	}

	public function testDownloadsExcerptFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_downloads_default_excerpt', $wp_filter['edd_downloads_excerpt'][10]);
	}

	public function testDownloadsContentFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_downloads_default_content', $wp_filter['edd_downloads_content'][10]);
	}

	public function testPurchaseFormFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_show_purchase_form', $wp_filter['edd_purchase_form'][10]);
	}

	public function testPurchaseFormAfterUserInfoFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_user_info_fields', $wp_filter['edd_purchase_form_after_user_info'][10]);
	}

	public function testCCFormFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_get_cc_form', $wp_filter['edd_cc_form'][10]);
	}

	public function testAfterCCFieldsFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_default_cc_address_fields', $wp_filter['edd_after_cc_fields'][10]);
	}

	public function testPurchaseFormRegisterFieldsFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_get_register_fields', $wp_filter['edd_purchase_form_register_fields'][10]);
	}

	public function testPurchaseFormLoginFieldsFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_get_login_fields', $wp_filter['edd_purchase_form_login_fields'][10]);
	}

	public function testPaymentModeSelectFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_payment_mode_select', $wp_filter['edd_payment_payment_mode_select'][10]);
	}

	public function testPurchaseFormBeforeCCFormFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_discount_field', $wp_filter['edd_purchase_form_before_cc_form'][10]);
	}

	public function testPurchaseFormAfterCCFormFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_terms_agreement', $wp_filter['edd_purchase_form_after_cc_form'][10]);
		$this->assertArrayHasKey('edd_checkout_submit', $wp_filter['edd_purchase_form_after_cc_form'][9999]);
	}

	public function testPurchaseFormBeforeSubmitFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_show_local_tax_opt_in', $wp_filter['edd_purchase_form_before_submit'][10]);
		$this->assertArrayHasKey('edd_print_errors', $wp_filter['edd_purchase_form_before_submit'][10]);
		$this->assertArrayHasKey('edd_checkout_final_total', $wp_filter['edd_purchase_form_before_submit'][999]);
	}

	public function testCheckoutFormTopFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_show_payment_icons', $wp_filter['edd_checkout_form_top'][10]);
		$this->assertArrayHasKey('edd_agree_to_terms_js', $wp_filter['edd_checkout_form_top'][10]);
	}

	public function testEmptyCartFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_empty_checkout_cart', $wp_filter['edd_empty_cart'][10]);
	}

	public function testAddToCartFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_process_add_to_cart', $wp_filter['edd_add_to_cart'][10]);
	}

	public function testRemoveFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_process_remove_from_cart', $wp_filter['edd_remove'][10]);
	}

	public function testPurchaseCollectionFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_process_collection_purchase', $wp_filter['edd_purchase_collection'][10]);
	}

	public function testFormatAmountDecimalsFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_currency_decimal_filter', $wp_filter['edd_format_amount_decimals'][10]);
	}

	public function testPayPalCCFormFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('__return_false', $wp_filter['edd_paypal_cc_form'][10]);
	}

	public function testGatewayPayPalFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_process_paypal_purchase', $wp_filter['edd_gateway_paypal'][10]);
	}

	public function testVerifyPayPalIPNFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_process_paypal_ipn', $wp_filter['edd_verify_paypal_ipn'][10]);
	}

	public function testPayPalWebAcceptFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_process_paypal_web_accept', $wp_filter['edd_paypal_web_accept'][10]);
	}

	public function testManualCCFormFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_manual_remove_cc_form', $wp_filter['edd_manual_cc_form'][10]);
	}

	public function testGatewayManualFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_manual_payment', $wp_filter['edd_gateway_manual'][10]);
	}

	public function testRemoveCartDiscountFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_remove_cart_discount', $wp_filter['edd_remove_cart_discount'][10]);
	}

	public function testCommentsClausesFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_hide_payment_notes', $wp_filter['comments_clauses'][10]);
	}

	public function testCommentFeedWhereFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_hide_payment_notes', $wp_filter['comments_clauses'][10]);
	}

	public function testUpdatePaymentStatusFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_complete_purchase', $wp_filter['edd_update_payment_status'][100]);
		$this->assertArrayHasKey('edd_record_status_change', $wp_filter['edd_update_payment_status'][100]);
		$this->assertArrayHasKey('edd_clear_user_history_cache', $wp_filter['edd_update_payment_status'][10]);
		$this->assertArrayHasKey('edd_trigger_purchase_receipt', $wp_filter['edd_update_payment_status'][10]);
	}

	public function testEditPaymentFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_update_edited_purchase', $wp_filter['edd_edit_payment'][10]);
	}

	public function testDeletePaymentFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_trigger_purchase_delete', $wp_filter['edd_delete_payment'][10]);
	}

	public function testInsertPaymentFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_clear_earnings_cache', $wp_filter['edd_insert_payment'][10]);
	}

	public function testUpgradePaymentsFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_update_old_payments_with_totals', $wp_filter['edd_upgrade_payments'][10]);
	}

	public function testCleanupFileSymlinksFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_cleanup_file_symlinks', $wp_filter['edd_cleanup_file_symlinks'][10]);
	}

	public function testDownloadPriceFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_format_amount', $wp_filter['edd_download_price'][10]);
		$this->assertArrayHasKey('edd_currency_filter', $wp_filter['edd_download_price'][20]);
	}

	public function testAdminHeadFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_admin_downloads_icon', $wp_filter['admin_head'][10]);
	}

	public function testEnterTitleHereFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_change_default_title', $wp_filter['enter_title_here'][10]);
	}

	public function testPostUpdatedMessagesFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_updated_messages', $wp_filter['post_updated_messages'][10]);
	}

	public function testLoadEditPHPFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_remove_post_types_order', $wp_filter['load-edit.php'][10]);
	}

	public function testSettingsMiscFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_append_no_cache_param', $wp_filter['edd_settings_misc'][-1]);
	}

	public function testAdminSaleNoticeFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_admin_email_notice', $wp_filter['edd_admin_sale_notice'][10]);
	}

	public function testPurchaseReceiptFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_email_default_formatting', $wp_filter['edd_purchase_receipt'][10]);
		$this->assertArrayHasKey('edd_apply_email_template', $wp_filter['edd_purchase_receipt'][20]);
	}

	public function testEmailSettingsFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_email_template_preview', $wp_filter['edd_email_settings'][10]);
	}

	public function testEmailTemplateDefaultFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_default_email_template', $wp_filter['edd_email_template_default'][10]);
	}

	public function testPurchaseReceiptDefaultFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_default_email_styling', $wp_filter['edd_purchase_receipt_default'][10]);
	}

	public function testViewReceiptFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_render_receipt_in_browser', $wp_filter['edd_view_receipt'][10]);
	}

	public function testEmailLinksFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_resend_purchase_receipt', $wp_filter['edd_email_links'][10]);
	}

	public function testSendEmailFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_send_test_email', $wp_filter['edd_send_test_email'][10]);
	}

	public function testQueryVarsFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_query_vars', $wp_filter['query_vars'][10]);
	}

	public function testPurchaseFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_process_purchase_form', $wp_filter['edd_purchase'][10]);
	}

	public function testUserLoginFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_process_login_form', $wp_filter['edd_user_login'][10]);
	}

	public function testEditUserProfileFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_process_profile_editor_updates', $wp_filter['edd_edit_user_profile'][10]);
	}

	public function testPostClassFilters() {
		global $wp_filter;
		$this->assertArrayHasKey('edd_responsive_download_post_class', $wp_filter['post_class'][999]);
	}
}