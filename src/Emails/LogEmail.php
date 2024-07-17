<?php

namespace EDD\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Database\Row;

/**
 * Class EmailLog
 *
 * @since 3.3.0
 * @package EDD\Emails
 */
class LogEmail extends Row {

	/**
	 * The email log ID.
	 *
	 * @since 3.3.0
	 * @var int
	 */
	protected $id;

	/**
	 * The email log object ID.
	 *
	 * @since 3.3.0
	 * @var int
	 */
	protected $object_id;

	/**
	 * The email log object type.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $object_type;

	/**
	 * The email log email ID.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $email_id;

	/**
	 * The email log subject.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $subject;

	/**
	 * The email log date created.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $date_created;

	/**
	 * The email log date modified.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $date_modified;

	/**
	 * The email log UUID.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	protected $uuid;
}
