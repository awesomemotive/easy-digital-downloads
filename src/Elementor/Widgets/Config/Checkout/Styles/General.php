<?php
/**
 * General Style Controls Configuration for Checkout Widget.
 *
 * @package     EDD\Elementor\Widgets\Config\Checkout\Styles
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Elementor\Widgets\Config\Checkout\Styles;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Elementor\Widgets\Config\Base;

/**
 * General Style Controls Configuration for Checkout Widget.
 *
 * @since 3.6.0
 */
class General extends Base {

	/**
	 * Get general style controls configuration.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	public static function get_controls(): array {
		return array(
			'section_general_style' => array(
				'label'    => __( 'General', 'easy-digital-downloads' ),
				'controls' => array(
					'general_typography' => self::create_typography_group(
						__( 'Typography', 'easy-digital-downloads' ),
						''
					),
					'general_text_color' => self::create_color_control(
						__( 'Text Color', 'easy-digital-downloads' ),
						''
					),
				),
			),
		);
	}
}
