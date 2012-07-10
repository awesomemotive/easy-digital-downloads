<?php
/**
 * Process Download
 *
 * @package     Easy Digital Downloads
 * @subpackage  Process Download
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/


/**
 * Process Download
 *
 * Handles the file download process.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_process_download() {
	if(isset($_GET['download']) && isset($_GET['email']) && isset($_GET['file'])) {
		$download = urldecode($_GET['download']);
		$key = urldecode($_GET['download_key']);
		$email = urldecode($_GET['email']);
		$file_key = urldecode($_GET['file']);
		$expire = urldecode(base64_decode($_GET['expire']));
				

		$payment = edd_verify_download_link($download, $key, $email, $expire, $file_key);
		
		 // defaulting this to true for now because the method below doesn't work well
		$has_access = true;
		//$has_access = ( edd_logged_in_only() && is_user_logged_in() ) || !edd_logged_in_only() ? true : false;
		if($payment && $has_access) {
			
			do_action('edd_process_verified_download', $download, $email);;

			// payment has been verified, setup the download
			$download_files = get_post_meta($download, 'edd_download_files', true);
			
			$requested_file = apply_filters('edd_requested_file', $download_files[$file_key]['file'] );
		
			$user_info = array();
			$user_info['email'] = $email;
			if(is_user_logged_in()) {
				global $user_ID;
				$user_data = get_userdata($user_ID);
				$user_info['id'] = $user_ID;
				$user_info['name'] = $user_data->display_name;
			}
			
			edd_record_download_in_log($download, $file_key, $user_info, edd_get_ip(), date('Y-m-d H:i:s'));
			
			$file_extension = edd_get_file_extension($requested_file);

            switch ($file_extension) :
                case "pdf": $ctype = "application/pdf"; break;
                case "exe": $ctype = "application/octet-stream"; break;
                case "zip": $ctype = "application/zip"; break;
                case "doc": $ctype = "application/msword"; break;
                case "xls": $ctype = "application/vnd.ms-excel"; break;
                case "ppt": $ctype = "application/vnd.ms-powerpoint"; break;
                case "gif": $ctype = "image/gif"; break;
                case "png": $ctype = "image/png"; break;
                case "jpe": $ctype="image/jpg"; break;
                case "jpeg": $ctype="image/jpg"; break;
                case "jpg": $ctype="image/jpg"; break;
                case 'mp3': $ctype="audio/mpeg"; break;
                case 'wav': $ctype="audio/x-wav"; break;
                case 'mpeg': $ctype="video/mpeg"; break;
                case 'mpg': $ctype="video/mpeg"; break;
                case 'mpe': $ctype="video/mpeg"; break;
                case 'mov': $ctype="video/quicktime"; break;
                case 'avi': $ctype="'video/x-msvideo"; break;
                default: $ctype = "application/force-download";
            endswitch;
			
			set_time_limit(0);
			set_magic_quotes_runtime(0);
				
			header("Pragma: no-cache");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Robots: none");
			header("Content-Type: " . $ctype . "");
			header("Content-Description: File Transfer");	
		    header("Content-Disposition: attachment; filename=\"" . apply_filters('edd_requested_file_name', basename($requested_file) ) . "\";");
			header("Content-Transfer-Encoding: binary");
			edd_read_file( $requested_file );			
			exit;
			
		} else {
			wp_die(__('You do not have permission to download this file', 'edd'), __('Purchase Verification Failed', 'edd'));
		}
		exit;
	}
}
add_action('init', 'edd_process_download', 100);