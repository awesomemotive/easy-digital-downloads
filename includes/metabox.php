<?php

$edd_download_meta_box = array( 
	'id' => 'downloadinformation',
	'title' => 'Download Configuration',
	'page' => array('download'),
	'context' => 'normal',
	'priority' => 'default',
	'fields' => array(				
		array(
			'name' 	=> __('Price', 'edd'),
			'desc' 	=> __('Enter the download price', 'edd'),
			'id' 	=> 'edd_price',
			'class' => 'edd_price',
			'type' 	=> 'price',
			'std' 	=> '' 											
		),							
		array(
			'name' 	=> __('Download Files', 'edd'),
			'desc' 	=> __('Upload the downloadable files.', 'edd'),
			'id' 	=> 'edd_download_files',
			'class' => 'edd_download_files',
			'type' 	=> 'repeatable upload',
			'std'	=> ''
		),						
		array(
			'name' 	=> __('Show Purchase Link on Details Page', 'edd'),
			'desc' 	=> __('Check this to automatically append the purchase link to download detail pages. If unchecked, you must enter the short code manually.', 'edd'),
			'id' 	=> '_edd_show_purchase_link',
			'class' => 'edd_show_purchase_link',
			'type' 	=> 'checkbox',
			'std'	=> ''
		),							
		array(
			'name' 	=> __('Show Purchase Link on Archive Pages', 'edd'),
			'desc' 	=> __('Check this to automatically append the purchase link to download excerpts.', 'edd'),
			'id' 	=> '_edd_show_archive_purchase_link',
			'class' => 'edd_show_archive_purchase_link',
			'type' 	=> 'checkbox',
			'std'	=> ''
		),							
		array(
			'name' 	=> __('Purchase Text', 'edd'),
			'desc' 	=> __('Add the text you would like displayed for the purchase text', 'edd'),
			'id' 	=> '_edd_purchase_text',
			'class' => 'edd_text',
			'type' 	=> 'text',
			'std'	=> __('Purchase', 'edd')
		),							
		array(
			'name' 	=> __('Link Style', 'edd'),
			'desc' 	=> __('Choose the style of the purchase link', 'edd'),
			'id' 	=> '_edd_purchase_style',
			'class' => 'edd_radio',
			'type' 	=> 'radio',
			'options' => array('button', 'text')
		),							
		array(
			'name' 	=> __('Button Color', 'edd'),
			'desc' 	=> __('Choose the style of the purchase link', 'edd'),
			'id' 	=> '_edd_purchase_color',
			'class' => 'edd_select',
			'type' 	=> 'select',
			'options' => edd_get_button_colors()
		)		
	)
);			

function edd_add_download_meta_box() {

	global $edd_download_meta_box;		

	add_meta_box($edd_download_meta_box['id'], $edd_download_meta_box['title'], 'edd_render_download_meta_box', 'download', 'normal', 'default', $edd_download_meta_box);
	add_meta_box('edd_download_stats', __('Download Stats', 'edd'), 'edd_render_stats_meta_box', 'download', 'side', 'high');
	add_meta_box('edd_payment_info', __('Payment Info', 'edd'), 'edd_render_payment_meta_box', 'edd_payment', 'normal', 'high');
}
add_action('add_meta_boxes', 'edd_add_download_meta_box');

// render the download information meta box
function edd_render_download_meta_box()	{
	global $post;
	global $edd_download_meta_box;
	global $wp_version;
	
	// Use nonce for verification
	echo '<input type="hidden" name="edd_download_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';
	
	echo '<table class="form-table">';

	foreach ($edd_download_meta_box['fields'] as $field) {
		// get current post meta data

		$meta = get_post_meta($post->ID, $field['id'], true);
		
		echo '<tr>';
			echo '<th style="width:20%"><label for="', $field['id'], '">', $field['name'], '</label></th>';
			echo '<td class="edd_field_type_' . str_replace(' ', '_', $field['type']) . '">';
				switch ($field['type']) {
					case 'text':
						echo '<input class="regular-text" type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" /><br/>', '', $field['desc'];
						break;
					case 'price':
						echo '<input type="text" name="', $field['id'], '" id="', $field['id'], '" value="', $meta ? $meta : $field['std'], '" size="30" style="width:50px;" placeholder="9.99"/>', ' ', $field['desc'];
						break;
					case 'textarea':
						if($field['rich_editor'] == 1) {
							echo wp_editor($meta, $field['id'], array('textarea_name' => $field['id']));
						} else {
							echo '<div style="width: 100%;"><textarea name="', $field['id'], '" class="', $field['class'], '" id="', $field['id'], '" cols="60" rows="8" style="width:97%">', $meta ? $meta : $field['std'], '</textarea></div>', '', $field['desc'];				
						}				
						break;
					case 'radio':
						foreach ($field['options'] as $option) {
							echo '<input type="radio" name="', $field['id'], '" value="', $option, '"', $meta == $option ? ' checked="checked"' : '', ' />&nbsp;', $option;
						}
						echo '<br/>' . $field['desc'];
						break;
					case 'select':
						echo '<select name="' . $field['id'] . '" id="' . $field['id'] . '">';
							foreach ($field['options'] as $option) {
								echo '<option value="' . $option . '" ' . selected($meta, $option, false) . '>' . $option . '</option>';
							}
						echo '</select>';
						echo '<br/>' . $field['desc'];
						break;
					case 'checkbox':
						echo '<input type="checkbox" name="', $field['id'], '" id="', $field['id'], '"', $meta ? ' checked="checked"' : '', ' />&nbsp;';
						echo $field['desc'];
						break;			
					case 'repeatable upload' :			
						$field_html = '<input type="hidden" id="' . $field['id'] . '" class="edd_repeatable_upload_name_field" value=""/>';
						$field_html .= '<input type="hidden" id="' . $field['id'] . '" class="edd_repeatable_upload_file_field" value=""/>';
						if(is_array($meta)) {
							$count = 1;
							foreach($meta as $key => $value) {
								$field_html .= '<div class="edd_repeatable_upload_wrapper">';
									$name = isset($meta[$key]['name']) ? $meta[$key]['name'] : '';
									$file = isset($meta[$key]['file']) ? $meta[$key]['file'] : '';
									$field_html .= '<input type="text" class="edd_repeatable_name_field" placeholder="' . __('file name', 'edd') . '" name="' . $field['id'] . '[' . $key . '][name]" id="' . $field['id'] . '[' . $key . '][name]" value="' . $name . '" size="20" style="width:20%" />';
									$field_html .= '<input type="text" class="edd_repeatable_upload_field edd_upload_field" placeholder="' . __('file url', 'edd') . '" name="' . $field['id'] . '[' . $key . '][file]" id="' . $field['id'] . '[' . $key . '][file]" value="' . $file . '" size="30" style="width:50%" />';
									$field_html .= '<button class="button-secondary edd_upload_image_button">Upload File</button>';
								if($count > 1) {
									$field_html .= '<a href="#" class="edd_remove_repeatable button-secondary">x</a><br/>';
								}
								$field_html .= '</div>';
								$count++;
							}
						} else {
							$field_html .= '<div class="edd_repeatable_upload_wrapper">';
								$field_html .= '<input type="text" class="edd_repeatable_name_field" placeholder="' . __('file name', 'edd') . '" name="' . $field['id'] . '[0][name]" id="' . $field['id'] . '[0][name]" value="" size="20" style="width:20%" />';
								$field_html .= '<input type="text" class="edd_repeatable_upload_field edd_upload_field" placeholder="' . __('file url', 'edd') . '" name="' . $field['id'] . '[0][file]" id="' . $field['id'] . '[0][file]" value="" size="30" style="width:50%" />';
								$field_html .= '<button class="button-secondary edd_upload_image_button">Upload File</button>';
							$field_html .= '</div>';
						}
						$field_html .= '<button class="edd_add_new_upload_field button-secondary">' . __('Add New', 'ecpt') . '</button>&nbsp;&nbsp;' . __(stripslashes($field['desc']));		
			
						echo $field_html;
			
						break;
				} // end switch
			echo '<td>';
		echo '</tr>';
	}
	echo '<tr>';
		echo '<th style="width:20%"><label>' . __('Notes', 'edd') . '</label></th>';
		echo '<td>' . __('The style options above do NOT reflect the style of short code. The short code allows you to place a purchase button for this download anywhere on the site.', 'edd') . '</td>';
	echo '</tr>';
	echo '<tr>';
		echo '<th style="width:20%"><label>' . __('Short Code for this Download', 'edd') . '</label></th>';
		echo '<td><em>[purchase_link id="' . $post->ID . '" text="' . __('Purchase', 'edd') . '" style="button" color="gray"]</td>';
	echo '</tr>';
	echo '</table>';
}


// Save data from meta box
function edd_download_meta_box_save($post_id) {
	global $post;
	global $edd_download_meta_box;
	
	// verify nonce
	if (isset($_POST['edd_download_meta_box_nonce']) && !wp_verify_nonce($_POST['edd_download_meta_box_nonce'], basename(__FILE__))) {
		return $post_id;
	}

	// check autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post_id;
	}

	// check permissions
	if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
		if (!current_user_can('edit_page', $post_id)) {
			return $post_id;
		}
	} elseif (!current_user_can('edit_post', $post_id)) {
		return $post_id;
	}
	
	if(isset($edd_download_meta_box)) {
		foreach ($edd_download_meta_box['fields'] as $field) {
	
			$old = get_post_meta($post_id, $field['id'], true);
			$new = isset($_POST[$field['id']]) ? $_POST[$field['id']] : '';
		
			if ($new && $new != $old) {
				if(is_string($new)) {
					$new = esc_attr($new);
				} 
				update_post_meta($post_id, $field['id'], $new);				
			} elseif ('' == $new && $old) {
				delete_post_meta($post_id, $field['id'], $old);
			}
		}
	}
}
add_action('save_post', 'edd_download_meta_box_save');

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
	echo '</table>';
}