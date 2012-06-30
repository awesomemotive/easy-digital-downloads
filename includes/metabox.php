<?php
/**
 * Metabox Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Metabox Functions
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/


/**
 * Add Download Meta Box
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_add_download_meta_box() {
	add_meta_box('downloadinformation', sprintf(__('%1$s Configuration', 'edd'), edd_get_label_singular(), edd_get_label_plural() ),  'edd_render_download_meta_box', 'download', 'normal', 'default');
	add_meta_box('edd_download_stats', sprintf( __('%1$s Stats', 'edd'), edd_get_label_singular(), edd_get_label_plural() ), 'edd_render_stats_meta_box', 'download', 'side', 'high');
	add_meta_box('edd_purchase_log', __('Purchase Log', 'edd'), 'edd_render_purchase_log_meta_box', 'download', 'normal', 'default');
	add_meta_box('edd_file_download_log', __('File Download Log', 'edd'), 'edd_render_download_log_meta_box', 'download', 'normal', 'default');
	add_meta_box('edd_payment_info', __('Payment Info', 'edd'), 'edd_render_payment_meta_box', 'edd_payment', 'normal', 'high');
}
add_action('add_meta_boxes', 'edd_add_download_meta_box');


/**
 * Render Download Meta Box
 *
 * Render the download information meta box.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_render_download_meta_box() {
	global $post, $edd_options;
	
	// Use nonce for verification
	echo '<input type="hidden" name="edd_download_meta_box_nonce" value="' . wp_create_nonce(basename(__FILE__)) . '" />';
	
	echo '<table class="form-table">';

		// fields are attached with hooks
		do_action('edd_meta_box_fields', $post->ID);

	echo '</table>';
}


/**
 * Render Price Field
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_render_price_field($post_id) {
	global $edd_options;
	$variable_pricing = get_post_meta($post_id, '_variable_pricing', true);
	$price = get_post_meta($post_id, 'edd_price', true);
	echo '<tr id="edd_price" class="edd_table_row">';
		echo '<th style="width:20%"><label for="edd_price">' . __('Pricing', 'edd') . '</label></th>';
		echo '<td>';
			echo '<p>';
				echo '<input type="checkbox" name="_variable_pricing" id="edd_variable_pricing" value="1" ' . checked(1, $variable_pricing, false) . '/>&nbsp;';
				echo __('Check this to enable variable pricing.', 'edd') . '<br/>';
			echo '</p>';
			echo '<p>';
				
				// check to see which pricing fields should be displayed
				$price_display = $variable_pricing ? ' style="display:none;"' : '';
				$variable_display = $variable_pricing ? '' : ' style="display:none;"';
				
				/*
				|--------------------------------------------------------------------------
				| VARIABLE PRICING
				|--------------------------------------------------------------------------
				*/
				
				$prices = get_post_meta($post_id, 'edd_variable_prices', true);
				// variable pricing (multiple pricing options)
				$field_html = '<input type="hidden" id="edd_variable_prices" class="edd_variable_prices_name_field" value=""/>';
				if(is_array($prices)) {
					$count = 1;
					foreach($prices as $key => $value) {
						$field_html .= '<div class="edd_variable_prices_wrapper">';
							$name = isset($prices[$key]['name']) ? $prices[$key]['name'] : '';
							$amount = isset($prices[$key]['amount']) ? $prices[$key]['amount'] : '';
							$field_html .= '<input type="text" class="edd_variable_prices_name" placeholder="' . __('price option name', 'edd') . '" name="edd_variable_prices[' . $key . '][name]" id="edd_variable_prices[' . $key . '][name]" value="' . esc_attr( $name ) . '" size="20" style="width:50%" />';
							$field_html .= '<input type="text" class="edd_variable_prices_amount text" placeholder="' . __('9.99', 'edd') . '" name="edd_variable_prices[' . $key . '][amount]" id="edd_variable_prices[' . $key . '][amount]" value="' . $amount . '" size="30" style="width:50px;" />';
						if($count > 1) {
							$field_html .= '<a href="#" class="edd_remove_repeatable button-secondary">x</a><br/>';
						}
						$field_html .= '</div>';
						$count++;
					}
				} else {
					$field_html .= '<div class="edd_variable_prices_wrapper">';
						$field_html .= '<input type="text" class="edd_variable_prices_name" placeholder="' . __('price name', 'edd') . '" name="edd_variable_prices[0][name]" id="edd_variable_prices[0][name]" value="" size="20" style="width:50%" />';
						$field_html .= '<input type="text" class="edd_variable_prices_amount" placeholder="' . __('9.99', 'edd') . '" name="edd_variable_prices[0][amount]" id="edd_variable_prices[0][amount]" value="" size="30" style="width:50px;" />';
					$field_html .= '</div>';
				}
				$field_html .= '<button class="edd_add_new_price button-secondary">' . __('Add New Price Option', 'edd') . '</button>&nbsp;&nbsp;';
				
				echo '<div id="edd_variable_price_fields" class="edd_pricing_fields" ' . $variable_display . '>' . $field_html . '</div>';
					
				/*
				|--------------------------------------------------------------------------
				| DEFAULT, SINGLE PRICE
				|--------------------------------------------------------------------------
				*/
				
				echo '<div id="edd_regular_price_field" class="edd_pricing_fields" ' . $price_display . '>';
					if(!isset($edd_options['currency_position']) || $edd_options['currency_position'] == 'before') {
						echo edd_currency_filter('') . '<input type="text" name="edd_price" id="edd_price" value="', $price ? $price : '', '" size="30" style="width:50px;" placeholder="9.99"/>';
					} else {
						echo '<input type="text" name="edd_price" id="edd_price" value="', $price ? $price : '', '" size="30" style="width:50px;" placeholder="9.99"/>' . edd_currency_filter('');
					}
					echo __('Enter the download price. Do not include a currency symbol', 'edd');						
				echo '</div>';
			echo '</p>';
		echo '</td>';
	echo '</tr>';
}
add_action('edd_meta_box_fields', 'edd_render_price_field', 10);


/**
 * Render Files Field
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_render_files_field($post_id) {
	
	// downloadable files
	
	$files = get_post_meta($post_id, 'edd_download_files', true);
	$variable_pricing = get_post_meta($post_id, '_variable_pricing', true);
	$prices = get_post_meta($post_id, 'edd_variable_prices', true);
	$variable_display = $variable_pricing ? '' : ' style="display:none;"';
	
	echo '<tr id="edd_download_files" class="edd_table_row">';
		echo '<th style="width:20%"><label for="edd_download_files">' . __('Download Files', 'edd') . '</label></th>';
		echo '<td>';
			$field_html = '<div class="edd_file_help_labels">';
				$field_html	.= '<div class="edd_files_name_label">' . __('File Name', 'edd') . '</div>';
				$field_html	.= '<div class="edd_files_src_label">' . __('File URL', 'edd') . '</div>';
			$field_html .= '</div>';
			$field_html .= '<input type="hidden" id="edd_download_files" class="edd_repeatable_upload_name_field" value=""/>';
			
			if(is_array($files)) {
				$count = 1;
				foreach($files as $key => $value) {
					$field_html .= '<div class="edd_repeatable_upload_wrapper">';
						$name = isset($files[$key]['name']) ? $files[$key]['name'] : '';
						$file = isset($files[$key]['file']) ? $files[$key]['file'] : '';
						$condition = isset($files[$key]['condition']) ? $files[$key]['condition'] : false;
						$field_html .= '<input type="text" class="edd_repeatable_name_field" placeholder="' . __('file name', 'edd') . '" name="edd_download_files[' . $key . '][name]" id="edd_download_files[' . $key . '][name]" value="' . $name . '" size="20" style="width:20%" />';
						$field_html .= '<input type="text" class="edd_repeatable_upload_field edd_upload_field" placeholder="' . __('file url', 'edd') . '" name="edd_download_files[' . $key . '][file]" id="edd_download_files[' . $key . '][file]" value="' . $file . '" size="30" style="width:30%" />';
						$field_html .= '<select class="edd_repeatable_condition_field" name="edd_download_files[' . $key . '][condition]" id="edd_download_files[' . $key . '][condition]" ' . $variable_display . '>';
							$field_html .= '<option value="all">' . __('All Prices', 'edd') . '</option>';
							if($prices) {
								foreach($prices as $price_key => $price) {
									$field_html .= '<option value="' . $price_key . '" ' . selected($price_key, $condition, false) . '>' . $prices[$price_key]['name'] . '</option>';
								}
							}
						$field_html .= '</select>';
						$field_html .= '<button class="button-secondary edd_upload_image_button">' . __('Upload File', 'edd') . '</button>';
					if($count > 1) {
						$field_html .= '<a href="#" class="edd_remove_repeatable button-secondary">x</a><br/>';
					}
					$field_html .= '</div>';
					$count++;
				}
			} else {
				$field_html .= '<div class="edd_repeatable_upload_wrapper">';
					$field_html .= '<input type="text" class="edd_repeatable_name_field" placeholder="' . __('file name', 'edd') . '" name="edd_download_files[0][name]" id="edd_download_files[0][name]" value="" size="20" style="width:20%" />';
					$field_html .= '<input type="text" class="edd_repeatable_upload_field edd_upload_field" placeholder="' . __('file url', 'edd') . '" name="edd_download_files[0][file]" id="edd_download_files[0][file]" value="" size="30" style="width:50%" />';
					$field_html .= '<button class="button-secondary edd_upload_image_button">' . __('Upload File', 'edd') . '</button>';
				$field_html .= '</div>';
			}
			$field_html .= '<button class="edd_add_new_upload_field button-secondary">' . __('Add New', 'edd') . '</button>&nbsp;&nbsp;' . __('Upload the downloadable files.', 'edd');		

			echo $field_html;
		echo '</td>';
	echo '</tr>';
}
add_action('edd_meta_box_fields', 'edd_render_files_field', 20);


/**
 * Render Purchase Text Field
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_render_purchase_text_field($post_id) {
	// purchase text
	$purchase_text = get_post_meta($post_id, '_edd_purchase_text', true);
	echo '<tr id="_edd_purchase_text" class="edd_table_row">';
		echo '<th style="width:20%"><label for="edd_purchase_text">' . __('Purchase Text', 'edd') . '</label></th>';
		echo '<td>';
			echo '<input type="text" name="_edd_purchase_text" id="edd_purchase_text" value="', $purchase_text ? $purchase_text : '', '" size="30" class="regular-text" style="width: 40%;" placeholder="' . __('Purchase', 'edd') . '"/>' . __('Add the text you would like displayed for the purchase text', 'edd'); // field description				
		echo '</td>';
	echo '</tr>';
}
add_action('edd_meta_box_fields', 'edd_render_purchase_text_field', 30);


/**
 * Render Link Styles
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_render_link_styles($post_id) {
	// link style
	$link_style = get_post_meta($post_id, '_edd_purchase_style', true);
	echo '<tr id="edd_purchase_style" class="edd_table_row">';
		echo '<th style="width:20%"><label for="_edd_purchase_style">' . __('Link Style', 'edd') . '</label></th>';
		echo '<td>';
			echo '<input type="radio" name="_edd_purchase_style" value="button" ' . checked($link_style, 'button', false) . '/>&nbsp;' . __('Button', 'edd') . '&nbsp;';				
			echo '<input type="radio" name="_edd_purchase_style" value="text" ' . checked($link_style, 'text', false) . '/>&nbsp;' . __('Text', 'edd') . '&nbsp;<br/>';		
			echo __('Choose the style of the purchase link', 'edd');
		echo '</td>';
	echo '</tr>';
}
add_action('edd_meta_box_fields', 'edd_render_link_styles', 40);	


/**
 * Render Button Color
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_render_button_color($post_id) {	
	//	button color
	$button_color = get_post_meta($post_id, '_edd_purchase_color', true);
	$link_style = get_post_meta($post_id, '_edd_purchase_style', true);
	$display = $link_style == 'button' ? '' : ' style="display:none;"';
	echo '<tr id="edd_purchase_color" class="edd_table_row"' . $display . '>';
		echo '<th style="width:20%"><label for="_edd_purchase_color">' . __('Button Color', 'edd') . '</label></th>';
		echo '<td>';
			echo '<select name="_edd_purchase_color">';
				$colors = edd_get_button_colors();
				foreach($colors as $key => $color) {
					echo '<option value="' . $key . '" ' . selected($key, $button_color, false) . '>' . $color . '</option>';
				}
			echo '</select>&nbsp;';
			echo __('Choose the color of the purchase link, if button was selected above.', 'edd');
		echo '</td>';
	echo '</tr>';
}
add_action('edd_meta_box_fields', 'edd_render_button_color', 50);


/**
 * Render Disable Button
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_render_disable_button($post_id) {	
	$show_button = get_post_meta($post_id, '_edd_hide_purchase_link', true);
	echo '<tr id="edd_hide_purchase_link" class="edd_table_row">';
		echo '<th style="width:20%"><label for="_edd_hide_purchase_link">' . __('Disable the purchase button?', 'edd') . '</label></th>';
		echo '<td>';
			echo '<input type="checkbox" name="_edd_hide_purchase_link" id="_edd_hide_purchase_link" value="1" ' . checked(1, $show_button, false) . '/>&nbsp;';
			echo __('Check this if you do not want the purchase button displayed.', 'edd');
		echo '</td>';
	echo '</tr>';
}
add_action('edd_meta_box_fields', 'edd_render_disable_button', 60);


/**
 * Render Meta Notes
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_render_meta_notes($post_id) {
	// notes
	echo '<tr>';
		echo '<td style="width:20%" colspan=2><strong>' . __('Notes', 'edd') . '</strong></td>';
	echo '</tr>';
	echo '<tr>';
		echo '<td colspan=2>' . __('The style options above do NOT reflect the style of short code. The short code allows you to place a purchase button for this download anywhere on the site.', 'edd') . '</td>';
	echo '</tr>';
	
	// short code
	echo '<tr>';
		echo '<th style="width:20%"><label>' . __('Short Code', 'edd') . '</label></th>';
		echo '<td><em>[purchase_link id="' . $post_id . '" text="' . __('Purchase', 'edd') . '" style="button" color="' . get_post_meta( $post_id, '_edd_purchase_color', true ) . '"]</em><br/>' . __('This short code can be placed anywhere on your site', 'edd') . '</td>';
	echo '</tr>';
}
add_action('edd_meta_box_fields', 'edd_render_meta_notes', 70);
	

/**
 * Download Meta Box Save
 *
 * Save data from meta box.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_download_meta_box_save($post_id) {
	global $post;
	
	// verify nonce
	if (isset($_POST['edd_download_meta_box_nonce']) && !wp_verify_nonce($_POST['edd_download_meta_box_nonce'], basename(__FILE__))) {
		return $post_id;
	}

	// check autosave
	if ( (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) || ( defined('DOING_AJAX') && DOING_AJAX) || isset($_REQUEST['bulk_edit']) ) return $post_id;
	
	//don't save if only a revision
	if ( isset($post->post_type) && $post->post_type == 'revision' ) return $post_id;

	// check permissions
	if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id)) {
			return $post_id;
		}
	} elseif (!current_user_can('edit_post', $post_id)) {
		return $post_id;
	}
	
	// these are the default fields that get saved
	$fields = apply_filters('edd_metabox_fields_save', array(
			'edd_price',
			'_variable_pricing',
			'edd_variable_prices',
			'edd_download_files',
			'_edd_purchase_text',
			'_edd_purchase_style',
			'_edd_purchase_color',
			'_edd_hide_purchase_link'
		)
	);
	foreach($fields as $field) {
		if(isset($_POST[$field])) {
			$old = get_post_meta($post_id, $field, true);
			if($old != $_POST[$field]) {
				if( is_string( $_POST[$field] ) ) {
					$new = esc_attr( $_POST[$field] );	
				} else {
					$new = $_POST[$field];
				}				
				update_post_meta($post_id, $field, $new);
			}
		} else {
			delete_post_meta($post_id, $field);
		}
	}

}
add_action('save_post', 'edd_download_meta_box_save');


/**
 * Render Stats Meta Box
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_render_stats_meta_box() {
	global $post;
	
	$earnings = edd_get_download_earnings_stats($post->ID);
	$sales = edd_get_download_sales_stats($post->ID);
	
	echo '<table class="form-table">';
		echo '<tr>';
			echo '<th style="width:20%">' . __('Sales:', 'edd') . '</th>';
			echo '<td class="edd_download_stats">';
				echo $sales;
			echo '</td>';
		echo '</tr>';
		echo '<tr>';
			echo '<th style="width:20%">' . __('Earnings:', 'edd') . '</th>';
			echo '<td class="edd_download_stats">';
				echo '$' . $earnings;
			echo '</td>';
		echo '</tr>';
		do_action('edd_stats_meta_box');
	echo '</table>';
}


/**
 * Render Purchase Log Meta Box
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_render_purchase_log_meta_box() {
	global $post;
	
	$sales = edd_get_download_sales_log($post->ID);
	
	$per_page = 10;	
	
	if( isset( $_GET['edd_sales_log_page'] ) ) {
		$page = intval( $_GET['edd_sales_log_page'] );
		$offset = $per_page * ( $page - 1 );
		$sales_log = edd_get_download_sales_log($post->ID, true, $per_page, $offset);
	} else {
		$page = 1;
		$sales_log = edd_get_download_sales_log($post->ID, false);
	}	
	
	echo '<table class="form-table">';
		echo '<tr>';
			echo '<th style="width:20%"><strong>' . __('Sales Log', 'edd') . '</strong></th>';
			echo '<td colspan="4" class="edd_download_stats">';
				_e('Each sale for this download is listed below.', 'edd');
			echo '</td>';
		echo '</tr>';
		if($sales_log['sales']) {
			foreach($sales_log['sales'] as $sale) {
				if($sale['user_info']['id'] != 0) {
					$user_data = get_userdata($sale['user_info']['id']);
					$name = $user_data->display_name;
				} else {
					$name = $sale['user_info']['first_name'] . ' ' . $sale['user_info']['last_name'];
				}
				echo '<tr>';
				
					echo '<td class="edd_download_sales_log">';
						echo '<strong>' . __('Date:', 'edd') . '</strong> ' . $sale['date'];
					echo '</td>';
				
					echo '<td class="edd_download_sales_log">';
						echo '<strong>' . __('Buyer:', 'edd') . '</strong> ' . $name;
					echo '</td>';
					
					echo '<td colspan="3" class="edd_download_sales_log">';
						echo '<strong>' . __('Purchase ID:', 'edd') . '</strong> <a href="' . admin_url('edit.php?post_type=download&page=edd-payment-history&purchase_id=' . $sale['payment_id'] . '&edd-action=edit-payment') . '">' . $sale['payment_id'] . '</a>';
					echo '</td>';
				echo '</tr>';
			} // endforeach
			do_action('edd_purchase_log_meta_box');
		} else {
			echo '<tr>';
				echo '<td colspan=2" class="edd_download_sales_log">';
					echo __('No sales yet', 'edd');
				echo '</td>';
			echo '</tr>';		
		}
	echo '</table>';
	
	$total_log_entries = $sales_log['number'];		
	$total_pages = ceil( $total_log_entries / $per_page );
	
	if ($total_pages > 1) :
		echo '<div class="tablenav">';
			echo '<div class="tablenav-pages alignright">';
				$base = 'post.php?post=' . $post->ID . '&action=edit%_%';		
				echo paginate_links( array(
					'base' => $base,
					'format' => '&edd_sales_log_page=%#%',
					'prev_text' => '&laquo; ' . __('Previous', 'edd'),
					'next_text' => __('Next', 'edd') . ' &raquo;',
					'total' => $total_pages,
					'current' => $page,
					'end_size' => 1,
					'mid_size' => 5,
					'add_fragment' => '#edd_purchase_log'
				));
			echo '</div>';
		echo '</div><!--end .tablenav-->';
	endif;
	
}


/**
 * Render Download Log Meta Box
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_render_download_log_meta_box() {
	global $post;
	
	$per_page = 10;	
	
	if( isset( $_GET['edd_log_page'] ) ) {
		$page = intval( $_GET['edd_log_page'] );
		$offset = $per_page * ( $page - 1 );
		$download_log = edd_get_file_download_log($post->ID, true, $per_page, $offset);
	} else {
		$page = 1;
		$download_log = edd_get_file_download_log($post->ID, true);
	}
	
	$files = edd_get_download_files($post->ID);
	
	echo '<table class="form-table">';
		echo '<tr>';
			echo '<th style="width:20%"><strong>' . __('Download Log', 'edd') . '</strong></th>';
			echo '<td colspan="4" class="edd_download_stats">';
				_e('Each time a file is downloaded, it is recorded below.', 'edd');
			echo '</td>';
		echo '</tr>';
		if($download_log) {
			foreach($download_log['downloads'] as $file_download) {
				$user_id = isset($file_download['user_info']['id']) ? $file_download['user_info']['id'] : 0;
				$user_data = get_userdata($user_id);
				if( $user_data ) {
					$name = $user_data->display_name;
				} else {
					$name = $file_download['user_info']['email'];
				}
				$file_name = $files[$file_download['file_id']]['name'];
				
				echo '<tr>';
				
					echo '<td class="edd_download_sales_log">';
						echo '<strong>' . __('Date:', 'edd') . '</strong> ' . $file_download['date'];
					echo '</td>';
				
					echo '<td class="edd_download_sales_log">';
						echo '<strong>' . __('Downloaded by:', 'edd') . '</strong> ' . $name;
					echo '</td>';
					
					echo '<td class="edd_download_sales_log">';
						echo '<strong>' . __('IP Address:', 'edd') . '</strong> ' . $file_download['ip'];
					echo '</td>';
					
					echo '<td colspan="2" class="edd_download_sales_log">';
						echo '<strong>' . __('File: ', 'edd') . '</strong> ' . $file_name;
					echo '</td>';
					
				echo '</tr>';
				do_action('edd_download_log__meta_box');
			} // endforeach
		} else {
			echo '<tr>';
				echo '<td colspan=4" class="edd_download_sales_log">';
					echo __('No file downloads yet yet', 'edd');
				echo '</td>';
			echo '</tr>';		
		}
	echo '</table>';
	
	$total_log_entries = $download_log['number'];		
	$total_pages = ceil( $total_log_entries / $per_page );
	
	if ($total_pages > 1) :
		echo '<div class="tablenav">';
			echo '<div class="tablenav-pages alignright">';
				$base = 'post.php?post=' . $post->ID . '&action=edit%_%';		
				echo paginate_links( array(
					'base' => $base,
					'format' => '&edd_log_page=%#%',
					'prev_text' => '&laquo; ' . __('Previous', 'edd'),
					'next_text' => __('Next', 'edd') . ' &raquo;',
					'total' => $total_pages,
					'current' => $page,
					'end_size' => 1,
					'mid_size' => 5,
					'add_fragment' => '#edd_file_download_log'
				));
			echo '</div>';
		echo '</div><!--end .tablenav-->';
	endif;
}