<?php
/**
 * Tests for the Registry API.
 *
 * @group edd_registry
 * @group edd_utils
 */
class EDD_Registry_Tests extends EDD_UnitTestCase {

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
	 * @covers \EDD_Registry::init()
	 */
	public function test_class_should_have_an_init_method() {
		$this->assertTrue( method_exists( $this->mockRegistry, 'init' ) );
	}

	/**
	 * @covers \EDD_Registry::add_item()
	 * @expectedException EDD_Exception
	 */
	public function test_add_item_with_empty_attributes_should_return_false() {
		$this->assertFalse( $this->mockRegistry->add_item( 'foo', array() ) );
	}

	/**
	 * @covers \EDD_Registry::add_item()
	 */
	public function test_add_item_with_empty_attributes_should_throw_exception() {
		$this->setExpectedException( 'EDD_Exception', "The attributes were missing when attempting to add item 'foo'." );

		$this->mockRegistry->add_item( 'foo', array() );
	}

	/**
	 * @covers \EDD_Registry::add_item()
	 */
	public function test_add_item_with_non_empty_attributes_should_return_true() {
		$result = $this->mockRegistry->add_item( 'foo', array( 'bar' ) );

		$this->assertTrue( $result );
	}

	/**
	 * @covers \EDD_Registry::add_item()
	 */
	public function test_add_item_should_register_the_item() {
		$this->mockRegistry->add_item( 'foobar', array(
			'class' => 'Foo\Bar',
			'file'  => 'path/to/foobar.php'
		) );

		$this->assertArrayHasKey( 'foobar', $this->mockRegistry->get_items() );
	}

	/**
	 * @covers \EDD_Registry::remove_item()
	 */
	public function test_remove_item_with_invalid_item_id_should_effect_no_change() {
		$this->mockRegistry->add_item( 'foo', array( 'bar' ) );

		$this->mockRegistry->remove_item( 'bar' );

		$this->assertTrue( $this->mockRegistry->offsetExists( 'foo' ) );
	}

	/**
	 * @covers \EDD_Registry::remove_item()
	 */
	public function test_remove_item_with_valid_item_id_should_remove_that_item() {
		$this->mockRegistry->add_item( 'foo', array( 'bar' ) );

		$this->mockRegistry->remove_item( 'foo' );

		$this->assertFalse( $this->mockRegistry->offsetExists( 'foo' ) );
	}

	/**
	 * @covers \EDD_Registry::get_item()
	 */
	public function test_get_item_with_invalid_item_id_should_return_an_empty_array() {
		$this->setExpectedException( 'EDD_Exception', "The item 'foo' does not exist." );

		$result = $this->mockRegistry->get_item( 'foo' );

		$this->assertEqualSets( array(), $result );
	}

	/**
	 * @covers \EDD_Registry::get_item()
	 */
	public function test_get_item_with_invalid_item_id_should_throw_an_exception() {
		$this->setExpectedException( 'EDD_Exception', "The item 'foo' does not exist." );

		$this->mockRegistry->get_item( 'foo' );
	}

	/**
	 * @covers \EDD_Registry::get_item()
	 */
	public function test_get_item_with_valid_item_id_should_return_that_item() {
		$this->mockRegistry->add_item( 'foo', array( 'key' => 'value' ) );

		$expected = array(
			'key' => 'value'
		);

		$this->assertEqualSetsWithIndex( $expected, $this->mockRegistry->get_item( 'foo' ) );
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

	/**
	 * @covers \EDD_Registry::_reset_items()
	 */
	public function test__reset_items_should_reset_items() {
		// Add an item.
		$this->mockRegistry->add_item( 'foo', array( 'key' => 'value' ) );

		// Confirm it's there.
		$this->assertArrayHasKey( 'foo', $this->mockRegistry->get_items() );

		// Reset the registry.
		$this->mockRegistry->_reset_items();

		// Confirm it's now empty.
		$this->assertEqualSets( array(), $this->mockRegistry->get_items() );
	}

	/**
	 * @covers \EDD_Registry::offsetExists()
	 */
	public function test_offsetExists_should_return_fakse_if_the_item_does_not_exist() {
		$this->assertFalse( $this->mockRegistry->offsetExists( 'foo' ) );
	}

	/**
	 * @covers \EDD_Registry::offsetExists()
	 */
	public function test_offsetExists_should_return_true_if_the_item_exists() {
		$this->mockRegistry->add_item( 'foo', array( 'key' => 'value' ) );

		$this->assertTrue( $this->mockRegistry->offsetExists( 'foo' ) );
	}

	/**
	 * @covers \EDD_Registry::offsetGet()
	 */
	public function test_offsetGet_with_invalid_item_id_should_return_an_empty_array() {
		$result = $this->mockRegistry->offsetGet( 'foo' );

		$this->assertEqualSets( array(), $result );
	}

	/**
	 * @covers \EDD_Registry::offsetGet()
	 */
	public function test_offsetGet_with_valid_item_id_should_return_that_item() {
		try {
			$this->mockRegistry->add_item( 'foo', array( 'key' => 'value' ) );
		} catch( EDD_Exception $e ) {}

		$this->assertEqualSetsWithIndex( array( 'key' => 'value' ), $this->mockRegistry->offsetGet( 'foo' ) );
	}

	/**
	 * @covers \EDD_Registry::offsetSet()
	 */
	public function test_offsetSet_with_empty_attributes_should_return_false() {
		$this->assertFalse( $this->mockRegistry->offsetSet( 'foo', array() ) );
	}

	/**
	 * @covers \EDD_Registry::offsetSet()
	 */
	public function test_offsetSet_with_non_empty_attributes_should_return_true() {
		$result = $this->mockRegistry->offsetSet( 'foo', array( 'bar' ) );

		$this->assertTrue( $result );
	}

	/**
	 * @covers \EDD_Registry::offsetSet()
	 */
	public function test_offsetSet_should_register_the_item() {
		$this->mockRegistry->offsetSet( 'foobar', array(
			'class' => 'Foo\Bar',
			'file'  => 'path/to/foobar.php'
		) );

		$this->assertArrayHasKey( 'foobar', $this->mockRegistry->get_items() );
	}

	/**
	 * @covers \EDD_Registry::offsetUnset()
	 */
	public function test_offsetUnset_with_invalid_item_id_should_effect_no_change() {
		$this->mockRegistry->add_item( 'foo', array( 'bar' ) );

		$this->mockRegistry->offsetUnset( 'bar' );

		$this->assertTrue( $this->mockRegistry->offsetExists( 'foo' ) );
	}

	/**
	 * @covers \EDD_Registry::offsetUnset()
	 */
	public function test_offsetUnset_with_valid_item_id_should_remove_that_item() {
		$this->mockRegistry->add_item( 'foo', array( 'bar' ) );

		$this->mockRegistry->offsetUnset( 'foo' );

		$this->assertFalse( $this->mockRegistry->offsetExists( 'foo' ) );
	}
}
