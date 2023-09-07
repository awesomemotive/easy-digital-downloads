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

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Renders the Discounts admin page.
 *
 * Here only for backwards compatibility
 *
 * @since 1.4
 * @since 3.0 Nomenclature updated for consistency.
*/
function edd_discounts_page() {
	// Enqueue scripts.
	wp_enqueue_script( 'edd-admin-discounts' );

	// Edit
	if ( ! empty( $_GET['edd-action'] ) && ( 'edit_discount' === $_GET['edd-action'] ) ) {
		if ( ! current_user_can( 'edit_shop_discounts' ) ) {
			wp_die( __( 'You do not have permission to edit discounts.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		wp_enqueue_script( 'edd-admin-notes' );
		require_once EDD_PLUGIN_DIR . 'includes/admin/discounts/edit-discount.php';

	// Add
	} elseif ( ! empty( $_GET['edd-action'] ) && ( 'add_discount' === $_GET['edd-action'] ) ) {
		if ( ! current_user_can( 'manage_shop_discounts' ) ) {
			wp_die( __( 'You do not have permission to manage discounts.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		require_once EDD_PLUGIN_DIR . 'includes/admin/discounts/add-discount.php';

	// List tables
	} else {
		edd_adjustments_page();
	}
}

/**
 * Output the discounts page content, in the adjustments page action.
 *
 * @since 3.0
 */
function edd_discounts_page_content() {
	if ( ! current_user_can( 'manage_shop_discounts' ) ) {
		wp_die( __( 'You do not have permission to manage discounts.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	require_once EDD_PLUGIN_DIR . 'includes/admin/discounts/class-discount-codes-table.php';

	$discount_codes_table = new EDD_Discount_Codes_Table();
	$discount_codes_table->prepare_items();

	do_action( 'edd_discounts_page_top' ); ?>

	<form id="edd-discounts-filter" method="get" action="<?php echo esc_url( edd_get_admin_url( array( 'page' => 'edd-discounts' ) ) ); ?>">
		<?php $discount_codes_table->search_box( __( 'Search Discounts', 'easy-digital-downloads' ), 'edd-discounts' ); ?>

		<input type="hidden" name="post_type" value="download" />
		<input type="hidden" name="page" value="edd-discounts" />

		<?php
		$discount_codes_table->views();
		$discount_codes_table->display();
		?>
	</form>

	<?php do_action( 'edd_discounts_page_bottom' );
}
add_action( 'edd_adjustments_page_discount', 'edd_discounts_page_content' );
