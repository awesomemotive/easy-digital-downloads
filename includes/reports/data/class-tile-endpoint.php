<?php
/**
 * Reports API - Tile Endpoint Handler
 *
 * @package     EDD
 * @subpackage  Reports
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Admin\Reports\Data;

/**
 * Handler for building a tile endpoint in the Reports API.
 *
 * @since 3.0
 */
final class Tile_Endpoint extends Endpoint {

	/**
	 * Endpoint view (type).
	 *
	 * @since 3.0
	 * @var   string
	 */
	protected $view = 'tile';

	/**
	 * Sets up the tile endpoint.
	 *
	 * @since 3.0
	 *
	 * @param array $args Tile endpoint attributes.
	 */
	public function __construct( array $args ) {
		parent::__construct( $args );

		$this->add_meta_box();
	}

	/**
	 * Retrieves the meta box context for the current tile endpoint.
	 *
	 * @since 3.0
	 *
	 * @return string Meta box context for the tile.
	 */
	public function get_context() {
		$display_args = $this->get_display_args();

		if ( isset( $display_args['context'] ) ) {
			$context = $display_args['context'];
		} else {
			$context = '';
		}

		return $context;
	}

	/**
	 * Registers the tile as a meta box.
	 *
	 * @since 3.0
	 */
	public function add_meta_box() {
		$id     = $this->get_id();
		$report = $this->get_report_id();

		add_meta_box(
			empty( $report ) ? "report-{$id}" : "{$report}-{$id}",
			$this->get_label(),
			$this->get_display_callback(),
			'download_page_edd-reports',
			$this->get_context(),
			'default',
			array(
				'endpoint'     => $this,
				'data'         => $this->get_data(),
				'display_args' => $this->get_display_args()
			)
		);
	}

	/**
	 * Display logic for the current tile endpoint.
	 *
	 * Tiles are rendered via meta boxes, so this method is deliberately empty.
	 *
	 * @since 3.0
	 */
	public function display() {}

}
