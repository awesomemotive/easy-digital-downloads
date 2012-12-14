<?php
/**
 * Discount Codes
 *
 * @package     Easy Digital Downloads
 * @subpackage  Discount Codes
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Discounts Page
 *
 * Renders the discount page contents.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_discounts_page() {
	global $edd_options;
	$current_page = get_bloginfo( 'wpurl' ) . '/wp-admin/admin.php?edit.php?post_type=download&page=edd-discounts';
	?>
	<div class="wrap">

		<?php if( isset( $_GET['edd-action'] ) && $_GET['edd-action'] == 'edit_discount' ): ?>

			<?php include_once( EDD_PLUGIN_DIR . 'includes/admin/discounts/edit-discount.php' ); ?>

		<?php else: ?>
			<h2><?php _e( 'Discount Codes', 'edd' ); ?></h2>
			<?php $discounts = edd_get_discounts(); ?>
			<table class="wp-list-table widefat fixed posts edd-discounts">
				<thead>
					<tr>
						<th><?php _e( 'Name', 'edd' ); ?></th>
						<th><?php _e( 'Code', 'edd' ); ?></th>
						<th><?php _e( 'Amount', 'edd' ); ?></th>
						<th><?php _e( 'Uses', 'edd' ); ?></th>
						<th><?php _e( 'Max Uses', 'edd' ); ?></th>
						<th><?php _e( 'Start Date', 'edd' ); ?></th>
						<th><?php _e( 'Expiration', 'edd' ); ?></th>
						<th><?php _e( 'Status', 'edd' ); ?></th>
						<th><?php _e( 'Actions', 'edd' ); ?></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th><?php _e( 'Name', 'edd' ); ?></th>
						<th><?php _e( 'Code', 'edd' ); ?></th>
						<th><?php _e( 'Amount', 'edd' ); ?></th>
						<th><?php _e( 'Uses', 'edd' ); ?></th>
						<th><?php _e( 'Max Uses', 'edd' ); ?></th>
						<th><?php _e( 'Start Date', 'edd' ); ?></th>
						<th><?php _e( 'Expiration', 'edd' ); ?></th>
						<th><?php _e( 'Status', 'edd' ); ?></th>
						<th><?php _e( 'Actions', 'edd' ); ?></th>
					</tr>
				</tfoot>
				<tbody>
					<?php if($discounts) : ?>
						<?php foreach($discounts as $discount_key => $discount) : ?>
							<tr>
								<td><?php if( isset( $discount['name'] ) ) echo $discount['name']; ?></td>
								<td><?php if( isset( $discount['code'] ) ) echo $discount['code']; ?></td>
								<td><?php if( isset( $discount['amount'] ) ) echo edd_format_discount_rate( $discount['type'], $discount['amount'] ); ?></td>
								<td>
									<?php
										if( isset( $discount['uses'] ) && isset( $discount['max'] ) && $discount['uses'] != '' && $discount['max'] != '' ) {
											echo $discount['uses'] == '' ? 0 : $discount['uses'] . '/' . $discount['max'];
										} else {
											echo isset( $discount['uses'] ) ? $discount['uses'] : 0;
										}
									?>
								</td>
								<td>
									<?php
										if( isset( $discount['max'] ) ) {
											echo $discount['max'] == '' ? __( 'unlimited', 'edd' ) : $discount['max'];
										} else {
											_e( 'unlimited', 'edd' );
										}
									?>
								</td>
								<td>
								<?php
									if( isset( $discount['start'] ) && $discount['start'] != '' ) {
										echo date( get_option( 'date_format' ), strtotime( $discount['start'] ) );
									} else {
										_e( 'No start date', 'edd' );
									}
									?>
								</td>
								<td>
								<?php
									if( isset( $discount['expiration'] ) && $discount['expiration'] != '' ) {
										echo edd_is_discount_expired( $discount_key ) ? __( 'Expired', 'edd' ) : date_i18n( get_option( 'date_format' ), strtotime( $discount['expiration'] ) );
									} else {
										_e( 'no expiration', 'edd' );
									}
									?>
								</td>
								<td><?php if( isset( $discount['status'] ) ) echo $discount['status']; ?></td>
								<td>
									<a href="<?php echo add_query_arg( 'edd-action', 'edit_discount', add_query_arg('discount', $discount_key, $current_page ) ); ?>"><?php _e( 'Edit', 'edd' ); ?></a> |
									<?php if( edd_is_discount_active( $discount_key ) ) { ?>
									<a href="<?php echo add_query_arg('edd-action', 'deactivate_discount', add_query_arg( 'discount', $discount_key, $current_page ) ); ?>"><?php _e( 'Deactivate', 'edd' ); ?></a> |
									<?php } else { ?>
										<a href="<?php echo add_query_arg('edd-action', 'activate_discount', add_query_arg( 'discount', $discount_key, $current_page ) ); ?>"><?php _e( 'Activate', 'edd' ); ?></a> |
									<?php } ?>
									<a href="<?php echo add_query_arg('edd-action', 'delete_discount', add_query_arg( 'discount', $discount_key, $current_page ) ); ?>"><?php _e( 'Delete', 'edd' ); ?></a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else: ?>
					<tr><td colspan=10><?php _e( 'No discount codes have been created.', 'edd' ); ?></td>
					<?php endif; ?>
				</tbody>
			</table>
			<?php do_action( 'edd_discounts_below_table' ); ?>

			<?php include_once( EDD_PLUGIN_DIR . 'includes/admin/discounts/add-discount.php' ); ?>

		<?php endif; ?>

	</div><!--end wrap-->
	<?php
}