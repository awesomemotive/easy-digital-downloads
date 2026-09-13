<?php
/**
 * Tests for EDD\Utils\Convert.
 *
 * @package   EDD\Tests\Utils
 * @copyright (c) 2026, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.7.1
 */

namespace EDD\Tests\Utils;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Utils\Convert as Utility;

/**
 * @coversDefaultClass \EDD\Utils\Convert
 */
class Convert extends EDD_UnitTestCase {

	/**
	 * Test snake_to_camel conversion.
	 *
	 * @dataProvider snake_to_camel_provider
	 *
	 * @param string $input    Input string in snake-case or kebab-case.
	 * @param string $expected Expected PascalCase/CamelCase output.
	 */
	public function test_snake_to_camel( $input, $expected ) {
		$this->assertSame( $expected, Utility::snake_to_camel( $input ) );
	}

	/**
	 * Data provider for test_snake_to_camel.
	 *
	 * @return array[]
	 */
	public function snake_to_camel_provider() {
		return array(
			'standard snake case'               => array( 'order_item', 'OrderItem' ),
			'single word'                       => array( 'payment', 'Payment' ),
			'kebab case with hyphens'           => array( 'easy-digital-downloads', 'EasyDigitalDownloads' ),
			'mixed hyphens and underscores'     => array( 'edd_payment-gateway_method', 'EddPaymentGatewayMethod' ),
			'alphanumeric string with numbers'  => array( 'item_1_details', 'Item1Details' ),
			'already pascal case'               => array( 'OrderItem', 'OrderItem' ),
			'single lowercase character'        => array( 'a', 'A' ),
			'empty string'                      => array( '', '' ),
			'multiple consecutive underscores'  => array( 'order__item', 'OrderItem' ),
		);
	}
}
