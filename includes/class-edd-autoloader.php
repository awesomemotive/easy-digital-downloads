<?php
/**
 * Contains autoloading functionality.
 *
 * @package   Fragen\Autoloader
 * @author    Andy Fragen <andy@thefragens.com>
 * @license   GPL-2.0+
 * @link      http://github.com/afragen/autoloader
 * @copyright 2015 Andy Fragen
 */

namespace EDD;

/*
 * Exit if called directly.
 */
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'EDD\\Autoloader' ) ) {
	/**
	 * Class Autoloader
	 *
	 * To use with different plugins be sure to create a new namespace.
	 *
	 * @package   EDD\Autoloader
	 * @author    Andy Fragen <andy@thefragens.com>
	 */
	class Autoloader {
		/**
		 * Roots to scan when autoloading.
		 *
		 * @var array
		 */
		protected $roots = array();

		/**
		 * List of class names and locations in filesystem, for situations
		 * where they deviate from convention etc.
		 *
		 * @var array
		 */
		protected $map = array();


		/**
		 * Constructor.
		 *
		 * @access public
		 *
		 * @param array      $roots      Roots to scan when autoloading.
		 * @param array|null $static_map List of classes that deviate from convention. Defaults to null.
		 */
		public function __construct( array $roots, array $static_map = null ) {
			$this->roots = $roots;
			if ( null !== $static_map ) {
				$this->map = $static_map;
			}
			spl_autoload_register( array( $this, 'autoload' ) );
		}

		/**
		 * Load classes.
		 *
		 * @access protected
		 *
		 * @param string $class The class name to autoload.
		 *
		 * @return void
		 */
		protected function autoload( $class ) {
			// Check for a static mapping first of all.
			if ( isset( $this->map[ $class ] ) && file_exists( $this->map[ $class ] ) ) {
				include_once $this->map[ $class ];

				return;
			}

			// Else scan the namespace roots.
			foreach ( $this->roots as $namespace => $root_dir ) {
				// If the class doesn't belong to this namespace, move on to the next root.

				// kludge until proper namespacing of files.
				if ( false === strpos( '$class', $namespace ) ) {
					$class = 'EDD\\' . $class;
				}
				if ( 0 !== strpos( $class, $namespace ) ) {
					continue;
				}

				// Determine the possible path to the class, include all subdirectories.
				$objects = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $root_dir ), \RecursiveIteratorIterator::SELF_FIRST );
				foreach ( $objects as $name => $object ) {
					if ( is_dir( $name ) ) {
						$dirs[] = rtrim( $name, './' );
					}
				}
				$dirs = array_unique( $dirs );

				//$path = substr( $class, strlen( $namespace ) + 1 );
				//$path = str_replace( '\\', DIRECTORY_SEPARATOR, $path );

				$path    = str_replace( 'EDD\\', '', $class );
				$path    = str_replace( '_', '-', $path );
				$path    = strtolower( $path );
				$path    = str_replace( 'edd-', '', $path );
				$bases[] = 'class-' . $path;
				$bases[] = 'class-edd-' . $path;

				$misnamed = array(
					'class-edd-payement-stats.php',
					'class-edd-email-tags.php',
				);

				foreach ( $misnamed as $mis ) {
					$misnameds[] = array_map( function( $dir ) use ( $mis ) {
						return $dir . DIRECTORY_SEPARATOR . $mis;
					}, $dirs );
				}
				$misnameds = call_user_func_array( 'array_merge', $misnameds );

				foreach ( $bases as $base ) {
					$paths[] = array_map( function( $dir ) use ( $base ) {
						return $dir . DIRECTORY_SEPARATOR . $base . '.php';
					}, $dirs );
				}
				$paths = call_user_func_array( 'array_merge', $paths );
				$paths = array_merge( $paths, $misnameds );

				// Test for its existence and load if present.
				foreach ( $paths as $path ) {
					if ( file_exists( $path ) ) {
						include_once $path;
						break;
					}
				}
			}
		}
	}
}
