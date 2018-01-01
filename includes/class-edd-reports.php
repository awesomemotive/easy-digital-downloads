<?php
/**
 * Reports API
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

namespace EDD\Admin;

/**
 * Core class that implements the Reports API.
 *
 * The reports API is intentionally initialized outside of the admin-only constraint
 * to provide greater accessibility to core and extensions. As such, the potential
 * footprint for report tab and tile registrations is intentionally kept minimal.
 *
 * @since 3.0
 */
final class Reports {

	/**
	 * Sets up the Reports API.
	 *
	 * @since 3.0
	 */
	public function __construct() {

	}

}
