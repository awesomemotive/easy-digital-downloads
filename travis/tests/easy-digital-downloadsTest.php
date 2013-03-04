<?php
/**
 * ConfigTest Tests
 * Some basic tests
 */
class Easy_Digital_DownloadsTest extends WP_UnitTestCase
{
    /**
     * @var Easy_Digital_Downloads
     */
    protected $object;
    
    /**
     * @var Easy_Digital_Downloads The one true Easy_Digital_Downloads
     */
    private static $instance;
    
    /**
     * EDD user roles and capabilities object
     * @since 1.4.4
     * @var object
     */
    private $roles;
    
    /**
     * EDD cart fees object
     * @var object
     * @since 1.5
     */
    public $fees;
    
    /**
     * EDD API object
     * @since 1.5
     */
    public $api;
    
    
    /**
     * EDD HTML session object
     *
     * This holds cart items, purchase sessions, and anything else stored in the session
     *
     * @since 1.5
     */
    public $session;
    
    
    /**
     * EDD HTML Element helper object
     * @since 1.5
     */
    public $html;
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     * For some reason, setUp must be public (to think through later)
     */
    public function setUp()
    {
        $this->object = new Easy_Digital_Downloads;
        //$this->AaaaaaInit();
    }
    // lets try it this way
    protected function AaaaaaInit()
    {
        $this->object->setup_constants();
        $this->object->includes();
        $this->object->load_textdomain();
        $this->object->roles   = new EDD_Roles();
        $this->object->fees    = new EDD_Fees();
        $this->object->api     = new EDD_API();
        $this->object->session = new EDD_Session();
        $this->object->html    = new EDD_HTML_Elements();
    }
    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     * For some reason, tearDown must be public (to think through later)
     */
    public function tearDown()
    {
    }
    
    /**
     * @covers Easy_Digital_Downloads::setup_constants
     */
    public function testSetup_Constants()
    {
        // At this point, since plugin is loaded these should be defined
        // Plugin version
        $this->assertSame( EDD_VERSION, '1.5' );
        
        // Plugin Folder URL
        $path = str_replace( 'travis/tests/', '', plugin_dir_url( __FILE__ ) );
        $this->assertSame( EDD_PLUGIN_URL, $path );
        
        // Plugin Folder Path
        $path = str_replace( 'travis/tests/', '', plugin_dir_path( __FILE__ ) );
        $this->assertSame( EDD_PLUGIN_DIR, $path );
        
        // Plugin Root File
        $this->assertSame( EDD_PLUGIN_FILE, 'easy-digital-downloads.php' );
    }
    
    /**
     * @covers Easy_Digital_Downloads::includes
     */
    public function testIncludes()
    {
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
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/class-edd-api.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/class-edd-fees.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/class-edd-html-elements.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/class-edd-logging.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/class-edd-session.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/class-edd-roles.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/formatting.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/widgets.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/mime-types.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/gateways/functions.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/gateways/paypal-standard.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/gateways/manual.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/discount-functions.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/payments/functions.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/payments/actions.php' );
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
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/admin-notices.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/admin-pages.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/dashboard-widgets.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/export-functions.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/thickbox.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/upload-functions.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/downloads/dashboard-columns.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/downloads/metabox.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/downloads/contextual-help.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/discounts/contextual-help.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/discounts/discount-actions.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/discounts/discount-codes.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/payments/payments-history.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/payments/contextual-help.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/reporting/contextual-help.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/reporting/reports.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/reporting/pdf-reports.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/reporting/graphing.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/settings/display-settings.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/settings/contextual-help.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/upgrades/upgrades.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/admin/welcome.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/process-download.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/shortcodes.php' );
        $this->assertFileExists( EDD_PLUGIN_DIR . 'includes/theme-compatibility.php' );
    }
    
}