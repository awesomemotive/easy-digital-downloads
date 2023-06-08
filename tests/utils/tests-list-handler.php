<?php
/**
 * tests-array-handler.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\Tests\Utils;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Utils\ListHandler;

/**
 * @coversDefaultClass \EDD\Utils\ListHandler
 */
class ListHandlerTests extends EDD_UnitTestCase {

	/**
	 * Sample array that matches EDD's variable pricing structure.
	 * @var array
	 */
	private $prices = array(
		array(
			'index'  => 1,
			'name'   => '1 Site',
			'amount' => 99,
		),
		array(
			'index'  => 2,
			'name'   => '2-5 Sites',
			'amount' => 199,
		),
		array(
			'index'  => 3,
			'name'   => '10 Sites',
			'amount' => 599,
		),
	);

	public function test_get_minimum_amount_key_is_0() {
		$list_handler = new ListHandler( $this->prices );
		$min          = $list_handler->search( 'amount' );

		$this->assertEquals( 0, $min );
	}

	public function test_get_minimum_amount_is_99() {
		$list_handler = new ListHandler( $this->prices );
		$min          = $list_handler->search( 'amount' );

		$this->assertEquals( 99, $this->prices[ $min ]['amount'] );
	}

	public function test_get_minimum_amount_min_defined_is_99() {
		$list_handler = new ListHandler( $this->prices );
		$min          = $list_handler->search( 'amount', 'min' );

		$this->assertEquals( 99, $this->prices[ $min ]['amount'] );
	}

	public function test_get_maximum_amount_key_is_2() {
		$list_handler = new ListHandler( $this->prices );
		$max          = $list_handler->search( 'amount', 'max' );

		$this->assertEquals( 2, $max );
	}

	public function test_get_maximum_amount_is_599() {
		$list_handler = new ListHandler( $this->prices );
		$max          = $list_handler->search( 'amount', 'max' );

		$this->assertEquals( 599, $this->prices[ $max ]['amount'] );
	}

	public function test_get_new_minimum_amount() {
		$prices       = $this->prices;
		$prices[]     = array(
			'index'  => 4,
			'name'   => 'Half a Site',
			'amount' => 19,
		);
		$list_handler = new ListHandler( $prices );
		$min          = $list_handler->search( 'amount' );

		$this->assertEquals( 19, $prices[ $min ]['amount'] );
	}

	public function test_get_new_maximum_amount() {
		$prices       = $this->prices;
		$prices[0]    = array(
			'index'  => 4,
			'name'   => 'Unlimited Sites',
			'amount' => 999,
		);
		$list_handler = new ListHandler( $prices );
		$max          = $list_handler->search( 'amount', 'max' );

		$this->assertEquals( 999, $prices[ $max ]['amount'] );
	}

	public function test_associative_array_key() {
		$array        = array(
			'one'   => 1,
			'two'   => 2,
			'three' => 3,
		);
		$list_handler = new ListHandler( $array );

		$this->assertEquals( 'two', $list_handler->search( 2 ) );
	}

	public function test_search_string_returns_false() {
		$list_handler = new ListHandler( 'not an array' );

		$this->assertFalse( $list_handler->search( 'anything' ) );
	}

	public function test_search_false_returns_false() {
		$list_handler = new ListHandler( false );

		$this->assertFalse( $list_handler->search( false ) );
	}

	public function test_search_nonexistent_key_returns_false() {
		$list_handler = new ListHandler( $this->prices );

		$this->assertFalse( $list_handler->search( 'nonexistent' ) );
	}
}
