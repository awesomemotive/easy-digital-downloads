<?php
/**
 * Discount Codes
 *
 * @package     EDD
 * @subpackage  Admin/Discounts
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Renders the Discount Pages Admin Page
 *
 * @since 1.4
 * @author Sunny Ratilal
 * @return void
*/
function edd_discounts_page() {
	global $edd_options;

	if ( isset( $_GET['edd-action'] ) && $_GET['edd-action'] == 'edit_discount' ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/discounts/edit-discount.php';
	} else {
		require_once EDD_PLUGIN_DIR . 'includes/admin/discounts/class-discount-codes-table.php';
		$discount_codes_table = new EDD_Discount_Codes_Table();
		$discount_codes_table->prepare_items();
	?>
	<div class="wrap">
		<h2><?php _e( 'Discount Codes', 'edd' ); ?></h2>
		<?php do_action( 'edd_discounts_page_top' ); ?>
		<form id="edd-discounts-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-discounts' ); ?>">
			<?php $discount_codes_table->search_box( __( 'Search', 'edd' ), 'edd-discounts' ); ?>

			<input type="hidden" name="post_type" value="download" />
			<input type="hidden" name="page" value="edd-discounts" />

			<?php $discount_codes_table->views() ?>
			<?php $discount_codes_table->display() ?>
		</form>
		<?php require_once EDD_PLUGIN_DIR . 'includes/admin/discounts/add-discount.php'; ?>
		<?php do_action( 'edd_discounts_page_bottom' ); ?>
	</div>
<?php
	}
}