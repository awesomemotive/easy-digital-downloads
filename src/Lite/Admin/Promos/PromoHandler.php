<?php
/**
 * Promo Handler
 *
 * Handles logic for displaying and dismissing promotional notices.
 *
 * @package   EDD\Lite\Admin\Promos
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license   GPL2+
 * @since     3.5.1
 */

namespace EDD\Lite\Admin\Promos;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Promo Handler
 *
 * Handles logic for displaying and dismissing promotional notices.
 *
 * @package   EDD\Lite\Admin\Promos
 * @copyright Copyright (c) 2025, Sandhills Development, LLC
 * @license   GPL2+
 * @since     3.5.1
 */
class PromoHandler extends \EDD\Admin\Promos\PromoHandler {

	/**
	 * Registered notices.
	 *
	 * @var array
	 */
	protected $lite_notices = array(
		'\\EDD\\Lite\\Admin\\Promos\\Notices\\FeaturedDownloads',
	);

	/**
	 * Gets the notices.
	 * This method overrides the parent method if an inactive pro install is detected.
	 *
	 * @return array
	 */
	protected function get_notices() {
		return array_unique( array_merge( $this->notices, $this->lite_notices ) );
	}
}
