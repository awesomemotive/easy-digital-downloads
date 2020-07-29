<?php

/**
 * EDD class tests.
 *
 * @coversDefaultClass EDD
 */
class Tests_EDD extends EDD_UnitTestCase {
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
	 * @dataProvider _test_includes_dp
	 * @covers ::includes()
	 *
	 * @group edd_includes
	 */
	public function test_includes( $path_to_file ) {
		$this->assertFileExists( $path_to_file );
	}

	/**
	 * Data provider for test_includes().
	 */
	public function _test_includes_dp() {
		return array(
			array( EDD_PLUGIN_DIR . 'includes/admin/settings/register-settings.php' ),
			array( EDD_PLUGIN_DIR . 'includes/install.php' ),
			array( EDD_PLUGIN_DIR . 'includes/actions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/deprecated-functions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/ajax-functions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/template-functions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/checkout/template.php' ),
			array( EDD_PLUGIN_DIR . 'includes/checkout/functions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/cart/template.php' ),
			array( EDD_PLUGIN_DIR . 'includes/cart/functions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/cart/actions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/api/class-edd-api.php' ),
			array( EDD_PLUGIN_DIR . 'includes/api/class-edd-api-v1.php' ),
			array( EDD_PLUGIN_DIR . 'includes/class-edd-cache-helper.php' ),
			array( EDD_PLUGIN_DIR . 'includes/class-edd-fees.php' ),
			array( EDD_PLUGIN_DIR . 'includes/class-edd-html-elements.php' ),
			array( EDD_PLUGIN_DIR . 'includes/class-edd-logging.php' ),
			array( EDD_PLUGIN_DIR . 'includes/class-edd-session.php' ),
			array( EDD_PLUGIN_DIR . 'includes/class-edd-roles.php' ),
			array( EDD_PLUGIN_DIR . 'includes/class-edd-stats.php' ),
			array( EDD_PLUGIN_DIR . 'includes/class-utilities.php' ),
			array( EDD_PLUGIN_DIR . 'includes/formatting.php' ),
			array( EDD_PLUGIN_DIR . 'includes/widgets.php' ),
			array( EDD_PLUGIN_DIR . 'includes/mime-types.php' ),
			array( EDD_PLUGIN_DIR . 'includes/gateways/functions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/gateways/paypal-standard.php' ),
			array( EDD_PLUGIN_DIR . 'includes/gateways/manual.php' ),
			array( EDD_PLUGIN_DIR . 'includes/interface-edd-exception.php' ),
			array( EDD_PLUGIN_DIR . 'includes/discount-functions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/payments/functions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/payments/actions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/payments/class-payment-stats.php' ),
			array( EDD_PLUGIN_DIR . 'includes/payments/class-payments-query.php' ),
			array( EDD_PLUGIN_DIR . 'includes/misc-functions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/download-functions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/scripts.php' ),
			array( EDD_PLUGIN_DIR . 'includes/post-types.php' ),
			array( EDD_PLUGIN_DIR . 'includes/plugin-compatibility.php' ),
			array( EDD_PLUGIN_DIR . 'includes/reports/exceptions/class-invalid-parameter.php' ),
			array( EDD_PLUGIN_DIR . 'includes/emails/functions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/emails/template.php' ),
			array( EDD_PLUGIN_DIR . 'includes/emails/actions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/error-tracking.php' ),
			array( EDD_PLUGIN_DIR . 'includes/user-functions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/query-filters.php' ),
			array( EDD_PLUGIN_DIR . 'includes/tax-functions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/process-purchase.php' ),
			array( EDD_PLUGIN_DIR . 'includes/login-register.php' ),
			array( EDD_PLUGIN_DIR . 'includes/reports/class-init.php' ),
			array( EDD_PLUGIN_DIR . 'includes/utils/class-edd-exception.php' ),
			array( EDD_PLUGIN_DIR . 'includes/utils/class-registry.php' ),
			array( EDD_PLUGIN_DIR . 'includes/utils/interface-static-registry.php' ),
			array( EDD_PLUGIN_DIR . 'includes/utils/exceptions/class-attribute-not-found.php' ),
			array( EDD_PLUGIN_DIR . 'includes/utils/exceptions/class-invalid-argument.php' ),
			array( EDD_PLUGIN_DIR . 'includes/utils/exceptions/class-invalid-parameter.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/add-ons.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/admin-actions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/class-edd-notices.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/admin-pages.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/dashboard-widgets.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/thickbox.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/upload-functions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/customers/class-customer-table.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/customers/customer-actions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/customers/customer-functions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/customers/customers.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/downloads/dashboard-columns.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/downloads/metabox.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/downloads/contextual-help.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/discounts/contextual-help.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/discounts/discount-actions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/discounts/discount-codes.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/payments/payments-history.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/payments/contextual-help.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/reporting/contextual-help.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/reporting/export/export-functions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/reporting/reports.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/reporting/graphing.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/settings/display-settings.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/settings/contextual-help.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/upgrades/upgrades.php' ),
			array( EDD_PLUGIN_DIR . 'includes/admin/class-edd-heartbeat.php' ),
			array( EDD_PLUGIN_DIR . 'includes/process-download.php' ),
			array( EDD_PLUGIN_DIR . 'includes/shortcodes.php' ),
			array( EDD_PLUGIN_DIR . 'includes/theme-compatibility.php' ),
		);
	}

	/**
	 * @dataProvider _test_includes_assets_dp
	 * @covers ::includes()
	 *
	 * @group edd_includes
	 */
	public function test_includes_assets( $path_to_file ) {
		$this->assertFileExists( $path_to_file );
	}

	/**
	 * Data provider for test_includes_assets().
	 */
	public function _test_includes_assets_dp() {
		return array(
			array( EDD_PLUGIN_DIR . 'assets/css/chosen.css' ),
			array( EDD_PLUGIN_DIR . 'assets/css/chosen.min.css' ),
			array( EDD_PLUGIN_DIR . 'assets/css/edd-admin.css' ),
			array( EDD_PLUGIN_DIR . 'assets/images/edd-cpt-2x.png' ),
			array( EDD_PLUGIN_DIR . 'assets/images/edd-cpt.png' ),
			array( EDD_PLUGIN_DIR . 'assets/images/edd-icon-2x.png' ),
			array( EDD_PLUGIN_DIR . 'assets/images/edd-icon.png' ),
			array( EDD_PLUGIN_DIR . 'assets/images/edd-logo.png' ),
			array( EDD_PLUGIN_DIR . 'assets/images/edd-media.png' ),
			array( EDD_PLUGIN_DIR . 'assets/images/loading.gif' ),
			array( EDD_PLUGIN_DIR . 'templates/images/loading.gif' ),
			array( EDD_PLUGIN_DIR . 'assets/images/media-button.png' ),
			array( EDD_PLUGIN_DIR . 'templates/images/tick.png' ),
			array( EDD_PLUGIN_DIR . 'assets/images/xit.gif' ),
			array( EDD_PLUGIN_DIR . 'templates/images/xit.gif' ),
			array( EDD_PLUGIN_DIR . 'assets/js/edd-admin.js' ),
			array( EDD_PLUGIN_DIR . 'assets/js/edd-ajax.js' ),
			array( EDD_PLUGIN_DIR . 'assets/js/edd-checkout-global.js' ),
			array( EDD_PLUGIN_DIR . 'assets/js/vendor/chosen.jquery.min.js' ),
			array( EDD_PLUGIN_DIR . 'assets/js/vendor/jquery.creditcardvalidator.min.js' ),
			array( EDD_PLUGIN_DIR . 'assets/js/vendor/jquery.flot.min.js' ),

			// Cannot be in /vendor/ for back-compat :(
			array( EDD_PLUGIN_DIR . 'assets/js/jquery.validate.min.js' ),
		);
	}
}
