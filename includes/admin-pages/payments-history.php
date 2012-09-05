<?php
/**
 * Admin Payment History
 *
 * @package     Easy Digital Downloads
 * @subpackage  Admin Payment History
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/

function edd_payments_remove_download() {
	check_admin_referer( sprintf( 'edd-remove-download_%d', $_GET[ 'download' ] ) );

	if ( ! isset ( $_REQUEST[ 'action' ] ) && ( $_REQUEST[ 'action' ] == 'edd-remove-download' ) )
		return;

	if ( ! isset( $_GET[ 'payment' ] ) )
		return;

	if ( ! isset( $_GET[ 'download' ] ) )
		return;

	$download_id = absint( $_GET[ 'edd-remove-download' ] );
	$post_id     = absint( $_GET[ 'payment' ] );
	$download    = absint( $_GET[ 'download' ] );

	$payment_data = get_post_meta( $post_id, '_edd_payment_meta', true );
	$downloads    = maybe_unserialize( $payment_data[ 'downloads' ] );

	foreach ( $downloads as $key => $c_download ) {
		if ( $c_download[ 'id' ] == $download ) {
			unset( $downloads[ $key ] );
		}
	}

	$payment_data[ 'downloads' ] = serialize( $downloads );
	update_post_meta( $post_id, '_edd_payment_meta', $payment_data );

	wp_redirect( admin_url( sprintf( 'post.php?action=edit&post=%d', $post_id ) ) );
	exit;
}
add_action( 'admin_action_edd-remove-download', 'edd_payments_remove_download' );

/**
 * 
 */
function edd_payment_history_search_fields( $wp ) {
	global $pagenow, $wpdb;

	if( 'edit.php' != $pagenow ) 
		return $wp;

	if( ! isset( $wp->query_vars[ 's' ] ) || ! $wp->query_vars[ 's' ] ) 
		return $wp;

	if ( $wp->query_vars[ 'post_type' ] != 'edd_payment' )
		return $wp;

	$search_fields = apply_filters( 'edd_payment_history_search_fields', array(
		'_edd_payment_user_email',
		'_edd_payment_user_id',
		'_edd_payment_purchase_key',
		'_edd_payment_user_ip'
	) );

	$post_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT post_id FROM ' . $wpdb->postmeta . ' WHERE meta_key IN ( '.'"'.implode('","', $search_fields).'"'.' ) AND meta_value LIKE "%%%s%%"', esc_attr( $_GET[ 's' ] ) ) );

	unset( $wp->query_vars['s'] );

	$wp->query_vars[ 'edd_payment_search' ] = true;

	$wp->query_vars['post__in'] = $post_ids;
}

/**
 * 
 */
function edd_payment_history_search_label( $query ) {
	global $pagenow, $typenow;

    if( 'edit.php' != $pagenow ) 
    	return $query;

    if ( $typenow != 'edd_payment' ) 
    	return $query;

	if ( ! get_query_var( 'edd_payment_search' ) ) 
		return $query;

	return $_GET['s'];
}
add_filter( 'parse_query', 'edd_payment_history_search_fields' );
add_filter( 'get_search_query', 'edd_payment_history_search_label' );

/**
 * Query vars for custom searches.
 *
 * @access public
 * @param mixed $public_query_vars
 * @return array
 */
function woocommerce_add_custom_query_var($public_query_vars) {
	$public_query_vars[] = 'edd_payment_search';
	$public_query_vars[] = 'edd_delete_payment';

	return $public_query_vars;
}
add_filter( 'query_vars', 'woocommerce_add_custom_query_var' );

function edd_payments_column_orderby( $vars ) {
	if ( isset( $vars[ 'orderby' ] ) && 'id' == $vars[ 'orderby' ] ) {
		$vars['orderby'] = 'id';
	}

	if ( isset( $vars[ 'orderby' ] ) && 'email' == $vars[ 'orderby' ] ) {
		$vars = array_merge( $vars, array(
			'meta_key' => '_edd_payment_user_email',
			'orderby'  => 'meta_value'
		) );
	}
 
	return $vars;
}
add_filter( 'request', 'edd_payments_column_orderby' );

function edd_payments_column_register_sortable( $columns ) {
	$columns[ 'order_title' ] = 'id';
	$columns[ 'email' ] = 'email';
 
	return $columns;
}
add_filter( 'manage_edit-edd_payment_sortable_columns', 'edd_payments_column_register_sortable' );

/**
 * 
 */
function edd_payments_order_by_user( $vars ) {
	global $typenow, $wp_query;

	if ( $typenow != 'edd_payment' )
		return $vars;

	if ( ! isset( $_GET[ 'user_email' ] ) )
		return $vars;

	if ( ! is_email( $_GET[ 'user_email' ] ) )
		return $vars;

	$vars[ 'meta_key' ]   = '_edd_payment_user_email';
	$vars[ 'meta_value' ] = $_GET[ 'user_email' ];

	return $vars;
}
add_filter( 'request', 'edd_payments_order_by_user' );

function edd_payment_history_columns( $cols ) {
	$cols = array(
		'cb'       => '<input type="checkbox" />',
		'order_title' => __( 'Order', 'edd' ),
		'price'    => __( 'Price', 'edd' ),
		'email'    => __( 'Email', 'edd' ),
		'user'     => __( 'User', 'edd' ),
		'ordered'  => __( 'Date', 'edd' ),
		'status'   => __( 'Status', 'edd' )
	);

	return $cols;
}
add_filter( 'manage_edd_payment_posts_columns', 'edd_payment_history_columns' );

function edd_payment_history_custom_columns( $column, $post_id ) {
	global $post;

	$payment_meta = get_post_meta( $post->ID, '_edd_payment_meta', true);
	$user_info    = maybe_unserialize( $payment_meta[ 'user_info' ] );
	$user         = new WP_User( $user_info[ 'id' ] );
	$email        = get_post_meta( $post->ID, '_edd_payment_user_email', true );
	$payment      = $post;

	$post_type_object = get_post_type_object( 'edd_payment' );

	$can_edit_post = current_user_can( $post_type_object->cap->edit_post, $post->ID );

	switch ( $column ) {
		case 'order_title':
			echo '<strong><a href="' . admin_url( sprintf( 'post.php?post=%d&action=edit', $post_id ) ) . '" class="row-title">';
			echo sprintf( __( 'Order #%d', 'edd' ), $post_id );
			echo '</a></strong>';
			
			$actions = array();

			if ( current_user_can( 'edit_post', $post->ID ) ) {
				$actions[ 'edit' ] = "<a title='" . esc_attr( __( 'Edit Payment', 'edd' ) ) . "' href='" . admin_url( sprintf( 'post.php?post=%d&action=edit', $post->ID ) ) . "'>" . __( 'Edit' ) . "</a>";
			}

			$actions[ 'resend' ] = "<a title='" . esc_attr( __( 'Resend Purchase Receipt', 'edd' ) ) . "' href='" . admin_url( sprintf( 'edit.php?post_type=edd_payment&edd-action=email_links&purchase_id=%d', $post->ID ) ) . "'>" . __( 'Resend Purchase Receipt' ) . "</a>";

			if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) ) {
				if ( 'trash' == $post->post_status )
					$actions['untrash'] = "<a title='" . esc_attr( __( 'Restore this item from the Trash', 'edd' ) ) . "' href='" . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-' . $post->post_type . '_' . $post->ID ) . "'>" . __( 'Restore', 'edd' ) . "</a>";
				elseif ( EMPTY_TRASH_DAYS )
					$actions['trash'] = "<a class='submitdelete' title='" . esc_attr( __( 'Move this item to the Trash', 'edd' ) ) . "' href='" . get_delete_post_link( $post->ID ) . "'>" . __( 'Trash', 'edd' ) . "</a>";
				if ( 'trash' == $post->post_status || !EMPTY_TRASH_DAYS )
					$actions['delete'] = "<a class='submitdelete' title='" . esc_attr( __( 'Delete this item permanently', 'edd' ) ) . "' href='" . get_delete_post_link( $post->ID, '', true ) . "'>" . __( 'Delete Permanently', 'edd' ) . "</a>";
			}

			$actions = apply_filters( 'post_row_actions', $actions, $post );

			echo '<div class="row-actions">';

			$i = 0;
			$action_count = sizeof($actions);

			foreach ( $actions as $action => $link ) {
				++$i;
				( $i == $action_count ) ? $sep = '' : $sep = ' | ';
				echo "<span class='$action'>$link$sep</span>";
			}
			echo '</div>';

			break;
		case 'email':
			echo $payment_meta[ 'email' ];
			break;
		case 'price' :
			echo edd_currency_filter( $payment_meta[ 'amount' ] );
			break;
		case 'ordered' :
			echo date_i18n( get_option( 'date_format' ), strtotime( $payment->post_date ) );
			break;
		case 'user' :
			$name = $user->display_name;

			if ( ! empty ( $user_info[ 'first_name' ] ) || ! empty( $user_info[ 'last_name' ] ) )
				$name = $user_info[ 'first_name' ] . ' ' . $user_info[ 'last_name' ];

			if ( $user->ID == 0 )
				$name = sprintf( __( '%s (Guest)', 'edd' ), $name );
				
			$name = sprintf( 
				'<a href="%s" title="%s">%s</a>', 
				self_admin_url( sprintf( 'edit.php?post_type=edd_payment&user_email=%s', $email ) ), 
				sprintf( __( 'View all payments by %s', 'edd' ), $name ),
				$name 
			);

			echo $name;

			break;
		case 'status' :
			echo edd_get_payment_status( $payment, true );
			break;
	}
}
add_action( 'manage_posts_custom_column', 'edd_payment_history_custom_columns', 10, 2 );

function edd_add_payment_meta_boxes() {
	add_meta_box( 'buyer-information', __( 'Purchase Information', 'edd' ), 'edd_render_buyer_info_meta_box', 'edd_payment', 'normal', 'default' );
	add_meta_box( 'purchased-files', __( 'Download Information', 'edd' ), 'edd_render_purchased_files_meta_box', 'edd_payment', 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'edd_add_payment_meta_boxes');

function edd_render_buyer_info_meta_box() {
	global $post;

	$payment_meta = get_post_meta( $post->ID, '_edd_payment_meta', true );
	$user_info    = maybe_unserialize( $payment_meta['user_info'] ); 
	$user         = new WP_user( $user_info[ 'id' ] );

	$email        = get_post_meta( $post->ID, '_edd_payment_user_email', true );

	$gateways = edd_get_enabled_payment_gateways();
	$gateway  = get_post_meta( $post->ID, '_edd_payment_gateway', true );

	$status   = $post->post_status;
	$statuses = edd_get_payment_statuses();

	$name = $user->display_name;

	if ( ! empty ( $user_info[ 'first_name' ] ) || ! empty( $user_info[ 'last_name' ] ) )
		$name = $user_info[ 'first_name' ] . ' ' . $user_info[ 'last_name' ];

	if ( $user->ID == 0 )
		$name = sprintf( __( '%s (Guest)', 'edd' ), $name );
		
	$name = sprintf( 
		'<a href="%s" title="%s">%s</a>', 
		self_admin_url( sprintf( 'edit.php?post_type=edd_payment&user_email=%s', $email ) ), 
		sprintf( __( 'View all payments by %s', 'edd' ), $name ),
		$name 
	);
?>
	<div class="purcase-personal-details">
		<p>
			<label for="edd_payment_buyer_name">
				<strong><?php _e( 'Buyer', 'edd' ); ?></strong>: <?php if ( $payment_meta ) : ?><?php echo $name; ?><?php endif; ?><br />
				<input type="text" name="edd_payment_buyer_email" value="<?php echo $payment_meta['email']; ?>" class="regular-text" />
			</label>
		</p>

		<?php do_action('edd_payment_personal_details_list', $payment_meta, $user_info); ?>
	</div>

	<div class="status-wrap">
		<p>
			<label for="edd_payment_status">
				<strong><?php _e( 'Payment Status', 'edd' ); ?></strong>: <br />
				<select name="edd-payment-status" id="edd_payment_status">
					<?php foreach( $statuses as $status_id => $label ) : ?>
					<option value="<?php echo $status_id; ?>" <?php selected( $status, $status_id ); ?>><?php echo $label; ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label for="edd_send_email">
				<input type="checkbox" name="edd-payment-send-email" id="edd_send_email" value="yes"/>
				<span class="description"><?php _e('Send Purchase Receipt', 'edd'); ?></span>
			</label>
		</p>
	</div>

	<div class="payment-method">
		<p>
			<label for="edd_payment_gateway">
				<strong><?php _e( 'Payment Method', 'edd' ); ?></strong>: <br />
				<select class="edd-select" name="payment-mode" id="edd-gateway">
				<?php foreach( $gateways as $gateway_id => $_gateway ) : ?>
					<option value="<?php echo $gateway_id; ?>" <?php selected( $gateway_id, $gateway ); ?>><?php echo $_gateway[ 'checkout_label' ]; ?></option>
				<?php endforeach; ?>
				</select>
			</label>
		</p>
	</div>

	<?php if( isset( $user_info['discount']) && $user_info['discount'] != 'none') : ?>
	<div class="discount-wrap">
		<p>
			<label for="edd_payment_discount">
				<strong><?php _e( 'Discount Code', 'edd'); ?></strong>: <br />
				<input type="text" name="edd_payment_discount" value="<?php echo $user_info['discount']; ?>" class="regular-text" readonly="readonly" />
			</label>
		</p>
	</div>
	<?php endif; ?>

	<div class="total-wrap">
		<p>
			<label for="edd_payment_total">
				<strong><?php _e( 'Total', 'edd' ); ?></strong>: (<?php echo edd_currency_filter(''); ?>) <br />
				<input type="text" name="edd_payment_discount" value="<?php echo $payment_meta[ 'amount' ]; ?>" class="regular-text" <?php if ( $payment_meta ) : ?>readonly="readonly"<?php endif; ?> />
			</label>
		</p>
	</div>

	<?php if ( $payment_meta ) : ?>
	<div class="purchase-key-wrap">
		<p>
			<label for="edd_payment_gateway">
				<strong><?php _e( 'Purchase Key', 'edd' ); ?></strong>: <br />
				<input type="text" name="edd_payment_purchase_key" value="<?php echo $payment_meta['key']; ?>" class="regular-text"  readonly="readonly" />
			</label>
		</p>
	</div>
	<?php endif; ?>
<?php
}

function edd_render_purchased_files_meta_box() {
	global $post;

	$payment_meta = get_post_meta( $post->ID, '_edd_payment_meta', true );
	$user_info    = maybe_unserialize( $payment_meta[ 'user_info' ] ); 

	$downloads    = maybe_unserialize( $payment_meta[ 'downloads' ] );
?>

	<?php if ( $downloads ) : ?>
		
	<p>
		<strong><?php _e( 'Current Downloads', 'edd' ); ?></strong>: 
	</p>

	<div id="postcustomstuff">
		<table id="newmeta" class="widefat">
			<thead>
				<tr>
					<th align="left"><?php _e( 'Download', 'edd' ); ?></th>
					<th align="left"><?php _e( 'Price', 'edd' ); ?></th>
					<th align="left"><?php _e( 'Options', 'edd' ); ?></th>
				</tr>
			<thead>
			<tbody>
				<?php foreach( $downloads as $key => $download ) : ?>
					<?php
						$id = $download[ 'id' ];
						$user_info = unserialize( $payment_meta[ 'user_info' ]);
						$price = edd_get_download_final_price( $id, $user_info );
						$price_option = isset( $download[ 'options' ]['price_id'] ) ? $download[ 'options' ]['price_id'] : null;
					?>
					<tr>
						<td>
							<strong><a href="<?php echo admin_url( sprintf( 'post.php?post=%d&action=edit', $id ) ); ?>" target="_blank" class="row-title"><?php echo get_the_title( $id ); ?></a></strong>

							<div class="row-actions">
								<a href="<?php echo esc_url( admin_url( sprintf( 'post.php?post=%d&action=edit', $id ) ) ); ?>"><?php _e( 'Edit Download', 'edd' ); ?></a> 
									| 
								<span class="trash">
									<a href="<?php echo esc_url( wp_nonce_url( admin_url( sprintf( '?action=edd-remove-download&payment=%d&download=%d', $post->ID, $id ) ), 'edd-remove-download_' . $id ) ); ?>" class="submitdelete"><?php _e( 'Remove', 'edd' ); ?></a>
								</span>
							</div>

							<input type="hidden" name="edd-purchased-downloads[<?php echo $id; ?>]" value="<?php echo $price_option; ?>" />
						</td>
						<td>
							<?php echo edd_currency_filter( $price ); ?>
						</td>
						<td>
							<?php
								if ( $price_option ) {
									echo edd_get_price_option_name( $id, $price_option );
								}
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php endif; ?>

		<p id="edd-add-download" class="submit">
			<?php
				$downloads = get_posts( array(
					'post_type' => 'download', 
					'posts_per_page' => -1
				) );

				if ( $downloads ) :
			?>
				<select name="edd-add-download">
					<option value="0"><?php _e( 'Select Download', 'edd' ); ?></option>
					<?php foreach ( $downloads as $download ) : ?>
					<option value="<?php echo $download->ID; ?>"><?php echo $download->post_title; ?></option>
					<?php 
						$prices = get_post_meta( $download->ID, 'edd_variable_prices', true ); 
						$has_variable = get_post_meta( $download->ID, '_variable_pricing', true );

						if ( $prices && $has_variable ) :  foreach ( $prices as $key => $price ) :
					?>

						<option value="<?php echo $download->ID; ?>.<?php echo $key; ?>">&nbsp; &mdash; <?php echo $price[ 'name' ]; ?></option>
					<?php endforeach; endif; ?>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>
			<?php submit_button( __( 'Add New Download', 'edd' ), 'button-secondary', 'add-download', false ); ?>
		</p>
	</div>
	<script>
	jQuery( 'input[name="add-download"]' ).click(function(e) {
		e.preventDefault();
		jQuery('#publish').click();
	});
	</script>
<?php
}

/**
 * 
 *
 * @access      public
 * @since       1.1.8
 * @return      int $post_id The ID of the updated payment.
*/
function edd_update_edited_purchase( $post_id, $post ) {
	/** Don't save when autosaving */
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		return $post_id;
	
	/** Make sure we are on a product */
	if ( 'edd_payment' != $post->post_type )
		return $post_id;

	$payment_data = get_post_meta( $post->ID, '_edd_payment_meta', true);

	$current_downloads = $_POST[ 'edd-purchased-downloads' ];

	if ( $current_downloads ) {
		foreach ( $current_downloads as $id => $price_id ) {
			$current_downloads[ $id ] = array(
				'id' => $id,
				'options' => array(
					'price_id' => $price_id
				)
			);
		}
	} else 
		$current_downloads = array();

	$new_download = $_POST[ 'edd-add-download' ];

	if ( $new_download != 0 ) {
		$updated_downloads = array();

		$new_download = explode( '.', $new_download );

		$updated_downloads[ $new_download[0] ] = array( 'id' => $new_download[0] );

		if ( count( $new_download ) > 1 ) {
			$updated_downloads[ $new_download[0] ] = array(
				'id'      => $new_download[0],
				'options' => array(
					'price_id' => $new_download[1]
				)
			);
		}

		$payment_data[ 'downloads' ] = serialize( array_merge( $current_downloads, $updated_downloads ) );
	}
	
	$email = $_POST[ 'edd_payment_buyer_email' ];

	if ( is_email( $email ) )
		$payment_data[ 'email' ] = strip_tags( $email );
	
	/** update all data */
	update_post_meta( $post_id, '_edd_payment_meta', $payment_data );
	
	/** update user email */
	update_post_meta( $post_id, '_edd_payment_user_email', $payment_data['email'] );
		
	$status = $_POST[ 'edd-payment-status' ];

	if ( $status ) {
		if( 'refunded' == $status ) {
			foreach( $downloads as $download ) {
				edd_undo_purchase( $download, $payment_id );					
			}
		}
		
		remove_action( 'save_post', 'edd_update_edited_purchase', 10, 2 );

		wp_update_post( array(
			'ID'          => $post->ID, 
			'post_status' => $status
		) );

		add_action( 'save_post', 'edd_update_edited_purchase', 10, 2 );
	}
	
	if( 'publish' == $status && isset( $_POST[ 'edd-payment-send-email' ] ) ) {
		edd_email_purchase_receipt( $post->ID, false );
	}

	return $post_id;
}
add_action( 'save_post', 'edd_update_edited_purchase', 10, 2 );