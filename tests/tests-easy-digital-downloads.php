<?php


class Tests_EDD extends WP_UnitTestCase {
	protected $object;

	public function setUp() {
		parent::setUp();
		$this->object = EDD();
	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_edd_instance() {
		$this->assertClassHasStaticAttribute( 'instance', 'Easy_Digital_Downloads' );
	}

	/**
	 * @covers Easy_Digital_Downloads::setup_constants
	 */
	public function test_constants() {
		// Plugin Folder URL
		$path = str_replace( 'tests/', '', plugin_dir_url( __FILE__ ) );
		$this->assertSame( EDD_PLUGIN_URL, $path );

		// Plugin Folder Path
		$path = str_replace( 'tests/', '', plugin_dir_path( __FILE__ ) );
		$path = substr( $path, 0, -1 );
		$edd  = substr( EDD_PLUGIN_DIR, 0, -1 );
		$this->assertSame( $edd, $path );

		// Plugin Root File
		$path = str_replace( 'tests/', '', plugin_dir_path( __FILE__ ) );
		$this->assertSame( EDD_PLUGIN_FILE, $path.'easy-digital-downloads.php' );
	}

	/**
	 * @covers Easy_Digital_Downloads::includes
	 */
	public function test_includes() {
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/settings/register-settings.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/install.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/actions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/deprecated-functions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/ajax-functions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/template-functions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/checkout/template.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/checkout/functions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/cart/template.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/cart/functions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/cart/actions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/api/class-edd-api.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/api/class-edd-api-v1.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/class-edd-cache-helper.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/class-edd-fees.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/class-edd-html-elements.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/class-edd-logging.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/class-edd-session.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/class-edd-roles.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/class-edd-stats.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/formatting.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/widgets.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/mime-types.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/gateways/functions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/gateways/paypal-standard.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/gateways/manual.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/discount-functions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/payments/functions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/payments/actions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/payments/class-payment-stats.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/payments/class-payments-query.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/misc-functions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/download-functions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/scripts.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/post-types.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/plugin-compatibility.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/emails/functions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/emails/template.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/emails/actions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/error-tracking.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/user-functions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/query-filters.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/tax-functions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/process-purchase.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/login-register.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/add-ons.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/admin-actions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/class-edd-notices.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/admin-pages.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/dashboard-widgets.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/thickbox.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/upload-functions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/customers/class-customer-table.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/customers/customer-actions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/customers/customer-functions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/customers/customers.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/downloads/dashboard-columns.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/downloads/metabox.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/downloads/contextual-help.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/discounts/contextual-help.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/discounts/discount-actions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/discounts/discount-codes.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/payments/payments-history.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/payments/contextual-help.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/reporting/contextual-help.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/reporting/export/export-functions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/reporting/reports.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/reporting/pdf-reports.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/reporting/graphing.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/settings/display-settings.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/settings/contextual-help.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/upgrades/upgrades.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/class-edd-heartbeat.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/welcome.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/process-download.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/shortcodes.php' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'includes/theme-compatibility.php' );

        /** Check Assets Exist */
        $this->assertFileExists( EDD_PLUGIN_DIR . 'assets/css/chosen.css' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'assets/css/chosen.min.css' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/css/colorbox.css' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/css/edd-admin.css' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/css/jquery-ui-classic.css' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/css/jquery-ui-fresh.css' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'templates/fonts/padlock.eot' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'templates/fonts/padlock.svg' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'templates/fonts/padlock.ttf' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'templates/fonts/padlock.woff' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/colorbox/ie6/borderBottomCenter.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/colorbox/ie6/borderBottomLeft.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/colorbox/ie6/borderBottomRight.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/colorbox/ie6/borderMiddleLeft.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/colorbox/ie6/borderMiddleRight.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/colorbox/ie6/borderTopCenter.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/colorbox/ie6/borderTopLeft.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/colorbox/ie6/borderTopRight.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/colorbox/border.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/colorbox/controls.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/colorbox/loading.gif' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/colorbox/loading_background.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/colorbox/overlay.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'templates/images/icons/americanexpress.gif' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'templates/images/icons/discover.gif' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'templates/images/icons/iphone.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'templates/images/icons/mastercard.gif' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'templates/images/icons/paypal.gif' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'templates/images/icons/visa.gif' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/edd-cpt-2x.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/edd-cpt.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/edd-icon-2x.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/edd-icon.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/edd-logo.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/edd-media.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/loading.gif' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'templates/images/loading.gif' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/media-button.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'templates/images/tick.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/ui-icons_21759b_256x240.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/ui-icons_333333_256x240.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/ui-icons_999999_256x240.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/ui-icons_cc0000_256x240.png' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/images/xit.gif' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'templates/images/xit.gif' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'assets/js/admin-scripts.js' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'assets/js/chosen.jquery.js' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'assets/js/chosen.jquery.min.js' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/js/edd-ajax.js' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/js/edd-checkout-global.js' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/js/jquery.colorbox-min.js' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/js/jquery.creditCardValidator.js' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/js/jquery.flot.js' );
		$this->assertFileExists( EDD_PLUGIN_DIR . 'assets/js/jquery.validate.min.js' );
	}
}
