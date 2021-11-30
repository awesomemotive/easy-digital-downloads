<?php
/**
 * Reviews
 *
 * Manages automatic activation for Reviews.
 *
 * @package     EDD
 * @subpackage  Reviews
 * @copyright   Copyright (c) 2021, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.11.x
 */
namespace EDD\Admin\Settings;

use \EDD\Admin\Extensions\Extension;

class Reviews extends Extension {

	/**
	 * The product ID on EDD.
	 *
	 * @var integer
	 */
	protected $item_id = 37976;

	/**
	 * The pass level required to access this extension.
	 */
	const PASS_LEVEL = \EDD\Admin\Pass_Manager::EXTENDED_PASS_ID;

	public function __construct() {
		add_filter( 'edd_settings_sections_marketing', array( $this, 'add_section' ) );
		add_action( 'edd_settings_tab_top_marketing_reviews', array( $this, 'settings_field' ) );
		add_action( 'edd_settings_tab_top_marketing_reviews', array( $this, 'hide_submit_button' ) );
		add_action( 'admin_init', array( $this, 'maybe_do_metabox' ) );

		parent::__construct();
	}

	/**
	 * Gets the custom configuration for Reviews.
	 *
	 * @since 2.11.x
	 * @return array
	 */
	protected function get_configuration() {
		return array(
			'card' => 'horizontal',
		);
	}

	/**
	 * Adds the Reviews section to the settings.
	 *
	 * @param array $sections
	 * @return array
	 */
	public function add_section( $sections ) {
		if ( $this->is_activated() ) {
			return $sections;
		}

		$sections['reviews'] = __( 'Reviews', 'easy-digital-downloads' );

		return $sections;
	}

	/**
	 * If Reviews is not active, registers a metabox on individual download edit screen.
	 *
	 * @since 2.11.x
	 * @return void
	 */
	public function maybe_do_metabox() {
		if ( $this->is_activated() ) {
			return;
		}
		\add_meta_box(
			'edd-reviews-status',
			__( 'Product Reviews', 'easy-digital-downloads' ),
			array( $this, 'settings_field' ),
			'download',
			'side',
			'low'
		);
	}

	/**
	 * Whether EDD Reviews active or not.
	 *
	 * @since 2.11.x
	 *
	 * @return bool True if Reviews is active.
	 */
	protected function is_activated() {
		$product_data = $this->get_product_data();

		if ( $this->manager->is_plugin_active( $product_data['basename'] ) ) {
			return true;
		}

		return function_exists( 'edd_reviews' );
	}
}

new Reviews();
