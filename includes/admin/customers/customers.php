<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

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
 *
 * @since  2.3
 * @return array Array of views and their callbacks
 */
function edd_customer_views() {

	$views = array();
	return apply_filters( 'edd_customer_views', $views );

}

/**
 * Register the tabs for customer management
 *
 * @since  2.3
 * @return array Array of tabs for the customer
 */
function edd_customer_tabs() {

	$tabs = array();
	return apply_filters( 'edd_customer_tabs', $tabs );

}

/**
 * List table of customers
 *
 * @since  2.3
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
			$customers_table->search_box( __( 'Search Customers', 'edd' ), 'edd-customers' );
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

/**
 * Renders the customer view wrapper
 *
 * @since  2.3
 * @param  string $view      The View being requested
 * @param  array $callbacks  The Registered views and their callback functions
 * @return void
 */
function edd_render_customer_view( $view, $callbacks ) {

	$customer_edit_role = apply_filters( 'edd_view_customers_role', 'view_shop_reports' );

	if ( ! current_user_can( $customer_edit_role ) ) {
		edd_set_error( 'edd-no-access', __( 'You are not permitted to view this data.', 'edd' ) );
	}

	if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
		edd_set_error( 'edd-invalid_customer', __( 'Invalid Customer ID Provided.', 'edd' ) );
	}

	$customer_id = (int)$_GET['id'];
	$customer    = new EDD_Customer( $customer_id );

	if ( empty( $customer->id ) ) {
		edd_set_error( 'edd-invalid_customer', __( 'Invalid Customer ID Provided.', 'edd' ) );
	}

	$customer_tabs = edd_customer_tabs();
	$errors        = edd_get_errors();
	?>

	<div class='wrap'>
		<h2><?php _e( 'Customer Details', 'edd' );?></h2>
		<?php if ( edd_get_errors() ) :?>
			<div class="error settings-error">
				<?php edd_print_errors(); ?>
			</div>
		<?php endif; ?>

		<?php if ( $customer ) : ?>

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

		<?php endif; ?>

	</div>
	<?php

}


/**
 * View a customer
 *
 * @since  2.3
 * @param  $customer The Customer object being displayed
 * @return void
 */
function edd_customers_view( $customer ) {
	?>

	<?php do_action( 'edd_customer_card_top', $customer ); ?>

	<div class="info-wrapper customer-section">

		<form id="edit-customer-info" method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-customers&view=overview&id=' . $customer->id ); ?>">

			<div class="customer-info">

				<div class="avatar-wrap left" id="customer-avatar">
					<?php echo get_avatar( $customer->email ); ?><br />
					<span class="info-item editable customer-edit-link"><a title="<?php _e( 'Edit Customer', 'edd' ); ?>" href="#" id="edit-customer"><?php _e( 'Edit Customer', 'edd' ); ?></a></span>
				</div>

				<div class="customer-id right">
					#<?php echo $customer->id; ?>
				</div>

				<div class="customer-address-wrapper right">
				<?php if ( isset( $customer->user_id ) ) : ?>
					<?php $address = get_user_meta( $customer->user_id, '_edd_user_address', true ); ?>
					<?php if ( ! empty( $address ) ) : ?>
					<strong><?php _e( 'Customer Address', 'edd' ); ?></strong>
					<span class="customer-address info-item editable">
						<span class="info-item" data-key="line1"><?php echo $address['line1']; ?></span>
						<span class="info-item" data-key="line2"><?php echo $address['line2']; ?></span>
						<span class="info-item" data-key="city"><?php echo $address['city']; ?></span>
						<span class="info-item" data-key="state"><?php echo $address['state']; ?></span>
						<span class="info-item" data-key="country"><?php echo $address['country']; ?></span>
						<span class="info-item" data-key="zip"><?php echo $address['zip']; ?></span>
					</span>
					<?php endif; ?>
					<span class="customer-address info-item edit-item">
						<input class="info-item" type="text" data-key="line1" name="customerinfo[line1]" placeholder="<?php _e( 'Address 1', 'edd' ); ?>" value="<?php echo $address['line1']; ?>" />
						<input class="info-item" type="text" data-key="line2" name="customerinfo[line2]" placeholder="<?php _e( 'Address 2', 'edd' ); ?>" value="<?php echo $address['line2']; ?>" />
						<input class="info-item" type="text" data-key="city" name="customerinfo[city]" placeholder="<?php _e( 'City', 'edd' ); ?>" value="<?php echo $address['city']; ?>" />
						<select data-key="country" name="customerinfo[country]" id="billing_country" class="billing_country edd-select edit-item">
							<?php

							$selected_country = edd_get_shop_country();
							$selected_country = $address['country'];

							$countries = edd_get_country_list();
							foreach( $countries as $country_code => $country ) {
								echo '<option value="' . esc_attr( $country_code ) . '"' . selected( $country_code, $selected_country, false ) . '>' . $country . '</option>';
							}
							?>
						</select>
						<?php
						$selected_state = edd_get_shop_state();
						$states         = edd_get_shop_states( $selected_country );

						$selected_state = isset( $address['state'] ) ? $address['state'] : $selected_state;

						if( ! empty( $states ) ) : ?>
						<select data-key="state" name="customerinfo[state]" id="card_state" class="card_state edd-select info-item">
							<?php
								foreach( $states as $state_code => $state ) {
									echo '<option value="' . $state_code . '"' . selected( $state_code, $selected_state, false ) . '>' . $state . '</option>';
								}
							?>
						</select>
						<?php else : ?>
						<input type="text" size="6" data-key="state" name="customerinfo[state]" id="card_state" class="card_state edd-input info-item" placeholder="<?php _e( 'State / Province', 'edd' ); ?>"/>
						<?php endif; ?>
						<input class="info-item" type="text" data-key="zip" name="customerinfo[zip]" placeholder="<?php _e( 'Postal', 'edd' ); ?>" value="<?php echo $address['zip']; ?>" />
					</span>
				<?php endif; ?>
				</div>

				<span class="customer-name info-item edit-item"><input size="15" data-key="name" name="customerinfo[name]" type="text" value="<?php echo $customer->name; ?>" placeholder="<?php _e( 'Customer Name', 'edd' ); ?>" /></span>
				<span class="customer-name info-item editable"><span data-key="name"><?php echo $customer->name; ?></span></span>
				<span class="customer-name info-item edit-item"><input size="20" data-key="email" name="customerinfo[email]" type="text" value="<?php echo $customer->email; ?>" placeholder="<?php _e( 'Customer Email', 'edd' ); ?>" /></span>
				<span class="customer-email info-item editable" data-key="email"><?php echo $customer->email; ?></span>
				<span class="customer-since info-item">
					<?php _e( 'Customer since', 'edd' ); ?>
					<?php echo date_i18n( get_option( 'date_format' ), strtotime( $customer->date_created ) ) ?>
				</span>
				<span class="customer-user-id info-item edit-item">
					<?php
					$customers = EDD()->customers->get_customers( array( 'number' => -1 ) );
					foreach ( $customers as $key => $customer_search ) {
						if ( $customer_search->id == $customer->id ) {
							unset( $customers[$key] );
							break;
						}
					}
					$user_ids  = wp_list_pluck( $customers, 'user_id' );
					$user_dropdown_args = array(
						'name'                    => 'customerinfo[user_id]',
						'selected'                =>  $customer->user_id,
						'include_selected'        => true,
						'echo'                    => '0',
						'show_option_none'        => __( 'None', 'edd' ),
						'class'                   => 'edd-user-dropdown',
						'exclude'                 => $user_ids,
						'hide_if_only_one_author' => false
					);
					$users_dropdown = wp_dropdown_users( $user_dropdown_args );
					$find           = array( 'class=\'edd-user-dropdown\'', 'value=\'-1\'' );
					$replace        = array( 'data-key=\'user_id\' class=\'edd-user-dropdown\'', 'value=\'0\'' );
					$users_dropdown = str_replace( $find, $replace, $users_dropdown );

					echo $users_dropdown;
					?>
				</span>

				<span class="customer-user-id info-item editable">
					<?php _e( 'User ID', 'edd' ); ?>:&nbsp;
					<span data-key="user_id"><?php echo $customer->user_id; ?></span>
				</span>

			</div>

			<span id="customer-edit-actions" class="edit-item">
				<input type="hidden" data-key="id" name="customerinfo[id]" value="<?php echo $customer->id; ?>" />
				<?php wp_nonce_field( 'edit-customer', '_wpnonce', false, true ); ?>
				<input type="submit" id="edd-edit-customer-save" class="button-secondary" value="<?php _e( 'Update Customer', 'edd' ); ?>" />
				<a id="edd-edit-customer-cancel" href="" class="delete"><?php _e( 'Cancel', 'edd' ); ?></a>
			</span>

		</form>
	</div>

	<?php do_action( 'edd_customer_before_stats', $customer ); ?>

	<div id="customer-stats-wrapper" class="customer-section">
		<ul>
			<li>
				<a title="<?php _e( 'View All Purchases', 'edd' ); ?>" href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-payment-history&user=' . urlencode( $customer->email ) ); ?>">
					<span class="dashicons dashicons-cart"></span>
					<?php echo $customer->purchase_count; ?> <?php _e( 'Completed Sales' ,'edd' ); ?>
				</a>
			</li>
			<li>
				<span class="dashicons dashicons-chart-area"></span>
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
				<?php if ( ! empty( $payments ) ) : ?>
					<?php foreach ( $payments as $payment ) : ?>
						<tr>
							<td><?php echo $payment->ID; ?></td>
							<td><?php echo edd_payment_amount( $payment->ID ); ?></td>
							<td><?php echo date_i18n( get_option( 'date_format' ), strtotime( $payment->post_date ) ); ?></td>
							<td><?php echo edd_get_payment_status( $payment, true ); ?></td>
							<td>
								<a title="<?php _e( 'View Details for Payment', 'edd' ); echo ' ' . $payment->ID; ?>" href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $payment->ID ); ?>">
									<?php _e( 'View Details', 'edd' ); ?>
								</a>
								<?php do_action( 'edd_customer_recent_purcahses_actions', $customer, $payment ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else: ?>
					<tr><td colspan="5"><?php _e( 'No Payments Found', 'edd' ); ?></td></tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

	<?php do_action( 'edd_customer_card_bottom', $customer ); ?>

	<?php
}

/**
 * View the notes of a customer
 *
 * @since  2.3
 * @param  $customer The Customer being displayed
 * @return void
 */
function edd_customer_notes_view( $customer ) {

	$paged       = isset( $_GET['paged'] ) && is_numeric( $_GET['paged'] ) ? $_GET['paged'] : 1;
	$paged       = absint( $paged );
	$note_count  = $customer->get_notes_count();
	$per_page    = apply_filters( 'edd_customer_notes_per_page', 20 );
	$total_pages = ceil( $note_count / $per_page );

	$customer_notes = $customer->get_notes( $per_page, $paged );
	?>

	<div id="customer-notes-wrapper">
		<div class="customer-notes-header">
			<?php echo get_avatar( $customer->email, 30 ); ?> <span><?php echo $customer->name; ?></span>
		</div>
		<h3><?php _e( 'Notes', 'edd' ); ?></h3>

		<?php if ( 1 == $paged ) : ?>
		<div style="display: block; margin-bottom: 35px;">
			<form id="edd-add-customer-note" method="post" action="<?php echo admin_url( 'edit.php?post_type=download&page=edd-customers&view=notes&id=' . $customer->id ); ?>">
				<textarea id="customer-note" name="customer_note" class="customer-note-input"></textarea>
				<br />
				<input type="hidden" id="customer-id" name="customer_id" value="<?php echo $customer->id; ?>" />
				<input type="hidden" name="edd_action" value="add-customer-note" />
				<?php wp_nonce_field( 'add-customer-note', 'add_customer_note_nonce', true, true ); ?>
				<input id="add-customer-note" class="right button-primary" type="submit" value="Add Note" />
			</form>
		</div>
		<?php endif; ?>

		<?php
		$pagination_args = array(
			'base'     => '%_%',
			'format'   => '?paged=%#%',
			'total'    => $total_pages,
			'current'  => $paged,
			'show_all' => true
		);

		echo paginate_links( $pagination_args );
		?>

		<div id="edd-customer-notes">
		<?php if ( count( $customer_notes ) > 0 ) : ?>
			<?php foreach( $customer_notes as $key => $note ) : ?>
				<div class="customer-note-wrapper dashboard-comment-wrap comment-item">
					<span class="note-content-wrap">
						<?php echo stripslashes( $note ); ?>
					</span>
				</div>
			<?php endforeach; ?>
		<?php else: ?>
			<div class="edd-no-customer-notes">
				<?php _e( 'No Customer Notes', 'edd' ); ?>
			</div>
		<?php endif; ?>
		</div>

		<?php echo paginate_links( $pagination_args ); ?>

	</div>

	<?php
}
