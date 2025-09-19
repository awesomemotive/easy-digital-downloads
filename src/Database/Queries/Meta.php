<?php
/**
 * Base Custom Database Table Meta Query Class.
 *
 * @package     Database
 * @subpackage  Meta
 * @copyright   Copyright (c) 2020
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.1.0
 */
namespace EDD\Database\Queries;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// @todo Remove the need for this dependency
use \WP_Meta_Query;

/**
 * Class for generating SQL clauses that filter a primary query according to meta.
 *
 * It currently extends the WP_Meta_Query class in WordPress, but in the future
 * will be derived completely from other registered tables.
 *
 * @since 1.1.0
 */
class Meta extends WP_Meta_Query {

}
