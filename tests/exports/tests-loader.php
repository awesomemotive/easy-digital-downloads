<?php
/**
 * Tests for the core exporter definitions.
 *
 * @package   EDD\Tests\Exports
 * @copyright (c) 2026, Sandhills Development, LLC
 * @license https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.7.2
 */

namespace EDD\Tests\Exports;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Admin\Exports\Loader as ExportsLoader;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Core exporter definition tests.
 *
 * @since 3.7.2
 *
 * @group edd_exports
 */
class Loader extends EDD_UnitTestCase {

	/**
	 * PSR-4 autoloading maps the referenced name to a file path, so a class referenced in
	 * the wrong case is only found on a case-insensitive filesystem.
	 */
	public function test_exporter_classes_are_referenced_by_their_declared_names() {
		foreach ( $this->get_core_exporters() as $exporter_id => $args ) {
			$declared_name = ( new \ReflectionClass( $args['class'] ) )->getName();

			$this->assertSame( $declared_name, $args['class'], "The {$exporter_id} exporter references its class in the wrong case." );
		}
	}

	/**
	 * Reads the core exporter definitions without touching the shared registry.
	 *
	 * @return array
	 */
	private function get_core_exporters() {
		$method = new \ReflectionMethod( ExportsLoader::class, 'get_exporters' );
		$method->setAccessible( true );

		return $method->invoke( new ExportsLoader() );
	}
}
