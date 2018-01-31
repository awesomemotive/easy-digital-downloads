<?php
namespace EDD\Utils;

/**
 * Tests for the Registry API.
 *
 * @group edd_registry
 * @group edd_utils
 *
 * @coversDefaultClass \EDD\Utils\Registry
 */
class Registry_Tests extends \EDD_UnitTestCase {

	/**
	 * Mock registry test fixture.
	 *
	 * @access protected
	 * @var    \EDD\Utils\Registry
	 */
	protected $mockRegistry;

	/**
	 * Set up fixtures once.
	 */
	public function setUp() {
		parent::setUp();

		$this->mockRegistry = $this->getMockForAbstractClass( '\EDD\Utils\Registry' );
	}

	/**
	 * Runs after each test to reset the items array.
	 *
	 * @access public
	 */
	public function tearDown() {
		$this->mockRegistry->exchangeArray( array() );

		parent::tearDown();
	}

	/**
	 * @covers ::add_item()
	 * @expectedException \EDD_Exception
	 */
	public function test_add_item_with_empty_attributes_should_return_false() {
		$this->assertFalse( $this->mockRegistry->add_item( 'foo', array() ) );
	}

	/**
	 * @covers ::add_item()
	 * @throws \EDD_Exception
	 */
	public function test_add_item_with_empty_attributes_should_throw_exception() {
		$this->setExpectedException( '\EDD_Exception', "The attributes were missing when attempting to add the 'foo' item." );

		$this->mockRegistry->add_item( 'foo', array() );
	}

	/**
	 * @covers ::add_item()
	 * @throws \EDD_Exception
	 */
	public function test_add_item_with_non_empty_attributes_should_return_true() {
		$result = $this->mockRegistry->add_item( 'foo', array( 'bar' ) );

		$this->assertTrue( $result );
	}

	/**
	 * @covers ::add_item()
	 * @throws \EDD_Exception
	 */
	public function test_add_item_should_register_the_item() {
		$this->mockRegistry->add_item( 'foobar', array(
			'class' => 'Foo\Bar',
			'file'  => 'path/to/foobar.php'
		) );

		$this->assertArrayHasKey( 'foobar', $this->mockRegistry->get_items() );
	}

	/**
	 * @covers ::remove_item()
	 * @throws \EDD_Exception
	 */
	public function test_remove_item_with_invalid_item_id_should_effect_no_change() {
		$this->mockRegistry->add_item( 'foo', array( 'bar' ) );

		$this->mockRegistry->remove_item( 'bar' );

		$this->assertTrue( $this->mockRegistry->offsetExists( 'foo' ) );
	}

	/**
	 * @covers ::remove_item()
	 * @throws \EDD_Exception
	 */
	public function test_remove_item_with_valid_item_id_should_remove_that_item() {
		$this->mockRegistry->add_item( 'foo', array( 'bar' ) );

		$this->mockRegistry->remove_item( 'foo' );

		$this->assertFalse( $this->mockRegistry->offsetExists( 'foo' ) );
	}

	/**
	 * @covers ::get_item()
	 * @throws \EDD_Exception
	 */
	public function test_get_item_with_invalid_item_id_should_return_an_empty_array() {
		$this->setExpectedException( '\EDD_Exception', "The 'foo' item does not exist." );

		$result = $this->mockRegistry->get_item( 'foo' );

		$this->assertEqualSets( array(), $result );
	}

	/**
	 * @covers ::get_item()
	 * @throws \EDD_Exception
	 */
	public function test_get_item_with_invalid_item_id_should_throw_an_exception() {
		$this->setExpectedException( '\EDD_Exception', "The 'foo' item does not exist." );

		$this->mockRegistry->get_item( 'foo' );
	}

	/**
	 * @covers ::get_item()
	 * @throws \EDD_Exception
	 */
	public function test_get_item_with_valid_item_id_should_return_that_item() {
		$this->mockRegistry->add_item( 'foo', array( 'key' => 'value' ) );

		$expected = array(
			'key' => 'value'
		);

		$this->assertEqualSetsWithIndex( $expected, $this->mockRegistry->get_item( 'foo' ) );
	}

	/**
	 * @covers ::get_items()
	 */
	public function test_get_items_should_be_empty_with_no_registered_items() {
		$this->assertEqualSets( array(), $this->mockRegistry->get_items() );
	}

	/**
	 * @covers ::get_items()
	 * @throws \EDD_Exception
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
