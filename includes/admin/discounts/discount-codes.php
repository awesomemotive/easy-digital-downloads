<?php
/**
 * Discount Codes
 *
 * @package     EDD
 * @subpackage  Admin/Discounts
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Renders the Discounts admin page.
 *
 * Here only for backwards compatibility
 *
 * @since 1.4
 * @since 3.0 Nomenclature updated for consistency.
 * @since 3.3.9 Use the new EDD\Admin\Discounts\Screen class.
 */
function edd_discounts_page() {
	EDD\Admin\Discounts\Screen::render();
}

/**
 * Output the discounts page content, in the adjustments page action.
 *
 * @since 3.0
 */
function edd_discounts_page_content() {
	EDD\Admin\Discounts\Screen::render_list_table();
}
add_action( 'edd_adjustments_page_discount', 'edd_discounts_page_content' );
