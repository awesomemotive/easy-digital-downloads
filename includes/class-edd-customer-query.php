<?php
/**
 * This file only exists to maintain backwards compatibility for anyone who may
 * have included it directly.
 *
 * If you are reading this, and are including this file directly in any of your
 * code, please consider not doing so, and interfacing with customer data via
 * the REST API or some other way.
 *
 * @package     EDD
 * @subpackage  Classes/Customer Query
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.8
 * @deprecated  3.0.0
 */

// Exit if accessed directly
if ( ! defined( 'EDD_PLUGIN_DIR' ) ) exit;

// These are here for backwards compatibility
require_once EDD_PLUGIN_DIR . 'includes/database/queries/class-wp-db-query.php';
require_once EDD_PLUGIN_DIR . 'includes/database/queries/class-edd-db-query.php';
require_once EDD_PLUGIN_DIR . 'includes/database/queries/class-edd-db-column.php';
require_once EDD_PLUGIN_DIR . 'includes/database/queries/class-edd-db-customer-query.php';
