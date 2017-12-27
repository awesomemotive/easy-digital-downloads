<?php
/**
 * Tests for the Registry API.
 *
 * @group edd_registry
 * @group edd_utils
 */
class Tests extends EDD_UnitTestCase {

	/**
	 * Mock registry test fixture.
	 *
	 * @access protected
	 * @var    \EDD_Registry
	 */
	protected $mockRegistry;

	/**
	 * Set up fixtures once.
	 */
	public function setUp() {
		parent::setUp();

		$this->mockRegistry = $this->getMockForAbstractClass( 'EDD_Registry' );
	}

	/**
	 * Runs after each test to reset the items array.
	 *
	 * @access public
	 */
	public function tearDown() {
		$this->mockRegistry->_reset_items();

		parent::tearDown();
	}

	/**
	 * @covers \EDD_Registry::register_item()
	 */
	public function test_register_item_should_register_the_item() {
		$this->mockRegistry->add_item( 'foobar', array(
			'class' => 'Foo\Bar',
			'file'  => 'path/to/foobar.php'
		) );

		$this->assertArrayHasKey( 'foobar', $this->mockRegistry->get_items() );
	}

	/**
	 * @covers \EDD_Registry::get_items()
	 */
	public function test_get_items_should_be_empty_with_no_registered_items() {
		$this->assertEqualSets( array(), $this->mockRegistry->get_items() );
	}

	/**
	 * @covers \EDD_Registry::get_items()
	 */
	public function test_get_items_should_return_registered_items() {
		$item = array(
			'foobar' => array(
				'class' => 'Foo\Bar',
				'file'  => 'path/to/foobar.php'
			)
		);

		// Add a item.
		$this->mockRegistry->add_item( 'foobar', array(
			'class' => 'Foo\Bar',
			'file'  => 'path/to/foobar.php'
		) );

		// Confirm the item is retrieved.
		$this->assertEqualSets( $item, $this->mockRegistry->get_items() );
	}
}
