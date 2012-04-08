<?php

function edd_process_download() {
	if(isset($_GET['download']) && isset($_GET['email']) && isset($_GET['file'])) {
		$download = urldecode($_GET['download']);
		$key = urldecode($_GET['download_key']);
		$email = urldecode($_GET['email']);
		$file_key = urldecode($_GET['file']);
		$expire = urldecode(base64_decode($_GET['expire']));
				
		$payment = edd_verify_download_link($download, $key, $email, $expire);
		$has_access = ( edd_logged_in_only() && is_user_logged_in() ) || !edd_logged_in_only() ? true : false;
		if($payment && $has_access) {
			
			// payment has been verified, setup the download
			$download_files = get_post_meta($download, 'edd_download_files', true);
			
			$requested_file = $download_files[$file_key]['file'];
			
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
                case "jpe": case "jpeg": case "jpg": $ctype="image/jpg"; break;
                default: $ctype = "application/force-download";
            endswitch;
			
			@ini_set('zlib.output_compression', 'Off');
			@set_time_limit(0);
			@session_start();					
			@session_cache_limiter('none');		
			@set_magic_quotes_runtime(0);
			@ob_end_clean();
			@session_write_close();
			
			
			header("Pragma: no-cache");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Robots: none");
			header("Content-Type: " . $ctype . "");
			header("Content-Description: File Transfer");	
		    header("Content-Disposition: attachment; filename=\"" . $requested_file . "\";");
			header("Content-Transfer-Encoding: binary");
			header('Location: ' . $requested_file);
			exit;
			
		} else {
			wp_die(__('You do not have permission to download this file', 'edd'), __('Purchase Verification Failed', 'edd'));
		}
		exit;
	}
}
add_action('init', 'edd_process_download', 100);
