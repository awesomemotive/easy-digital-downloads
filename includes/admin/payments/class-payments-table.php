<?php

if( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class EDD_Payment_History_Table extends WP_List_Table {

	var $per_page = 30;

	function __construct(){
		global $status, $page;
			   
		//Set parent defaults
		parent::__construct( array(
			'singular'  => edd_get_label_singular(),    // singular name of the listed records
			'plural'    => edd_get_label_plural(),    	// plural name of the listed records
			'ajax'      => false             			// does this table support ajax?
		) );

	}

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
	<?php submit_button( $text, 'button', false, false, array('id' => 'search-submit') ); ?>
</p>
<?php
	}

	function get_views() {

        $base = admin_url('edit.php?post_type=download&page=edd-payment-history');
        $current = isset( $_GET['status'] ) ? $_GET['status'] : '';
        $views = array(
            'all'       => sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( 'status', $base ), $current === 'all' || $current == '' ? ' class="current"' : '', __('All') ),
            'unpaid'    => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'unpaid', $base ), $current === 'unpaid' ? ' class="current"' : '', __('Unpaid') ),
            'paid'      => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'paid', $base ), $current === 'paid' ? ' class="current"' : '', __('Paid') )
        );		
		$views = array(
			'all'		=> sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( 'status', $base ), $current === 'all' || $current == '' ? ' class="current"' : '', __('All') ),
			'publish'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'publish', $base ), $current === 'publish' ? ' class="current"' : '', __('Complete') ),
			'pending'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'pending', $base ), $current === 'pending' ? ' class="current"' : '', __('Pending') ),
			'refunded'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'refunded', $base ), $current === 'refunded' ? ' class="current"' : '', __('Refunded') )
		);
		return $views;
	}

	function get_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			'id'     	=> __( 'ID', 'edd' ),
			'email'  	=> __( 'Email', 'edd' ),
			'details'  	=> __( 'Details', 'edd' ),
			'amount'  	=> __( 'Amount', 'edd' ),
			'user'  	=> __( 'User', 'edd' ),
			'status'  	=> __( 'Status', 'edd' ),
			'date'  	=> __( 'Date', 'edd' )
		);
		return $columns;
	}


	function get_sortable_columns() {
		return array(
			'id' 		=> array( 'id', true ),
			'amount' 	=> array( 'amount', false ),
			'date' 		=> array( 'date', false ),
			'status' 	=> array( 'status', false )
		);
	}

	function column_default( $item, $column_name ) {
		switch( $column_name ){
			case 'amount' :
				return edd_currency_filter( edd_format_amount( $item[ $column_name ] ) );
			case 'date' :
				$date = strtotime( $item[ $column_name ] );
				return date_i18n( get_option( 'date_format' ), $date );
			case 'status' :
				$payment = get_post( $item['id'] );
				return edd_get_payment_status( $payment, true );
			default:
				return $item[ $column_name ];
		}
	}

	function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],
            /*$2%s*/ $item['id']
        );
    }

	function column_details( $item ) {
		$details = "<a href='#TB_inline?width=640&amp;inlineId=purchased-files-" . $item['id'] . "' class='thickbox' title='" . sprintf( __( 'Purchase Details for Payment #%s', 'edd' ), $item['id'] ) . "'>" . __( 'View Order Details', 'edd' ) . "</a>";
		ob_start(); ?>
			<div id="purchased-files-<?php echo $item['id']; ?>" style="display:none;">
				<?php 
					$payment_meta = edd_get_payment_meta( $item['id'] );
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
								$user_info = edd_get_payment_meta_user_info( $item['id'] );
								
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
				<p><?php echo __( 'Total:', 'edd' ) . ' ' . edd_currency_filter( edd_format_amount( edd_get_payment_amount( $item['id'] ) ) ); ?></p>
				
				<div class="purcase-personal-details">
					<h4><?php _e( 'Buyer\'s Personal Details:', 'edd' ); ?></h4>
					<ul>
						<li><?php echo __( 'Name:', 'edd' ) . ' ' . $user_info['first_name'] . ' ' . $user_info['last_name']; ?></li>
						<li><?php echo __( 'Email:', 'edd' ) . ' ' . $payment_meta['email']; ?></li>
						<?php do_action( 'edd_payment_personal_details_list', $payment_meta, $user_info ); ?>
					</ul>
				</div>
				
				<?php
				$gateway = edd_get_payment_gateway( $item['id'] );
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


	function bulk_actions() {
		
	}


	function payments_data() {

		$payments_data = array();

		if( isset( $_GET['paged'] ) ) $page = $_GET['paged']; else $page = 1;
		
		$per_page = $this->per_page;
		
		if( isset( $_GET['show'] ) && $_GET['show'] > 0 ) {
			$per_page = intval( $_GET['show'] );
		}

		$offset = $per_page * ( $page - 1 );

		$mode = isset( $_GET['mode'] ) ? $_GET['mode'] : 'live';
		if( edd_is_test_mode() && !isset( $_GET['mode'] ) ) $mode = 'test';
		
		$orderby 		= isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'ID';
		$order 			= isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
		$order_inverse 	= $order == 'DESC' ? 'ASC' : 'DESC';
		$order_class 	= strtolower($order_inverse);
		$user 			= isset( $_GET['user'] ) ? $_GET['user'] : null;
		$status 		= isset( $_GET['status'] ) ? $_GET['status'] : 'any';
		$meta_key		= isset( $_GET['meta_key'] ) ? $_GET['meta_key'] : null;

		$payments = edd_get_payments( array(
			'offset'   => $offset,
			'number'   => $per_page, 
			'mode'     => $mode, 
			'orderby'  => $orderby, 
			'order'    => $order, 
			'user'     => $user, 
			'status'   => $status, 
			'meta_key' => $meta_key 
		) );

		if( $payments ) {
			foreach( $payments as $payment ) {
				
				$payment_meta 	= edd_get_payment_meta( $payment->ID );
				$user_info 		= edd_get_payment_meta_user_info( $payment->ID );
				$cart_details	= edd_get_payment_meta_cart_details( $payment->ID );

				$user_id = isset( $user_info['id'] ) && $user_info['id'] != -1 ? $user_info['id'] : $user_info['email'];

				$payments_data[] = array(
					'id' 		=> $payment->ID,
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

   
	/** ************************************************************************
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 **************************************************************************/

	function prepare_items() {
	   
		/**
		 * First, lets decide how many records per page to show
		 */
		$per_page = $this->per_page;

		$columns = $this->get_columns();

		$hidden = array(); // no hidden columns

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		 
		$data = $this->payments_data();

		$current_page = $this->get_pagenum();
	
		$payment_count 	= wp_count_posts( 'edd_payment' );

		$total_count 	= $payment_count->publish + $payment_count->pending + $payment_count->refunded + $payment_count->trash;

		$status 		= isset( $_GET['status'] ) ? $_GET['status'] : 'any';

		switch( $status ) {
			case 'publish':
				$total_items = $payment_count->publish;
				break;
			case 'pending':
				$total_items = $payment_count->pending;
				break;
			case 'refunded':
				$total_items = $payment_count->refunded;
				break;
			case 'any':
				$total_items = $total_count;
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