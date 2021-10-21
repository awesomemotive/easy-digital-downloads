<?php
/**
 * Notification.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\Models;

class Notification {

	public $id;

	public $remote_id = null;

	public $title;

	public $content;

	public $type;

	public $start = null;

	public $end = null;

	public $dismissed = 0;

	public $date_created;

	public $date_updated;

	public function __construct( $data = array() ) {

	}

}
