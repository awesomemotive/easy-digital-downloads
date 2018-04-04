<?php
/**
 * A Base Backwards Compatibility Class.
 *
 * @package     EDD
 * @subpackage  Compat
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */
namespace EDD\Compat;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Base class.
 *
 * @since 3.0.0
 */
abstract class Base {

	/**
	 * Holds the component for which we are handling back-compat. There is a chance that two methods have the same name
	 * and need to be dispatched to completely other methods. When a new instance of Back_Compat is created, a component
	 * can be passed to the constructor which will allow __call() to dispatch to the correct methods.
	 *
	 * @since 3.0.0
	 * @access protected
	 * @var string
	 */
	protected $component;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Getter for component.
	 *
	 * @since 3.0.0
	 *
	 * @return string Component.
	 */
	public function get_component() {
		return $this->component;
	}

	/**
	 * Backwards compatibility hooks for component.
	 *
	 * @since 3.0.0
	 * @access protected
	 */
	abstract protected function hooks();
}