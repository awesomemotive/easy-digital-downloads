<?php
/**
 * Logs UI
 *
 * @package     EDD
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * NOTE: This file has been intentionally left in place to maintain backward compatibility.
 *
 * As of 3.6.4, all log viewing functionality has been moved to the PSR-4
 * autoloaded class structure at \EDD\Admin\Tools\Logs.
 *
 * This file remains to prevent fatal errors for any plugins, extensions, or custom code
 * that may be requiring or including this file directly.
 *
 * All previously defined functions in this file have been moved to:
 * - /includes/admin/admin-deprecated-functions.php (for deprecated functions)
 * - \EDD\Admin\Tools\Logs class (for active functionality)
 *
 * If you are a developer and need log functionality, please use:
 * - \EDD\Admin\Tools\Logs::get_default_views() instead of edd_log_default_views()
 *
 * @see \EDD\Admin\Tools\Logs
 * @see /includes/admin/admin-deprecated-functions.php
 */
