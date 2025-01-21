<?php
/**
 * Section base class.
 *
 * @package EDD\Admin\Sections
 * @since 3.3.6
 */

namespace EDD\Admin\Sections;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Section.
 *
 * @since 3.3.6
 */
abstract class Section {

	/**
	 * Item.
	 *
	 * @since 3.3.6
	 * @var object
	 */
	protected $item;

	/**
	 * Constructor.
	 *
	 * @since 3.3.6
	 * @param string $id The section ID.
	 */
	public function __construct( $id = null ) {
		if ( ! is_null( $id ) ) {
			$this->id = $id;
		}
	}

	/**
	 * Section ID.
	 *
	 * @since 3.3.6
	 * @var string
	 */
	protected $id;

	/**
	 * Section priority.
	 *
	 * @since 3.3.6
	 * @var int
	 */
	protected $priority = 10;

	/**
	 * Section icon.
	 *
	 * @since 3.3.6
	 * @var string
	 */
	protected $icon = '';

	/**
	 * Get the section priority.
	 *
	 * @since 3.3.6
	 * @return int
	 */
	public function get_priority() {
		return $this->priority;
	}

	/**
	 * Set the item.
	 *
	 * @since 3.3.6
	 * @param object $item The item to set.
	 * @return void
	 */
	public function set_item( $item ) {
		$this->item = $item;
	}

	/**
	 * Get the section label.
	 *
	 * @since 3.3.6
	 * @return string
	 */
	abstract public function get_label();

	/**
	 * Get the section config.
	 *
	 * @since 3.3.6
	 * @return array
	 */
	public function get_config() {
		return array(
			'id'       => $this->get_id(),
			'label'    => $this->get_label(),
			'callback' => array( $this, 'render' ),
			'icon'     => $this->icon,
			'priority' => $this->priority,
			'classes'  => $this->get_classes(),
		);
	}

	/**
	 * Render the section.
	 *
	 * @since 3.3.6
	 * @return void
	 */
	abstract public function render();

	/**
	 * Get the section ID.
	 *
	 * @since 3.3.6
	 * @return string
	 */
	protected function get_id() {
		return $this->id;
	}

	/**
	 * Get the section classes.
	 *
	 * @since 3.3.6
	 * @return array
	 */
	protected function get_classes(): array {
		return array(
			'section-content',
			"section-content--{$this->get_id()}",
		);
	}
}
