<?php
/**
 * Abstract metabox class.
 *
 * @package   EDD\Admin\Downloads\Metaboxes
 * @copyright Copyright (c) 2024, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.3.6
 */

namespace EDD\Admin\Downloads\Metaboxes;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Admin\Downloads\Editor\Section;

/**
 * Abstract Metabox class.
 */
abstract class Metabox {

	/**
	 * Metabox ID.
	 *
	 * @var string
	 */
	protected $id;

	/**
	 * Screen.
	 *
	 * @var string
	 */
	protected $screen = 'download';

	/**
	 * Context.
	 *
	 * @var string
	 */
	protected $context = 'advanced';

	/**
	 * Priority.
	 *
	 * @var string
	 */
	protected $priority = 'default';

	/**
	 * Download object.
	 *
	 * @var \EDD_Download|false
	 */
	protected $download;

	/**
	 * Gets the metabox title.
	 *
	 * @since 3.3.6
	 * @return string
	 */
	abstract public function get_title(): string;

	/**
	 * Renders the metabox.
	 *
	 * @since 3.3.6
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	abstract public function render( \WP_Post $post );

	/**
	 * Sets the download object.
	 *
	 * @since 3.3.6
	 * @param \EDD_Download $download Download object.
	 * @return void
	 */
	public function set_download( $download ) {
		$this->download = $download;
	}

	/**
	 * Gets the metabox configuration.
	 *
	 * @since 3.3.6
	 * @return array
	 */
	public function get_config() {
		return array(
			'id'            => $this->id,
			'title'         => $this->get_title(),
			'callback'      => $this->get_callback(),
			'screen'        => $this->get_screen(),
			'context'       => $this->context,
			'priority'      => $this->priority,
			'callback_args' => $this->get_callback_args(),
		);
	}

	/**
	 * Gets the callback.
	 *
	 * @since 3.3.6
	 * @return array
	 */
	protected function get_callback() {
		return array( $this, 'render' );
	}

	/**
	 * Gets the screen.
	 *
	 * @since 3.3.6
	 * @return array
	 */
	protected function get_screen() {
		return apply_filters( 'edd_download_metabox_post_types', array( $this->screen ) );
	}

	/**
	 * Gets the additional arguments for the callback.
	 *
	 * @since 3.3.6
	 * @return array
	 */
	protected function get_callback_args(): array {
		return array();
	}

	/**
	 * Sort the sections by priority.
	 *
	 * @since 3.3.6
	 * @param array $a The first section to compare.
	 * @param array $b The second section to compare.
	 * @return int
	 */
	protected function sort_sections_by_priority( $a, $b ) {
		return $a['priority'] - $b['priority'];
	}

	/**
	 * Validate the section.
	 *
	 * @since 3.3.6
	 * @param string $section The section to validate.
	 * @return bool|EDD\Admin\Sections\Section
	 */
	protected function validate_section( $section, $id = '' ) {
		if ( ! class_exists( $section ) ) {
			return false;
		}

		// Ensure that the section is a subclass of the base abstract class.
		if ( ! is_subclass_of( $section, Section::class ) ) {
			return false;
		}

		// Return a new instance of the section.
		$instantiated = new $section( $id );
		$instantiated->set_item( $this->download );

		return $instantiated;
	}
}
