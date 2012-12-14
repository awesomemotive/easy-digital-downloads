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

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

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
	if( isset( $_GET['download'] ) && isset( $_GET['email'] ) && isset( $_GET['file'] ) ) {
		$download 	= urldecode( $_GET['download'] );
		$key 		= urldecode( $_GET['download_key'] );
		$email 		= rawurldecode( $_GET['email'] );
		$file_key 	= (int) urldecode( $_GET['file'] );
		$expire 	= urldecode( base64_decode( $_GET['expire'] ) );

		$payment = edd_verify_download_link( $download, $key, $email, $expire, $file_key );

		// Defaulting this to true for now because the method below doesn't work well
		$has_access = apply_filters( 'edd_file_download_has_access', true );

		//$has_access = ( edd_logged_in_only() && is_user_logged_in() ) || !edd_logged_in_only() ? true : false;
		if( $payment && $has_access ) {

			do_action( 'edd_process_verified_download', $download, $email );

			// payment has been verified, setup the download
			$download_files = edd_get_download_files( $download );

			$requested_file = apply_filters( 'edd_requested_file', $download_files[ $file_key ]['file'] );

			$user_info = array();
			$user_info['email'] = $email;
			if( is_user_logged_in() ) {
				global $user_ID;
				$user_data 			= get_userdata( $user_ID );
				$user_info['id'] 	= $user_ID;
				$user_info['name'] 	= $user_data->display_name;
			}

			edd_record_download_in_log( $download, $file_key, $user_info, edd_get_ip(), $payment );

			$file_extension = edd_get_file_extension( $requested_file );

			switch( $file_extension ):
				case 'ai'		: $ctype	= "application/postscript"; break;
				case 'aif'		: $ctype	= "audio/x-aiff"; break;
				case 'aifc'		: $ctype	= "audio/x-aiff"; break;
				case 'aiff'		: $ctype	= "audio/x-aiff"; break;
				case 'asc'		: $ctype	= "text/plain"; break;
				case 'atom'		: $ctype	= "application/atom+xml"; break;
				case 'au'		: $ctype	= "audio/basic"; break;
				case 'avi'		: $ctype	= "video/x-msvideo"; break;
				case 'bcpio'	: $ctype	= "application/x-bcpio"; break;
				case 'bin'		: $ctype	= "application/octet-stream"; break;
				case 'bmp'		: $ctype	= "image/bmp"; break;
				case 'cdf'		: $ctype	= "application/x-netcdf"; break;
				case 'cgm'		: $ctype	= "image/cgm"; break;
				case 'class'	: $ctype	= "application/octet-stream"; break;
				case 'cpio'		: $ctype	= "application/x-cpio"; break;
				case 'cpt'		: $ctype	= "application/mac-compactpro"; break;
				case 'csh'		: $ctype	= "application/x-csh"; break;
				case 'css'		: $ctype	= "text/css"; break;
				case 'dcr'		: $ctype	= "application/x-director"; break;
				case 'dif'		: $ctype	= "video/x-dv"; break;
				case 'dir'		: $ctype	= "application/x-director"; break;
				case 'djv'		: $ctype	= "image/vnd.djvu"; break;
				case 'djvu'		: $ctype	= "image/vnd.djvu"; break;
				case 'dll'		: $ctype	= "application/octet-stream"; break;
				case 'dmg'		: $ctype	= "application/octet-stream"; break;
				case 'dms'		: $ctype	= "application/octet-stream"; break;
				case 'doc'		: $ctype	= "application/msword"; break;
				case 'dtd'		: $ctype	= "application/xml-dtd"; break;
				case 'dv'		: $ctype	= "video/x-dv"; break;
				case 'dvi'		: $ctype	= "application/x-dvi"; break;
				case 'dxr'		: $ctype	= "application/x-director"; break;
				case 'eps'		: $ctype	= "application/postscript"; break;
				case 'etx'		: $ctype	= "text/x-setext"; break;
				case 'exe'		: $ctype	= "application/octet-stream"; break;
				case 'ez'		: $ctype	= "application/andrew-inset"; break;
				case 'gif'		: $ctype	= "image/gif"; break;
				case 'gram'		: $ctype	= "application/srgs"; break;
				case 'grxml'	: $ctype	= "application/srgs+xml"; break;
				case 'gtar'		: $ctype	= "application/x-gtar"; break;
				case 'hdf'		: $ctype	= "application/x-hdf"; break;
				case 'hqx'		: $ctype	= "application/mac-binhex40"; break;
				case 'htm'		: $ctype	= "text/html"; break;
				case 'html'		: $ctype	= "text/html"; break;
				case 'ice'		: $ctype	= "x-conference/x-cooltalk"; break;
				case 'ico'		: $ctype	= "image/x-icon"; break;
				case 'ics'		: $ctype	= "text/calendar"; break;
				case 'ief'		: $ctype	= "image/ief"; break;
				case 'ifb'		: $ctype	= "text/calendar"; break;
				case 'iges'		: $ctype	= "model/iges"; break;
				case 'igs'		: $ctype	= "model/iges"; break;
				case 'jnlp'		: $ctype	= "application/x-java-jnlp-file"; break;
				case 'jp2'		: $ctype	= "image/jp2"; break;
				case 'jpe'		: $ctype	= "image/jpeg"; break;
				case 'jpeg'		: $ctype	= "image/jpeg"; break;
				case 'jpg'		: $ctype	= "image/jpeg"; break;
				case 'js'		: $ctype	= "application/x-javascript"; break;
				case 'kar'		: $ctype	= "audio/midi"; break;
				case 'latex'	: $ctype	= "application/x-latex"; break;
				case 'lha'		: $ctype	= "application/octet-stream"; break;
				case 'lzh'		: $ctype	= "application/octet-stream"; break;
				case 'm3u'		: $ctype	= "audio/x-mpegurl"; break;
				case 'm4a'		: $ctype	= "audio/mp4a-latm"; break;
				case 'm4b'		: $ctype	= "audio/mp4a-latm"; break;
				case 'm4p'		: $ctype	= "audio/mp4a-latm"; break;
				case 'm4u'		: $ctype	= "video/vnd.mpegurl"; break;
				case 'm4v'		: $ctype	= "video/x-m4v"; break;
				case 'mac'		: $ctype	= "image/x-macpaint"; break;
				case 'man'		: $ctype	= "application/x-troff-man"; break;
				case 'mathml'	: $ctype	= "application/mathml+xml"; break;
				case 'me'		: $ctype	= "application/x-troff-me"; break;
				case 'mesh'		: $ctype	= "model/mesh"; break;
				case 'mid'		: $ctype	= "audio/midi"; break;
				case 'midi'		: $ctype	= "audio/midi"; break;
				case 'mif'		: $ctype	= "application/vnd.mif"; break;
				case 'mov'		: $ctype	= "video/quicktime"; break;
				case 'movie'	: $ctype	= "video/x-sgi-movie"; break;
				case 'mp2'		: $ctype	= "audio/mpeg"; break;
				case 'mp3'		: $ctype	= "audio/mpeg"; break;
				case 'mp4'		: $ctype	= "video/mp4"; break;
				case 'mpe'		: $ctype	= "video/mpeg"; break;
				case 'mpeg'		: $ctype	= "video/mpeg"; break;
				case 'mpg'		: $ctype	= "video/mpeg"; break;
				case 'mpga'		: $ctype	= "audio/mpeg"; break;
				case 'ms'		: $ctype	= "application/x-troff-ms"; break;
				case 'msh'		: $ctype	= "model/mesh"; break;
				case 'mxu'		: $ctype	= "video/vnd.mpegurl"; break;
				case 'nc'		: $ctype	= "application/x-netcdf"; break;
				case 'oda'		: $ctype	= "application/oda"; break;
				case 'ogg'		: $ctype	= "application/ogg"; break;
				case 'pbm'		: $ctype	= "image/x-portable-bitmap"; break;
				case 'pct'		: $ctype	= "image/pict"; break;
				case 'pdb'		: $ctype	= "chemical/x-pdb"; break;
				case 'pdf'		: $ctype	= "application/pdf"; break;
				case 'pgm'		: $ctype	= "image/x-portable-graymap"; break;
				case 'pgn'		: $ctype	= "application/x-chess-pgn"; break;
				case 'pic'		: $ctype	= "image/pict"; break;
				case 'pict'		: $ctype	= "image/pict"; break;
				case 'png'		: $ctype	= "image/png"; break;
				case 'pnm'		: $ctype	= "image/x-portable-anymap"; break;
				case 'pnt'		: $ctype	= "image/x-macpaint"; break;
				case 'pntg'		: $ctype	= "image/x-macpaint"; break;
				case 'ppm'		: $ctype	= "image/x-portable-pixmap"; break;
				case 'ppt'		: $ctype	= "application/vnd.ms-powerpoint"; break;
				case 'ps'		: $ctype	= "application/postscript"; break;
				case 'qt'		: $ctype	= "video/quicktime"; break;
				case 'qti'		: $ctype	= "image/x-quicktime"; break;
				case 'qtif'		: $ctype	= "image/x-quicktime"; break;
				case 'ra'		: $ctype	= "audio/x-pn-realaudio"; break;
				case 'ram'		: $ctype	= "audio/x-pn-realaudio"; break;
				case 'ras'		: $ctype	= "image/x-cmu-raster"; break;
				case 'rdf'		: $ctype	= "application/rdf+xml"; break;
				case 'rgb'		: $ctype	= "image/x-rgb"; break;
				case 'rm'		: $ctype	= "application/vnd.rn-realmedia"; break;
				case 'roff'		: $ctype	= "application/x-troff"; break;
				case 'rtf'		: $ctype	= "text/rtf"; break;
				case 'rtx'		: $ctype	= "text/richtext"; break;
				case 'sgm'		: $ctype	= "text/sgml"; break;
				case 'sgml'		: $ctype	= "text/sgml"; break;
				case 'sh'		: $ctype	= "application/x-sh"; break;
				case 'shar'		: $ctype	= "application/x-shar"; break;
				case 'silo'		: $ctype	= "model/mesh"; break;
				case 'sit'		: $ctype	= "application/x-stuffit"; break;
				case 'skd'		: $ctype	= "application/x-koan"; break;
				case 'skm'		: $ctype	= "application/x-koan"; break;
				case 'skp'		: $ctype	= "application/x-koan"; break;
				case 'skt'		: $ctype	= "application/x-koan"; break;
				case 'smi'		: $ctype	= "application/smil"; break;
				case 'smil'		: $ctype	= "application/smil"; break;
				case 'snd'		: $ctype	= "audio/basic"; break;
				case 'so'		: $ctype	= "application/octet-stream"; break;
				case 'spl'		: $ctype	= "application/x-futuresplash"; break;
				case 'src'		: $ctype	= "application/x-wais-source"; break;
				case 'sv4cpio'	: $ctype	= "application/x-sv4cpio"; break;
				case 'sv4crc'	: $ctype	= "application/x-sv4crc"; break;
				case 'svg'		: $ctype	= "image/svg+xml"; break;
				case 'swf'		: $ctype	= "application/x-shockwave-flash"; break;
				case 't'		: $ctype	= "application/x-troff"; break;
				case 'tar'		: $ctype	= "application/x-tar"; break;
				case 'tcl'		: $ctype	= "application/x-tcl"; break;
				case 'tex'		: $ctype	= "application/x-tex"; break;
				case 'texi'		: $ctype	= "application/x-texinfo"; break;
				case 'texinfo'	: $ctype	= "application/x-texinfo"; break;
				case 'tif'		: $ctype	= "image/tiff"; break;
				case 'tiff'		: $ctype	= "image/tiff"; break;
				case 'tr'		: $ctype	= "application/x-troff"; break;
				case 'tsv'		: $ctype	= "text/tab-separated-values"; break;
				case 'txt'		: $ctype	= "text/plain"; break;
				case 'ustar'	: $ctype	= "application/x-ustar"; break;
				case 'vcd'		: $ctype	= "application/x-cdlink"; break;
				case 'vrml'		: $ctype	= "model/vrml"; break;
				case 'vxml'		: $ctype	= "application/voicexml+xml"; break;
				case 'wav'		: $ctype	= "audio/x-wav"; break;
				case 'wbmp'		: $ctype	= "image/vnd.wap.wbmp"; break;
				case 'wbmxl'	: $ctype	= "application/vnd.wap.wbxml"; break;
				case 'wml'		: $ctype	= "text/vnd.wap.wml"; break;
				case 'wmlc'		: $ctype	= "application/vnd.wap.wmlc"; break;
				case 'wmls'		: $ctype	= "text/vnd.wap.wmlscript"; break;
				case 'wmlsc'	: $ctype	= "application/vnd.wap.wmlscriptc"; break;
				case 'wrl'		: $ctype	= "model/vrml"; break;
				case 'xbm'		: $ctype	= "image/x-xbitmap"; break;
				case 'xht'		: $ctype	= "application/xhtml+xml"; break;
				case 'xhtml'	: $ctype	= "application/xhtml+xml"; break;
				case 'xls'		: $ctype	= "application/vnd.ms-excel"; break;
				case 'xml'		: $ctype	= "application/xml"; break;
				case 'xpm'		: $ctype	= "image/x-xpixmap"; break;
				case 'xsl'		: $ctype	= "application/xml"; break;
				case 'xslt'		: $ctype	= "application/xslt+xml"; break;
				case 'xul'		: $ctype	= "application/vnd.mozilla.xul+xml"; break;
				case 'xwd'		: $ctype	= "image/x-xwindowdump"; break;
				case 'xyz'		: $ctype	= "chemical/x-xyz"; break;
				case 'zip'		: $ctype	= "application/zip"; break;
				default			: $ctype	= "application/force-download";
			endswitch;

			if( !edd_is_func_disabled( 'set_time_limit' ) && !ini_get('safe_mode') ) {
				set_time_limit(0);
			}
			if( function_exists( 'get_magic_quotes_runtime' ) && get_magic_quotes_runtime() ) {
				set_magic_quotes_runtime(0);
			}

			@session_write_close();
			if( function_exists( 'apache_setenv' ) ) @apache_setenv('no-gzip', 1);
			@ini_set( 'zlib.output_compression', 'Off' );
			@ob_end_clean();
			if( ob_get_level() ) @ob_end_clean(); // Zip corruption fix

			header("Pragma: no-cache");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Robots: none");
			header("Content-Type: " . $ctype . "");
			header("Content-Description: File Transfer");
			header("Content-Disposition: attachment; filename=\"" . apply_filters( 'edd_requested_file_name', basename( $requested_file ) ) . "\";");
			header("Content-Transfer-Encoding: binary");


			if( strpos( $requested_file, 'http://' ) === false && strpos( $requested_file, 'https://' ) === false && strpos( $requested_file, 'ftp://' ) === false ) {

				// this is an absolute path

				$requested_file = realpath( $requested_file );
				if( file_exists( $requested_file ) ) {
					if( $size = @filesize( $requested_file ) ) header("Content-Length: ".$size);
					@edd_readfile_chunked( $requested_file );
				} else {
					wp_die( __('Sorry but this file does not exist.', 'edd'), __('Error', 'edd') );
				}

			} else if( strpos( $requested_file, WP_CONTENT_URL ) !== false) {

				// This is a local file given by URL
				$upload_dir = wp_upload_dir();

				$requested_file = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $requested_file );
				$requested_file = realpath( $requested_file );

				if( file_exists( $requested_file ) ) {
					if( $size = @filesize( $requested_file ) ) header("Content-Length: ".$size);
					@edd_readfile_chunked( $requested_file );
				} else {
					wp_die( __('Sorry but this file does not exist.', 'edd'), __('Error', 'edd') );
				}

			} else {
				// This is a remote file
				header("Location: " . $requested_file);
			}

			exit;

		} else {
			wp_die(__('You do not have permission to download this file', 'edd'), __('Purchase Verification Failed', 'edd'));
		}
		exit;
	}
}
add_action( 'init', 'edd_process_download', 100 );


/**
 * readfile_chunked
 *
 * Reads file in chunks so big downloads are possible without changing PHP.INI - http://codeigniter.com/wiki/Download_helper_for_large_files/
 *
 * @access   public
 * @param    string    file
 * @param    boolean   return bytes of file
 * @return   void
 */

function edd_readfile_chunked( $file, $retbytes = TRUE ) {

	$chunksize = 1 * (1024 * 1024);
	$buffer = '';
	$cnt = 0;

	$handle = fopen( $file, 'r' );
	if( $handle === FALSE ) return FALSE;

	while( !feof($handle) ) :
	   $buffer = fread( $handle, $chunksize );
	   echo $buffer;
	   ob_flush();
	   flush();

	   if ( $retbytes ) $cnt += strlen( $buffer );
	endwhile;

	$status = fclose( $handle );

	if( $retbytes AND $status ) return $cnt;

	return $status;
}