<?php
/**
 * Emails
 *
 * This class handles all emails sent through EDD
 *
 * @package     EDD
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.1.php GNU Public License
 * @since       2.1
 *
 * @deprecated 3.2.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// This class has been moved to EDD\Emails\Base, and an alias has been put in place.
class_alias( 'EDD\Emails\Base', 'EDD_Emails' );
