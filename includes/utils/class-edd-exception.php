<?php
/**
 * Namespaced exception object for EDD
 *
 * @package     EDD
 * @subpackage  Classes/Utilities/Exceptions
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Utils;

/**
 * Implements a namespaced EDD-specific exception object.
 *
 * Implements the EDD_Exception marker interface to make it easier to catch
 * EDD-specific exceptions under one umbrella.
 *
 * @since 3.0
 *
 * @see \Exception
 * @see \EDD_Exception
 */
class Exception extends \Exception implements \EDD_Exception {}
