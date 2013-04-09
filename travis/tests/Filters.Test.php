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

	public function testAddToCartFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testRemoveFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testPurchaseCollectionFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testFormatAmountDecimalsFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testPayPalCCFormFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testGatewayPayPalFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testVerifyPayPalIPNFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testPayPalWebAcceptFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testManualCCFormFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testGatewayManualFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testRemoveCartDiscountFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testCommentsClausesFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testCommentFeedWhereFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testUpdatePaymentStatusFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testEditPaymentFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testDeletePaymentFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testInsertPaymentFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testUpgradePaymentsFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testCleanupFileSymlinksFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testDownloadPriceFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testAdminHeadFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testEnterTitleHereFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testPostUpdatedMessagesFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testLoadEditPHPFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testSettingsMiscFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testAdminSaleNoticeFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testPurchaseReceiptFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testEmailSettingsFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testEmailTemplateDefaultFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testPurchaseReceiptDefaultFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testViewReceiptFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testEmailLinksFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testSendEmailFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testQueryVarsFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testPurchaseFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testUserLoginFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testEditUserProfileFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testPostClassFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testPaymentMetaFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testShowUserProfileFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }

	public function testPersonalOptionsUpdateFilters() { $this->markTestIncomplete('This test has not been implemented yet.'); }
}