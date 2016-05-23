<?php
/**
 * Discount Codes
 *
 * @package     EDD
 * @subpackage  Admin/Discounts
 * @copyright   Copyright (c) 2015, Pippin Williamson
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
	if ( isset( $_GET['edd-action'] ) && $_GET['edd-action'] == 'edit_discount' ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/discounts/edit-discount.php';
	} elseif ( isset( $_GET['edd-action'] ) && $_GET['edd-action'] == 'add_discount' ) {
		require_once EDD_PLUGIN_DIR . 'includes/admin/discounts/add-discount.php';
	} else {
		require_once EDD_PLUGIN_DIR . 'includes/admin/discounts/class-discount-codes-table.php';
		$discount_codes_table = new EDD_Discount_Codes_Table();
		$discount_codes_table->prepare_items();
	?>
	<div class="wrap">
		<h1><?php _e( 'Discount Codes', 'easy-digital-downloads' ); ?><a href="<?php echo esc_url( add_query_arg( array( 'edd-action' => 'add_discount' ) ) ); ?>" class="add-new-h2"><?php _e( 'Add New', 'easy-digital-downloads' ); ?></a></h1>
		<?php do_action( 'edd_discounts_page_top' ); ?>
		<form id="edd-discounts-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-discounts' ); ?>">
			<?php $discount_codes_table->search_box( __( 'Search', 'easy-digital-downloads' ), 'edd-discounts' ); ?>

			<input type="hidden" name="post_type" value="download" />
			<input type="hidden" name="page" value="edd-discounts" />

			<?php $discount_codes_table->views() ?>
			<?php $discount_codes_table->display() ?>
		</form>
		<?php do_action( 'edd_discounts_page_bottom' ); ?>
	</div>
<?php
	}
}
