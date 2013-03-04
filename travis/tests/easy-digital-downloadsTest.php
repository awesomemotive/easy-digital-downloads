<?php
/**
 * ConfigTest Tests
 * Some basic tests
 */
class Easy_Digital_DownloadsTest extends WP_UnitTestCase {
    /**
     * @var EDD_Roles
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
	 * For some reason, setUp must be public (to think through later)
     */
    public function setUp()
    {
        $this->object = new EDD_Digital_Downloads;
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
     * @covers EDD_Digital_Downloads::setup_constants
     */
	 public function setup_constants()
	 {
		// At this point, since plugin is loaded these should be defined
		// Plugin version
		$this->assertSame(EDD_VERSION, '1.4.2' );

		// Plugin Folder URL
		$this->assertSame(EDD_PLUGIN_URL, plugin_dir_url( __FILE__ ) );

		// Plugin Folder Path
		$this->assertSame(EDD_PLUGIN_DIR, plugin_dir_path( __FILE__ ) );

		// Plugin Root File
		$this->assertSame(EDD_PLUGIN_FILE, __FILE__ );
	 }
}