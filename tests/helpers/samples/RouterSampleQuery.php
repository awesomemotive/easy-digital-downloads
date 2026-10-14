<?php
/**
 * A stand-in extension query, used to prove the action blocklist follows the component registry.
 *
 * @package     EDD\Tests\Helpers\Samples
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.2
 */

namespace EDD\Tests\Helpers\Samples;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Database\Query;

class RouterSampleQuery extends Query {

	/**
	 * Name of the database table to query.
	 *
	 * @var string
	 */
	protected $table_name = 'router_samples';

	/**
	 * String used to alias the database table in MySQL statement.
	 *
	 * @var string
	 */
	protected $table_alias = 'rs';

	/**
	 * Borrowed so the sample has a real transition column, `status`.
	 *
	 * @var string
	 */
	protected $table_schema = '\\EDD\\Database\\Schemas\\Orders';

	/**
	 * Name for a single item.
	 *
	 * @var string
	 */
	protected $item_name = 'router_sample';

	/**
	 * Plural version for a group of items.
	 *
	 * @var string
	 */
	protected $item_name_plural = 'router_samples';
}
