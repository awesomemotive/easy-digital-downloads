<?php
/**
 * Session Object
 *
 * @package     EDD
 * @subpackage  Classes/Sessions
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Sessions;

use EDD\Base_Object;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Session Class.
 *
 * @since 3.0
 *
 * @property int $id
 * @property string $hash
 * @property string $content
 * @property string $date_created
 * @property string $date_modified
 * @property string $date_expires
 */
class Session extends Base_Object {

}