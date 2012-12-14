<?php

if( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class EDD_Payment_History_Table extends WP_List_Table {

	var $per_page = 30;
	var $total_count;
	var $complete_count;
	var $pending_count;
	var $refunded_count;
	var $failed_count;

	function __construct(){
		global $status, $page;

		//Set parent defaults
		parent::__construct( array(
			'singular'  => edd_get_label_singular(),    // singular name of the listed records
			'plural'    => edd_get_label_plural(),    	// plural name of the listed records
			'ajax'      => false             			// does this table support ajax?
		) );

		$this->get_payment_counts();

	}

	/**
	 * Show the search field
	 *
	 * @access      private
	 * @since       1.3.4
	 * @return      void
	 */
	function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
			return;

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array('ID' => 'search-submit') ); ?>
		</p>
<?php

	}


	/**
	 * Retrieve the view types
	 *
	 * @access      private
	 * @since       1.3.4
	 * @return      array
	 */
	function get_views() {

        $base           = admin_url('edit.php?post_type=download&page=edd-payment-history');
        $current        = isset( $_GET['status'] ) ? $_GET['status'] : '';
        $total_count    = '&nbsp;<span class="count">(' . $this->total_count    . ')</span>';
        $complete_count = '&nbsp;<span class="count">(' . $this->complete_count . ')</span>';
        $pending_count  = '&nbsp;<span class="count">(' . $this->pending_count  . ')</span>';
        $refunded_count = '&nbsp;<span class="count">(' . $this->refunded_count . ')</span>';
        $failed_count   = '&nbsp;<span class="count">(' . $this->failed_count   . ')</span>';
		$views = array(
			'all'		=> sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( 'status', $base ), $current === 'all' || $current == '' ? ' class="current"' : '', __('All', 'edd') . $total_count ),
			'publish'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'publish', $base ), $current === 'publish' ? ' class="current"' : '', __('Completed', 'edd') . $complete_count ),
			'pending'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'pending', $base ), $current === 'pending' ? ' class="current"' : '', __('Pending', 'edd') . $pending_count ),
			'refunded'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'refunded', $base ), $current === 'refunded' ? ' class="current"' : '', __('Refunded', 'edd') . $refunded_count ),
			'failed'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'failed', $base ), $current === 'failed' ? ' class="current"' : '', __('Failed', 'edd') . $failed_count )
		);
		return $views;
	}


	/**
	 * Retrieve the table columnds
	 *
	 * @access      private
	 * @since       1.3.4
	 * @return      array
	 */
	function get_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			'ID'     	=> __( 'ID', 'edd' ),
			'email'  	=> __( 'Email', 'edd' ),
			'details'  	=> __( 'Details', 'edd' ),
			'amount'  	=> __( 'Amount', 'edd' ),
			'date'  	=> __( 'Date', 'edd' ),
			'user'  	=> __( 'User', 'edd' ),
			'status'  	=> __( 'Status', 'edd' )
		);
		return $columns;
	}


	/**
	 * Retrieve the table's sortable columns
	 *
	 * @access      private
	 * @since       1.3.4
	 * @return      array
	 */
	function get_sortable_columns() {
		return array(
			'ID' 		=> array( 'ID', true ),
			'amount' 	=> array( 'amount', false ),
			'date' 		=> array( 'date', false )
		);
	}


	/**
	 * Render most columns
	 *
	 * @access      private
	 * @since       1.3.4
	 * @return      string
	 */
	function column_default( $item, $column_name ) {

		switch( $column_name ){
			case 'amount' :
				return edd_currency_filter( edd_format_amount( $item[ $column_name ] ) );
			case 'date' :
				$date = strtotime( $item[ $column_name ] );
				return date_i18n( get_option( 'date_format' ), $date );
			case 'status' :
				$payment = get_post( $item['ID'] );
				return edd_get_payment_status( $payment, true );
			default:
				return $item[ $column_name ];
		}
	}


	/**
	 * Render the email column
	 *
	 * @access      private
	 * @since       1.3.4
	 * @return      string
	 */
	function column_email( $item ) {

     	$payment     = get_post( $item['ID'] );
        $base        = admin_url( 'edit.php?post_type=download&page=edd-payment-history&edd-action=edit-payment&purchase_id=' . $item['ID'] );

		$row_actions = array();

		$row_actions['edit'] = '<a href="' . add_query_arg( array( 'edd-action' => 'edit-payment', 'purchase_id' => $payment->ID ) ) . '">' . __( 'Edit', 'edd' ) . '</a>';

		if( edd_is_payment_complete( $payment->ID ) )
			$row_actions['email_links'] = '<a href="' . add_query_arg( array( 'edd-action' => 'email_links', 'purchase_id' => $payment->ID) ) . '">' . __( 'Resend Purchase Receipt', 'edd' ) . '</a>';

		$row_actions['delete'] = '<a href="' . wp_nonce_url( add_query_arg( array( 'edd-action' => 'delete_payment', 'purchase_id' => $payment->ID ) ), 'edd_payment_nonce') . '">' . __( 'Delete', 'edd' ) . '</a>';

		$row_actions = apply_filters( 'edd_payment_row_actions', $row_actions, $payment );

		return $item['email'] . $this->row_actions( $row_actions );
	}


	/**
	 * Render the checkbox column
	 *
	 * @access      private
	 * @since       1.3.4
	 * @return      string
	 */
	function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],
            /*$2%s*/ $item['ID']
        );
    }


    /**
	 * Render the details column
	 *
	 * @access      private
	 * @since       1.3.4
	 * @return      string
	 */
	function column_details( $item ) {

		$details = "<a href='#TB_inline?width=640&amp;inlineId=purchased-files-" . $item['ID'] . "' class='thickbox' title='" . sprintf( __( 'Purchase Details for Payment #%s', 'edd' ), $item['ID'] ) . "'>" . __( 'View Order Details', 'edd' ) . "</a>";

		ob_start();
?>
			<div id="purchased-files-<?php echo $item['ID']; ?>" style="display:none;">
				<?php
					$payment_meta = edd_get_payment_meta( $item['ID'] );
					$cart_items = isset( $payment_meta['cart_details'] ) ? maybe_unserialize($payment_meta['cart_details']) : false;
					if( empty( $cart_items ) || !$cart_items ) {
						$cart_items = maybe_unserialize( $payment_meta['downloads'] );
					}
				?>
				<h4><?php echo _n( __( 'Purchased File', 'edd' ), __( 'Purchased Files', 'edd' ), count( $cart_items ) ); ?></h4>
				<ul class="purchased-files-list">
				<?php

					if( $cart_items ) {

						foreach( $cart_items as $key => $cart_item ) {
							echo '<li>';

								// retrieve the ID of the download
								$id = isset( $payment_meta['cart_details'] ) ? $cart_item['id'] : $cart_item;

								// if download has variable prices, override the default price
								$price_override = isset( $payment_meta['cart_details'] ) ? $cart_item['price'] : null;

								// get the user information
								$user_info = edd_get_payment_meta_user_info( $item['ID'] );

								// calculate the final item price
								$price = edd_get_download_final_price( $id, $user_info, $price_override );

								// show name of download
								echo '<a href="' . admin_url( 'post.php?post=' . $id . '&action=edit' ) . '" target="_blank">' . get_the_title( $id ) . '</a>';

								echo  ' - ';

								if( isset( $cart_items[ $key ]['item_number'])) {

									$price_options = $cart_items[ $key ]['item_number']['options'];

									if( isset( $price_options['price_id'] ) ) {
										echo edd_get_price_option_name( $id, $price_options['price_id'] );
										echo ' - ';
									}
								}
								// show price
								echo edd_currency_filter( edd_format_amount( $price ) );

							echo '</li>';
						}
					}
?>
				</ul>
				<?php $payment_date = strtotime( $item['date'] ); ?>
				<p><?php echo __( 'Date and Time:', 'edd' ) . ' ' . date_i18n( get_option( 'date_format' ), $payment_date ) . ' ' . date_i18n( get_option( 'time_format' ), $payment_date ) ?>
				<p><?php echo __( 'Discount used:', 'edd' ) . ' '; if( isset( $user_info['discount'] ) && $user_info['discount'] != 'none' ) { echo $user_info['discount']; } else { _e( 'none', 'edd' ); } ?>
				<p><?php echo __( 'Total:', 'edd' ) . ' ' . edd_currency_filter( edd_format_amount( edd_get_payment_amount( $item['ID'] ) ) ); ?></p>

				<div class="purcase-personal-details">
					<h4><?php _e( 'Buyer\'s Personal Details:', 'edd' ); ?></h4>
					<ul>
						<li><?php echo __( 'Name:', 'edd' ) . ' ' . $user_info['first_name'] . ' ' . $user_info['last_name']; ?></li>
						<li><?php echo __( 'Email:', 'edd' ) . ' ' . $payment_meta['email']; ?></li>
						<?php do_action( 'edd_payment_personal_details_list', $payment_meta, $user_info ); ?>
					</ul>
				</div>

				<?php
				$gateway = edd_get_payment_gateway( $item['ID'] );
				if( $gateway ) { ?>
				<div class="payment-method">
					<h4><?php _e('Payment Method:', 'edd'); ?></h4>
					<span class="payment-method-name"><?php echo edd_get_gateway_admin_label( $gateway ); ?></span>
				</div>
				<?php } ?>
				<div class="purchase-key-wrap">
					<h4><?php _e('Purchase Key', 'edd'); ?></h4>
					<span class="purchase-key"><?php echo $payment_meta['key']; ?></span>
				</div>
				<p><a id="edd-close-purchase-details" class="button-secondary" onclick="tb_remove();" title="<?php _e('Close', 'edd'); ?>"><?php _e('Close', 'edd'); ?></a></p>
			</div>
<?php
			$details .= ob_get_clean();
		return $details;
	}


	/**
	 * Render the user column
	 *
	 * @access      private
	 * @since       1.3.4
	 * @return      string
	 */
	function column_user( $item ) {

		$user_info = edd_get_payment_meta_user_info( $item['ID'] );
		$user_id = isset( $user_info['id'] ) && $user_info['id'] != -1 ? $user_info['id'] : $user_info['email'];

		if( is_numeric( $user_id ) ) {
			$user = get_userdata( $user_id ) ;
			$display_name = is_object( $user ) ? $user->display_name : __('guest', 'edd');
		} else {
			$display_name = __( 'guest', 'edd' );
		}

		return '<a href="' . remove_query_arg( 'paged', add_query_arg( 'user', $user_id ) ) . '">' . $display_name . '</a>';
	}


	/**
	 * Retrieve the bulk actions
	 *
	 * @access      private
	 * @since       1.3.4
	 * @return      array
	 */
	function get_bulk_actions() {

		$actions = array(
			'delete' => __( 'Delete', 'edd' )
        );

        return $actions;

    }


    /**
	 * Process the bulk actions
	 *
	 * @access      private
	 * @since       1.3.4
	 * @return      void
	 */
    function process_bulk_action() {

        $ids = isset( $_GET['download'] ) ? $_GET['download'] : false;

        if ( ! is_array( $ids ) )
            $ids = array( $ids );

        foreach ( $ids as $id ) {
            // Detect when a bulk action is being triggered...
            if ( 'delete' === $this->current_action() ) {
                edd_delete_purchase( $id );
            }
        }

    }


    /**
	 * Retrieve the payment counts
	 *
	 * @access      private
	 * @since       1.3.4
	 * @return      array
	 */
	function get_payment_counts() {

		$payment_count 	= wp_count_posts( 'edd_payment' );

		$this->complete_count = $payment_count->publish;
		$this->pending_count  = $payment_count->pending;
		$this->refunded_count = $payment_count->refunded;
		$this->failed_count    = $payment_count->failed;
		$this->total_count    = $payment_count->publish + $payment_count->pending + $payment_count->refunded + $payment_count->failed + $payment_count->trash;

	}


	/**
	 * Retrieve all payment data
	 *
	 * @access      private
	 * @since       1.3.4
	 * @return      array
	 */
	function payments_data() {

		$payments_data = array();

		if( isset( $_GET['paged'] ) ) $page = $_GET['paged']; else $page = 1;

		$per_page = $this->per_page;

		$mode = edd_is_test_mode() ? 'test' : 'live';

		$orderby 		= isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'ID';
		$order 			= isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
		$order_inverse 	= $order == 'DESC' ? 'ASC' : 'DESC';
		$order_class 	= strtolower( $order_inverse );
		$user 			= isset( $_GET['user'] ) ? $_GET['user'] : null;
		$status 		= isset( $_GET['status'] ) ? $_GET['status'] : 'any';
		$meta_key		= isset( $_GET['meta_key'] ) ? $_GET['meta_key'] : null;
		$year 			= isset( $_GET['year'] ) ? $_GET['year'] : null;
		$month 			= isset( $_GET['m'] ) ? $_GET['m'] : null;
		$day 			= isset( $_GET['day'] ) ? $_GET['day'] : null;
		$search         = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null;

		$payments = edd_get_payments( array(
			'number'   => $per_page,
			'page'     => isset( $_GET['paged'] ) ? $_GET['paged'] : null,
			'mode'     => $mode,
			'orderby'  => $orderby,
			'order'    => $order,
			'user'     => $user,
			'status'   => $status,
			'meta_key' => $meta_key,
			'year'	   => $year,
			'month'    => $month,
			'day' 	   => $day,
			's'        => $search
		) );

		if( $payments ) {
			foreach( $payments as $payment ) {

				$payment_meta 	= edd_get_payment_meta( $payment->ID );
				$user_info 		= edd_get_payment_meta_user_info( $payment->ID );
				$cart_details	= edd_get_payment_meta_cart_details( $payment->ID );

				$user_id = isset( $user_info['ID'] ) && $user_info['ID'] != -1 ? $user_info['ID'] : $user_info['email'];

				$payments_data[] = array(
					'ID' 		=> $payment->ID,
					'email' 	=> $payment_meta['email'],
					'products' 	=> $cart_details,
					'amount' 	=> edd_get_payment_amount( $payment->ID ),
					'date' 		=> $payment->post_date,
					'user' 		=> $user_id,
					'status' 	=> $payment->post_status
				);
			}
		}
		return $payments_data;
	}


	/**
	 * Setup the final data for the table
	 *
	 * @access      private
	 * @since       1.3.4
	 * @return      array
	 */
	function prepare_items() {

		/**
		 * First, lets decide how many records per page to show
		 */
		$per_page = $this->per_page;

		$columns = $this->get_columns();

		$hidden = array(); // no hidden columns

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		$data = $this->payments_data();

		$current_page = $this->get_pagenum();

		$status = isset( $_GET['status'] ) ? $_GET['status'] : 'any';

		switch( $status ) {
			case 'publish':
				$total_items = $this->complete_count;
				break;
			case 'pending':
				$total_items = $this->pending_count;
				break;
			case 'refunded':
				$total_items = $this->refunded_count;
				break;
			case 'failed':
				$total_items = $this->failed_count;
				break;
			case 'any':
				$total_items = $this->total_count;
				break;
		}

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,                  	// WE have to calculate the total number of items
				'per_page'    => $per_page,                     	// WE have to determine how many items to show on a page
				'total_pages' => ceil( $total_items / $per_page )   // WE have to calculate the total number of pages
			)
		);
	}

}