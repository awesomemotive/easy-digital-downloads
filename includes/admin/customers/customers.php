<?php
/**
 * Customers Page
 *
 * Renders the customers page contents.
 *
 * @since  2.3
 * @return void
*/
function edd_customers_page() {
	$current_page  = admin_url( 'edit.php?post_type=download&page=edd-customers' );
	$default_views = edd_customer_views();
	$requested_view = isset( $_GET['view'] ) ? sanitize_text_field( $_GET['view'] ) : 'customers';
	if ( array_key_exists( $requested_view, $default_views ) && function_exists( $default_views[$requested_view] ) ) {
		edd_render_customer_view( $requested_view, $default_views );
	} else {
		edd_customers_list();
	}
}

/**
 * Register the views for customer management
 * @return array Array of views and their callbacks
 */
function edd_customer_views() {

	$views = array();
	return apply_filters( 'edd_customer_views', $views );

}

function edd_customer_tabs() {

	$tabs = array();
	return apply_filters( 'edd_customer_tabs', $tabs );

}

/**
 * List table of customers
 * @return void
 */
function edd_customers_list() {
	include( dirname( __FILE__ ) . '/class-customer-table.php' );

	$customers_table = new EDD_Customer_Reports_Table();
	$customers_table->prepare_items();
	?>
	<div class="wrap">
		<h2><?php _e( 'Customers', 'edd' ); ?></h2>
		<?php do_action( 'edd_customers_table_top' ); ?>
		<form id="edd-customers-filter" method="get" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-customers' ); ?>">
			<?php
			$customers_table->search_box( __( 'Search', 'edd' ), 'edd-customers' );
			$customers_table->display();
			?>
			<input type="hidden" name="post_type" value="download" />
			<input type="hidden" name="page" value="edd-customers" />
			<input type="hidden" name="view" value="customers" />
		</form>
		<?php do_action( 'edd_customers_table_bottom' ); ?>
	</div>
	<?php
}

function edd_render_customer_view( $view, $callbacks ) {
	$customer_edit_role = apply_filters( 'edd_view_customers_role', 'view_shop_reports' );
	if ( ! current_user_can( $customer_edit_role ) ) {
		edd_set_error( 'edd-no-access', __( 'You are not permitted to view this data.', 'edd' ) );
	}

	if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		edd_set_error( 'edd-invalid_customer', __( 'Invalid Customer ID Provided.', 'edd' ) );
	}

	$customer_id = (int)$_GET['id'];
	$customer    = EDD()->customers->get_customer( $customer_id );

	if ( empty( $customer ) ) {
		edd_set_error( 'edd-invalid_customer', __( 'Invalid Customer ID Provided.', 'edd' ) );
	}

	if ( ! empty( edd_get_errors() ) ) {
		edd_print_errors();
		edd_clear_errors();
		return;
	}

	$customer_tabs = edd_customer_tabs();
	?>

	<div class='wrap'>
		<h2><?php _e( 'Customer Details', 'edd' );?></h2>
		<div id="customer-tab-wrapper">
			<ul id="customer-tab-wrapper-list">
			<?php foreach ( $customer_tabs as $key => $tab ) : ?>
				<?php $active = $key === $view ? true : false; ?>
				<?php $class  = $active ? 'active' : 'inactive'; ?>

				<?php if ( ! $active ) : ?>
				<a title="<?php echo $tab['title']; ?>" href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-customers&view=' . $key . '&id=' . $customer->id ); ?>">
				<?php endif; ?>

				<li class="<?php echo $class; ?>"><span class="dashicons <?php echo $tab['dashicon']; ?>"></span></li>

				<?php if ( ! $active ) : ?>
				</a>
				<?php endif; ?>

			<?php endforeach; ?>
			</ul>
		</div>

		<div id="edd-customer-card-wrapper" style="float: left">
			<?php $callbacks[$view]( $customer ) ?>
		</div>

	</div>
	<?php

}


/**
 * View a customer
 * @return void
 */
function edd_customers_view( $customer ) {
	?>

	<?php do_action( 'edd_customer_card_top', $customer ); ?>

	<div class="info-wrapper customer-section">

		<div class="customer-info">
			<div class="avatar-wrap left">
				<?php echo get_avatar( $customer->email ); ?>
			</div>

			<div class="customer-id right">
				#<?php echo $customer->id; ?>
			</div>
			<span class="customer-name info-item"><?php echo $customer->name; ?>&nbsp;<a id="edit-customer"><span class="dashicons dashicons-edit"></span></a></span>
			<span class="customer-email info-item"><?php echo $customer->email; ?></span>
			<span class="customer-since info-item">
				<?php _e( 'Customer since', 'edd' ); ?>
				<?php echo date_i18n( get_option( 'date_format' ), strtotime( $customer->date_created ) ) ?>
			</span>
			<?php if ( isset( $customer->user_id ) && $customer->user_id > 0 ) : ?>
				<span class="customer-user-id info-item">
					<?php _e( 'User ID', 'edd' ); ?>:&nbsp;
					<?php echo $customer->user_id; ?>
					&nbsp; - &nbsp;
					<?php printf( '<a href="%s">' . __( 'Edit User', 'edd' ) . '</a>', admin_url( 'user-edit.php?user_id=' . $customer->user_id ) ); ?>
				</span>
			<?php endif; ?>
		</div>
	</div>

	<?php do_action( 'edd_customer_before_stats', $customer ); ?>

	<div id="customer-stats-wrapper" class="customer-section">
		<ul>
			<li>
				<a title="<?php _e( 'View All Purchases', 'edd' ); ?>" href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-payment-history&user=' . urlencode( $customer->email ) ); ?>">
					<span class="dashicons dashicons-products"></span>
					<?php echo $customer->purchase_count; ?> <?php _e( 'Purchases' ,'edd' ); ?>
				</a>
			</li>
			<li>
				<span class="dashicons dashicons-cart"></span>
				<?php echo edd_currency_filter( edd_format_amount( $customer->purchase_value ) ); ?> <?php _e( 'Lifetime Value', 'edd' ); ?>
			</li>
			<?php do_action( 'edd_customer_stats_list', $customer ); ?>
		</ul>
	</div>

	<?php do_action( 'edd_customer_before_purchases', $customer ); ?>

	<div id="customer-purchases-wrapper" class="customer-section">
		<h3><?php _e( 'Recent Payments', 'edd' ); ?></h3>
		<?php
			$payment_ids = explode( ',', $customer->payment_ids );
			$payments    = edd_get_payments( array( 'post__in' => $payment_ids ) );
			$payments    = array_slice( $payments, 0, 10 );
		?>
		<table class="wp-list-table widefat striped payments">
			<thead>
				<tr>
					<th><?php _e( 'ID', 'edd' ); ?></th>
					<th><?php _e( 'Amount', 'edd' ); ?></th>
					<th><?php _e( 'Date', 'edd' ); ?></th>
					<th><?php _e( 'Status', 'edd' ); ?></th>
					<th><?php _e( 'Actions', 'edd' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $payments as $payment ) : ?>
					<tr>
						<td><?php echo $payment->ID; ?></td>
						<td><?php echo edd_payment_amount( $payment->ID ); ?></td>
						<td><?php echo date_i18n( get_option( 'date_format' ), strtotime( $payment->post_date ) ); ?></td>
						<td><?php echo edd_get_payment_status( $payment, true ); ?></td>
						<td>
							<a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $payment->ID ); ?>">
								<?php _e( 'View Details', 'edd' ); ?>
							</a>
							<?php do_action( 'edd_customer_recent_purcahses_actions', $customer, $payment ); ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<?php do_action( 'edd_customer_card_bottom', $customer ); ?>

	<?php
}

/**
 * View the notes of a customer
 * @return void
 */
function edd_customer_notes_view( $customer ) {
	global $current_user;
	get_currentuserinfo();

	$customer_notes = edd_get_customer_notes( $customer->id );
	?>

	<div id="customer-notes-wrapper">
		<h3><?php _e( 'Notes', 'edd' ); ?></h3>

		<div style="display: block; margin-bottom: 35px;">
			<div class="avatar-wrap left">
				<?php echo get_avatar( $current_user->user_email, 32 ); ?>
			</div>
			<form id="edd-add-customer-note" method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-customers&view=notes&id=' . $customer->id ); ?>">
				<textarea name="customer-note" style="width: 93%; margin-bottom: 5px;"></textarea>
				<br />
				<input type="hidden" name="customer-id" value="<?php echo $customer->id; ?>" />
				<input type="hidden" name="edd-action" value="add-customer-note" />
				<?php wp_nonce_field( 'add-customer-note', 'add-customer-note-nonce', true, true ); ?>
				<input class="right button-primary" type="submit" value="Add Note" />
			</form>
		</div>

		<?php if ( count( $customer_notes ) > 0 ) : ?>
			<?php foreach( $customer_notes as $note ) : ?>
				<div class="customer-note-wrapper dashboard-comment-wrap comment-item">
					<span class="row-actions right">
						<a href="<?php echo wp_nonce_url( admin_url( 'edit.php?post_type=download&page=edd-customers&view=notes&edd-action=delete-customer-note&note_id=' . $note->comment_ID . '&id=' . $customer->id ), 'delete-customer-note' ); ?>" class="delete"><?php _e( 'Delete', 'edd' ); ?></a>
					</span>
					<span class="avatar-wrap left">
						<?php $user_data = get_userdata( $note->user_id ); ?>
						<?php echo get_avatar( $user_data->user_email, 32 ); ?>
					</span>
					<span class="note-meta-wrap">
						<?php echo $user_data->user_nicename; ?>
						 @ <?php echo date_i18n( get_option( 'time_format' ), strtotime( $note->comment_date ), true ); ?>
						 <?php _e( 'on', 'edd' ); ?> <?php echo date_i18n( get_option( 'date_format' ), strtotime( $note->comment_date ), true ); ?>
					</span>
					<span class="note-content-wrap">
						<?php echo $note->comment_content; ?>
					</span>
				</div>
			<?php endforeach; ?>
		<?php else: ?>
			<?php _e( 'No Customer Notes', 'edd' ); ?>
		<?php endif; ?>
	</div>

	<?php
}
