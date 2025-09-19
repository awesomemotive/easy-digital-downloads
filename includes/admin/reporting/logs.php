<?php
/**
 * Logs UI (moved)
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 * @deprecated  3.0
*/

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

_edd_deprecated_file(
	__FILE__,
	'3.0',
	'includes/admin/tools/logs.php',
	__( 'The logs tab has been moved to the Tools screen.', 'easy-digital-downloads' )
);

require_once EDD_PLUGIN_DIR . 'includes/admin/tools/logs.php';
