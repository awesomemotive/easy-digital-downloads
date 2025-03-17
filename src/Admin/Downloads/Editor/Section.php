<?php
/**
 * Section base class.
 *
 * @package   EDD\Admin\Downloads\Editor
 * @copyright Copyright (c) 2024, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.3.6
 */

namespace EDD\Admin\Downloads\Editor;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Admin\Sections\Section as Base;

/**
 * Section.
 *
 * @since 3.3.6
 */
abstract class Section extends Base {

	/**
	 * Whether the section is dynamic.
	 *
	 * @since 3.3.6
	 * @var bool
	 */
	protected $dynamic = false;

	/**
	 * Section requirement.
	 *
	 * @since 3.3.6
	 * @var string
	 */
	protected $requires;

	/**
	 * Section supports.
	 *
	 * @since 3.3.6
	 * @var array
	 */
	protected $supports;

	/**
	 * Get the section config.
	 *
	 * @since 3.3.6
	 * @return array
	 */
	public function get_config() {
		$config             = parent::get_config();
		$config['requires'] = $this->requires;
		$config['supports'] = $this->supports;

		return $config;
	}
}
