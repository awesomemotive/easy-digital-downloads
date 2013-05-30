<?php
/**
 * Process Download
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
  */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

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
	$args = apply_filters( 'edd_process_download_args', array(
		'download' => ( isset( $_GET['download'] ) )     ? (int) $_GET['download']                          : '',
		'email'    => ( isset( $_GET['email'] ) )        ? rawurldecode( $_GET['email'] )                   : '',
		'expire'   => ( isset( $_GET['expire'] ) )       ? base64_decode( rawurldecode( $_GET['expire'] ) ) : '',
		'file_key' => ( isset( $_GET['file'] ) )         ? (int) $_GET['file']                              : '',
		'price_id' => ( isset( $_GET['price_id'] ) )     ? (int) $_GET['price_id']                          : false,
		'key'      => ( isset( $_GET['download_key'] ) ) ? $_GET['download_key']                            : ''
	) );

	if( $args['download'] === '' || $args['email'] === '' || $args['file_key'] === '' )
		return false;

    extract( $args );

	$payment = edd_verify_download_link( $download, $key, $email, $expire, $file_key );

	// Defaulting this to true for now because the method below doesn't work well
	$has_access = apply_filters( 'edd_file_download_has_access', true, $payment, $args );

	//$has_access = ( edd_logged_in_only() && is_user_logged_in() ) || !edd_logged_in_only() ? true : false;
	if ( $payment && $has_access ) {
		do_action( 'edd_process_verified_download', $download, $email );

		// Payment has been verified, setup the download
		$download_files = edd_get_download_files( $download );

		$requested_file = apply_filters( 'edd_requested_file', $download_files[ $file_key ]['file'], $download_files, $file_key );

		$user_info = array();
		$user_info['email'] = $email;
		if ( is_user_logged_in() ) {
			global $user_ID;
			$user_data 			= get_userdata( $user_ID );
			$user_info['id'] 	= $user_ID;
			$user_info['name'] 	= $user_data->display_name;
		}

		edd_record_download_in_log( $download, $file_key, $user_info, edd_get_ip(), $payment );

		$file_extension = edd_get_file_extension( $requested_file );
		$ctype          = edd_get_file_ctype( $file_extension );

		if ( !edd_is_func_disabled( 'set_time_limit' ) && !ini_get('safe_mode') ) {
			set_time_limit(0);
		}
		if ( function_exists( 'get_magic_quotes_runtime' ) && get_magic_quotes_runtime() ) {
			set_magic_quotes_runtime(0);
		}

		@session_write_close();
		if( function_exists( 'apache_setenv' ) ) @apache_setenv('no-gzip', 1);
		@ini_set( 'zlib.output_compression', 'Off' );

		nocache_headers();
		header("Robots: none");
		header("Content-Type: " . $ctype . "");
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename=\"" . apply_filters( 'edd_requested_file_name', basename( $requested_file ) ) . "\";");
		header("Content-Transfer-Encoding: binary");

		$method = edd_get_file_download_method();
		if( 'x_sendfile' == $method && ( ! function_exists( 'apache_get_modules' ) || ! in_array( 'mod_xsendfile', apache_get_modules() ) ) ) {
			// If X-Sendfile is selected but is not supported, fallback to Direct
			$method = 'direct';
		}

		switch( $method ) :

			case 'redirect' :

				// Redirect straight to the file
				header( "Location: " . $requested_file );
				break;

			case 'direct' :
			default:

				$direct    = false;
				$file_path = realpath( $requested_file );

				if ( strpos( $requested_file, 'http://' ) === false && strpos( $requested_file, 'https://' ) === false && strpos( $requested_file, 'ftp://' ) === false && file_exists( $file_path ) ) {

					/** This is an absolute path */
					$direct = true;

				} else if( strpos( $requested_file, WP_CONTENT_URL ) !== false ) {

					/** This is a local file given by URL so we need to figure out the path */
					$upload_dir = wp_upload_dir();
					$file_path  = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $requested_file );
					$file_path  = realpath( $file_path );
					$direct     = true;

				}

				// Now deliver the file based on the kind of software the server is running / has enabled
				if ( function_exists( 'apache_get_modules' ) && in_array( 'mod_xsendfile', apache_get_modules() ) ) {

					header("X-Sendfile: $file_path");

				} elseif ( stristr( getenv( 'SERVER_SOFTWARE' ), 'lighttpd' ) ) {

					header( "X-Lighttpd-Sendfile: $file_path" );

				} elseif ( stristr( getenv( 'SERVER_SOFTWARE' ), 'nginx' ) || stristr( getenv( 'SERVER_SOFTWARE' ), 'cherokee' ) ) {

					header( "X-Accel-Redirect: /$file_path" );

				} elseif( $direct ) {
					edd_deliver_download( $file_path );
				} else {
					// The file supplied does not have a discoverable absolute path
					header( "Location: " . $requested_file );
				}

				break;

		endswitch;

		edd_die();
	} else {
		$error_message = __( 'You do not have permission to download this file', 'edd' );
		wp_die( apply_filters( ' edd_deny_download_message', $error_message, __( 'Purchase Verification Failed', 'edd' ) ) );
	}

	exit;
}
add_action( 'init', 'edd_process_download', 100 );


/**
 * Deliver the download file
 *
 * If enabled, the file is symlinked to better support large file downloads
 *
 * @access   public
 * @param    string    file
 * @return   void
 */
function edd_deliver_download( $file = '' ) {

	global $edd_options;

	$symlink = apply_filters( 'edd_symlink_file_downloads', isset( $edd_options['symlink_file_downloads'] ) );

	/*
	 * If symlinks are enabled, a link to the file will be created
	 * This symlink is used to hide the true location of the file, even when the file URL is revealed
	 * The symlink is deleted after it is used
	 */

	if( $symlink && function_exists( 'symlink' ) ) {

		// Generate a symbolic link
		$ext       = edd_get_file_extension( $file );
		$parts     = explode( '.', $file );
		$name      = basename( $parts[0] );
		$md5       = md5( $file );
		$file_name = $name . '_' . substr( $md5, 0, -15 ) . '.' . $ext;
		$path      = edd_get_symlink_dir() . '/' . $file_name;
		$url       = edd_get_symlink_url() . '/' . $file_name;

		// Set a transient to ensure this symlink is not deleted before it can be used
		set_transient( md5( $file_name ), '1', 30 );

		// Schedule deletion of the symlink
		if ( ! wp_next_scheduled( 'edd_cleanup_file_symlinks' ) )
			wp_schedule_single_event( time()+60, 'edd_cleanup_file_symlinks' );

		// Make sure the symlink doesn't already exist before we create it
		if( ! file_exists( $path ) )
			$link = symlink( $file, $path );
		else
			$link = true;

		if( $link ) {
			// Send the browser to the file
			header( 'Location: ' . $url );
		} else {
			@edd_readfile_chunked( $file );
		}

	} else {

		// Read the file and deliver it in chunks
		@edd_readfile_chunked( $file );

	}

}


/**
 * Get the file content type
 *
 * @access   public
 * @param    string    file extension
 * @return   string
 */
function edd_get_file_ctype( $extension ) {
	switch( $extension ):
		case 'ac'		: $ctype	= "application/pkix-attr-cert"; break;
		case 'adp'		: $ctype	= "audio/adpcm"; break;
		case 'ai'		: $ctype	= "application/postscript"; break;
		case 'aif'		: $ctype	= "audio/x-aiff"; break;
		case 'aifc'		: $ctype	= "audio/x-aiff"; break;
		case 'aiff'		: $ctype	= "audio/x-aiff"; break;
		case 'air'		: $ctype	= "application/vnd.adobe.air-application-installer-package+zip"; break;
		case 'apk'		: $ctype	= "application/vnd.android.package-archive"; break;
		case 'asc'		: $ctype	= "application/pgp-signature"; break;
		case 'atom'		: $ctype	= "application/atom+xml"; break;
		case 'atomcat'	: $ctype	= "application/atomcat+xml"; break;
		case 'atomsvc'	: $ctype	= "application/atomsvc+xml"; break;
		case 'au'		: $ctype	= "audio/basic"; break;
		case 'aw'		: $ctype	= "application/applixware"; break;
		case 'avi'		: $ctype	= "video/x-msvideo"; break;
		case 'bcpio'	: $ctype	= "application/x-bcpio"; break;
		case 'bin'		: $ctype	= "application/octet-stream"; break;
		case 'bmp'		: $ctype	= "image/bmp"; break;
		case 'boz'		: $ctype	= "application/x-bzip2"; break;
		case 'bpk'		: $ctype	= "application/octet-stream"; break;
		case 'bz'		: $ctype	= "application/x-bzip"; break;
		case 'bz2'		: $ctype	= "application/x-bzip2"; break;
		case 'ccxml'	: $ctype	= "application/ccxml+xml"; break;
		case 'cdmia'	: $ctype	= "application/cdmi-capability"; break;
		case 'cdmic'	: $ctype	= "application/cdmi-container"; break;
		case 'cdmid'	: $ctype	= "application/cdmi-domain"; break;
		case 'cdmio'	: $ctype	= "application/cdmi-object"; break;
		case 'cdmiq'	: $ctype	= "application/cdmi-queue"; break;
		case 'cdf'		: $ctype	= "application/x-netcdf"; break;
		case 'cer'		: $ctype	= "application/pkix-cert"; break;
		case 'cgm'		: $ctype	= "image/cgm"; break;
		case 'class'	: $ctype	= "application/octet-stream"; break;
		case 'cpio'		: $ctype	= "application/x-cpio"; break;
		case 'cpt'		: $ctype	= "application/mac-compactpro"; break;
		case 'crl'		: $ctype	= "application/pkix-crl"; break;
		case 'csh'		: $ctype	= "application/x-csh"; break;
		case 'css'		: $ctype	= "text/css"; break;
		case 'cu'		: $ctype	= "application/cu-seeme"; break;
		case 'davmount'	: $ctype	= "application/davmount+xml"; break;
		case 'dbk'		: $ctype	= "application/docbook+xml"; break;
		case 'dcr'		: $ctype	= "application/x-director"; break;
		case 'deploy'	: $ctype	= "application/octet-stream"; break;
		case 'dif'		: $ctype	= "video/x-dv"; break;
		case 'dir'		: $ctype	= "application/x-director"; break;
		case 'dist'		: $ctype	= "application/octet-stream"; break;
		case 'distz'	: $ctype	= "application/octet-stream"; break;
		case 'djv'		: $ctype	= "image/vnd.djvu"; break;
		case 'djvu'		: $ctype	= "image/vnd.djvu"; break;
		case 'dll'		: $ctype	= "application/octet-stream"; break;
		case 'dmg'		: $ctype	= "application/octet-stream"; break;
		case 'dms'		: $ctype	= "application/octet-stream"; break;
		case 'doc'		: $ctype	= "application/msword"; break;
		case 'docx'		: $ctype	= "application/vnd.openxmlformats-officedocument.wordprocessingml.document"; break;
		case 'dotx'		: $ctype	= "application/vnd.openxmlformats-officedocument.wordprocessingml.template"; break;
		case 'dssc'		: $ctype	= "application/dssc+der"; break;
		case 'dtd'		: $ctype	= "application/xml-dtd"; break;
		case 'dump'		: $ctype	= "application/octet-stream"; break;
		case 'dv'		: $ctype	= "video/x-dv"; break;
		case 'dvi'		: $ctype	= "application/x-dvi"; break;
		case 'dxr'		: $ctype	= "application/x-director"; break;
		case 'ecma'		: $ctype	= "application/ecmascript"; break;
		case 'elc'		: $ctype	= "application/octet-stream"; break;
		case 'emma'		: $ctype	= "application/emma+xml"; break;
		case 'eps'		: $ctype	= "application/postscript"; break;
		case 'epub'		: $ctype	= "application/epub+zip"; break;
		case 'etx'		: $ctype	= "text/x-setext"; break;
		case 'exe'		: $ctype	= "application/octet-stream"; break;
		case 'exi'		: $ctype	= "application/exi"; break;
		case 'ez'		: $ctype	= "application/andrew-inset"; break;
		case 'f4v'		: $ctype	= "video/x-f4v"; break;
		case 'fli'		: $ctype	= "video/x-fli"; break;
		case 'flv'		: $ctype	= "video/x-flv"; break;
		case 'gif'		: $ctype	= "image/gif"; break;
		case 'gml'		: $ctype	= "application/srgs"; break;
		case 'gpx'		: $ctype	= "application/gml+xml"; break;
		case 'gram'		: $ctype	= "application/gpx+xml"; break;
		case 'grxml'	: $ctype	= "application/srgs+xml"; break;
		case 'gtar'		: $ctype	= "application/x-gtar"; break;
		case 'gxf'		: $ctype	= "application/gxf"; break;
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
		case 'ink'		: $ctype	= "application/inkml+xml"; break;
		case 'inkml'	: $ctype	= "application/inkml+xml"; break;
		case 'ipfix'	: $ctype	= "application/ipfix"; break;
		case 'jar'		: $ctype	= "application/java-archive"; break;
		case 'jnlp'		: $ctype	= "application/x-java-jnlp-file"; break;
		case 'jp2'		: $ctype	= "image/jp2"; break;
		case 'jpe'		: $ctype	= "image/jpeg"; break;
		case 'jpeg'		: $ctype	= "image/jpeg"; break;
		case 'jpg'		: $ctype	= "image/jpeg"; break;
		case 'js'		: $ctype	= "application/javascript"; break;
		case 'json'		: $ctype	= "application/json"; break;
		case 'jsonml'	: $ctype	= "application/jsonml+json"; break;
		case 'kar'		: $ctype	= "audio/midi"; break;
		case 'latex'	: $ctype	= "application/x-latex"; break;
		case 'lha'    	: $ctype  = "application/octet-stream"; break;
		case 'lrf'    	: $ctype  = "application/octet-stream"; break;
		case 'lzh'    	: $ctype  = "application/octet-stream"; break;
		case 'lostxml'	: $ctype	= "application/lost+xml"; break;
		case 'm3u'		: $ctype	= "audio/x-mpegurl"; break;
		case 'm4a'		: $ctype	= "audio/mp4a-latm"; break;
		case 'm4b'		: $ctype	= "audio/mp4a-latm"; break;
		case 'm4p'		: $ctype	= "audio/mp4a-latm"; break;
		case 'm4u'		: $ctype	= "video/vnd.mpegurl"; break;
		case 'm4v'		: $ctype	= "video/x-m4v"; break;
		case 'm21'		: $ctype	= "application/mp21"; break;
		case 'ma'		: $ctype	= "application/mathematica"; break;
		case 'mac'		: $ctype	= "image/x-macpaint"; break;
		case 'mads'		: $ctype	= "application/mads+xml"; break;
		case 'man'		: $ctype	= "application/x-troff-man"; break;
		case 'mar'		: $ctype	= "application/octet-stream"; break;
		case 'mathml'	: $ctype	= "application/mathml+xml"; break;
		case 'mbox'		: $ctype	= "application/mbox"; break;
		case 'me'		: $ctype	= "application/x-troff-me"; break;
		case 'mesh'		: $ctype	= "model/mesh"; break;
		case 'metalink'	: $ctype	= "application/metalink+xml"; break;
		case 'meta4'	: $ctype	= "application/metalink4+xml"; break;
		case 'mets'		: $ctype	= "application/mets+xml"; break;
		case 'mid'		: $ctype	= "audio/midi"; break;
		case 'midi'		: $ctype	= "audio/midi"; break;
		case 'mif'		: $ctype	= "application/vnd.mif"; break;
		case 'mods'		: $ctype	= "application/mods+xml"; break;
		case 'mov'		: $ctype	= "video/quicktime"; break;
		case 'movie'	: $ctype	= "video/x-sgi-movie"; break;
		case 'm1v'		: $ctype	= "video/mpeg"; break;
		case 'm2v'		: $ctype	= "video/mpeg"; break;
		case 'mp2'		: $ctype	= "audio/mpeg"; break;
		case 'mp2a'		: $ctype	= "audio/mpeg"; break;
		case 'mp21'		: $ctype	= "application/mp21"; break;
		case 'mp3'		: $ctype	= "audio/mpeg"; break;
		case 'mp3a'		: $ctype	= "audio/mpeg"; break;
		case 'mp4'		: $ctype	= "video/mp4"; break;
		case 'mp4s'		: $ctype	= "application/mp4"; break;
		case 'mpe'		: $ctype	= "video/mpeg"; break;
		case 'mpeg'		: $ctype	= "video/mpeg"; break;
		case 'mpg'		: $ctype	= "video/mpeg"; break;
		case 'mpg4'		: $ctype	= "video/mpeg"; break;
		case 'mpga'		: $ctype	= "audio/mpeg"; break;
		case 'mrc'		: $ctype	= "application/marc"; break;
		case 'mrcx'		: $ctype	= "application/marcxml+xml"; break;
		case 'ms'		: $ctype	= "application/x-troff-ms"; break;
		case 'mscml'	: $ctype	= "application/mediaservercontrol+xml"; break;
		case 'msh'		: $ctype	= "model/mesh"; break;
		case 'mxf'		: $ctype	= "application/mxf"; break;
		case 'mxu'		: $ctype	= "video/vnd.mpegurl"; break;
		case 'nc'		: $ctype	= "application/x-netcdf"; break;
		case 'oda'		: $ctype	= "application/oda"; break;
		case 'oga'		: $ctype	= "application/ogg"; break;
		case 'ogg'		: $ctype	= "application/ogg"; break;
		case 'ogx'		: $ctype	= "application/ogg"; break;
		case 'omdoc'	: $ctype	= "application/omdoc+xml"; break;
		case 'onetoc'	: $ctype	= "application/onenote"; break;
		case 'onetoc2'	: $ctype	= "application/onenote"; break;
		case 'onetmp'	: $ctype	= "application/onenote"; break;
		case 'onepkg'	: $ctype	= "application/onenote"; break;
		case 'opf'		: $ctype	= "application/oebps-package+xml"; break;
		case 'oxps'		: $ctype	= "application/oxps"; break;
		case 'p7c'		: $ctype	= "application/pkcs7-mime"; break;
		case 'p7m'		: $ctype	= "application/pkcs7-mime"; break;
		case 'p7s'		: $ctype	= "application/pkcs7-signature"; break;
		case 'p8'		: $ctype	= "application/pkcs8"; break;
		case 'p10'		: $ctype	= "application/pkcs10"; break;
		case 'pbm'		: $ctype	= "image/x-portable-bitmap"; break;
		case 'pct'		: $ctype	= "image/pict"; break;
		case 'pdb'		: $ctype	= "chemical/x-pdb"; break;
		case 'pdf'		: $ctype	= "application/pdf"; break;
		case 'pki'		: $ctype	= "application/pkixcmp"; break;
		case 'pkipath'	: $ctype	= "application/pkix-pkipath"; break;
		case 'pfr'		: $ctype	= "application/font-tdpfr"; break;
		case 'pgm'		: $ctype	= "image/x-portable-graymap"; break;
		case 'pgn'		: $ctype	= "application/x-chess-pgn"; break;
		case 'pgp'		: $ctype	= "application/pgp-encrypted"; break;
		case 'pic'		: $ctype	= "image/pict"; break;
		case 'pict'		: $ctype	= "image/pict"; break;
		case 'pkg'		: $ctype	= "application/octet-stream"; break;
		case 'png'		: $ctype	= "image/png"; break;
		case 'pnm'		: $ctype	= "image/x-portable-anymap"; break;
		case 'pnt'		: $ctype	= "image/x-macpaint"; break;
		case 'pntg'		: $ctype	= "image/x-macpaint"; break;
		case 'pot'		: $ctype	= "application/vnd.ms-powerpoint"; break;
		case 'potx'		: $ctype	= "application/vnd.openxmlformats-officedocument.presentationml.template"; break;
		case 'ppm'		: $ctype	= "image/x-portable-pixmap"; break;
		case 'pps'		: $ctype	= "application/vnd.ms-powerpoint"; break;
		case 'ppsx'		: $ctype	= "application/vnd.openxmlformats-officedocument.presentationml.slideshow"; break;
		case 'ppt'		: $ctype	= "application/vnd.ms-powerpoint"; break;
		case 'pptx'		: $ctype	= "application/vnd.openxmlformats-officedocument.presentationml.presentation"; break;
		case 'prf'		: $ctype	= "application/pics-rules"; break;
		case 'ps'		: $ctype	= "application/postscript"; break;
		case 'psd'		: $ctype	= "image/photoshop"; break;
		case 'qt'		: $ctype	= "video/quicktime"; break;
		case 'qti'		: $ctype	= "image/x-quicktime"; break;
		case 'qtif'		: $ctype	= "image/x-quicktime"; break;
		case 'ra'		: $ctype	= "audio/x-pn-realaudio"; break;
		case 'ram'		: $ctype	= "audio/x-pn-realaudio"; break;
		case 'ras'		: $ctype	= "image/x-cmu-raster"; break;
		case 'rdf'		: $ctype	= "application/rdf+xml"; break;
		case 'rgb'		: $ctype	= "image/x-rgb"; break;
		case 'rm'		: $ctype	= "application/vnd.rn-realmedia"; break;
		case 'rmi'		: $ctype	= "audio/midi"; break;
		case 'roff'		: $ctype	= "application/x-troff"; break;
		case 'rss'		: $ctype	= "application/rss+xml"; break;
		case 'rtf'		: $ctype	= "text/rtf"; break;
		case 'rtx'		: $ctype	= "text/richtext"; break;
		case 'sgm'		: $ctype	= "text/sgml"; break;
		case 'sgml'		: $ctype	= "text/sgml"; break;
		case 'sh'		: $ctype	= "application/x-sh"; break;
		case 'shar'		: $ctype	= "application/x-shar"; break;
		case 'sig'		: $ctype	= "application/pgp-signature"; break;
		case 'silo'		: $ctype	= "model/mesh"; break;
		case 'sit'		: $ctype	= "application/x-stuffit"; break;
		case 'skd'		: $ctype	= "application/x-koan"; break;
		case 'skm'		: $ctype	= "application/x-koan"; break;
		case 'skp'		: $ctype	= "application/x-koan"; break;
		case 'skt'		: $ctype	= "application/x-koan"; break;
		case 'sldx'		: $ctype	= "application/vnd.openxmlformats-officedocument.presentationml.slide"; break;
		case 'smi'		: $ctype	= "application/smil"; break;
		case 'smil'		: $ctype	= "application/smil"; break;
		case 'snd'		: $ctype	= "audio/basic"; break;
		case 'so'		: $ctype	= "application/octet-stream"; break;
		case 'spl'		: $ctype	= "application/x-futuresplash"; break;
		case 'spx'		: $ctype	= "audio/ogg"; break;
		case 'src'		: $ctype	= "application/x-wais-source"; break;
		case 'stk'		: $ctype	= "application/hyperstudio"; break;
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
		case 'torrent'	: $ctype	= "application/x-bittorrent"; break;
		case 'tr'		: $ctype	= "application/x-troff"; break;
		case 'tsv'		: $ctype	= "text/tab-separated-values"; break;
		case 'txt'		: $ctype	= "text/plain"; break;
		case 'ustar'	: $ctype	= "application/x-ustar"; break;
		case 'vcd'		: $ctype	= "application/x-cdlink"; break;
		case 'vrml'		: $ctype	= "model/vrml"; break;
		case 'vsd'		: $ctype	= "application/vnd.visio"; break;
		case 'vss'		: $ctype	= "application/vnd.visio"; break;
		case 'vst'		: $ctype	= "application/vnd.visio"; break;
		case 'vsw'		: $ctype	= "application/vnd.visio"; break;
		case 'vxml'		: $ctype	= "application/voicexml+xml"; break;
		case 'wav'		: $ctype	= "audio/x-wav"; break;
		case 'wbmp'		: $ctype	= "image/vnd.wap.wbmp"; break;
		case 'wbmxl'	: $ctype	= "application/vnd.wap.wbxml"; break;
		case 'wm'		: $ctype	= "video/x-ms-wm"; break;
		case 'wml'		: $ctype	= "text/vnd.wap.wml"; break;
		case 'wmlc'		: $ctype	= "application/vnd.wap.wmlc"; break;
		case 'wmls'		: $ctype	= "text/vnd.wap.wmlscript"; break;
		case 'wmlsc'	: $ctype	= "application/vnd.wap.wmlscriptc"; break;
		case 'wmv'		: $ctype	= "video/x-ms-wmv"; break;
		case 'wmx'		: $ctype	= "video/x-ms-wmx"; break;
		case 'wrl'		: $ctype	= "model/vrml"; break;
		case 'xbm'		: $ctype	= "image/x-xbitmap"; break;
		case 'xdssc'	: $ctype	= "application/dssc+xml"; break;
		case 'xer'		: $ctype	= "application/patch-ops-error+xml"; break;
		case 'xht'		: $ctype	= "application/xhtml+xml"; break;
		case 'xhtml'	: $ctype	= "application/xhtml+xml"; break;
		case 'xla'		: $ctype	= "application/vnd.ms-excel"; break;
		case 'xlam'		: $ctype	= "application/vnd.ms-excel.addin.macroEnabled.12"; break;
		case 'xlc'		: $ctype	= "application/vnd.ms-excel"; break;
		case 'xlm'		: $ctype	= "application/vnd.ms-excel"; break;
		case 'xls'		: $ctype	= "application/vnd.ms-excel"; break;
		case 'xlsx'		: $ctype	= "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"; break;
		case 'xlsb'		: $ctype	= "application/vnd.ms-excel.sheet.binary.macroEnabled.12"; break;
		case 'xlt'		: $ctype	= "application/vnd.ms-excel"; break;
		case 'xltx'		: $ctype	= "application/vnd.openxmlformats-officedocument.spreadsheetml.template"; break;
		case 'xlw'		: $ctype	= "application/vnd.ms-excel"; break;
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

	return apply_filters( 'edd_file_ctype', $ctype );
}

/**
 * Reads file in chunks so big downloads are possible without changing PHP.INI
 * See http://codeigniter.com/wiki/Download_helper_for_large_files/
 *
 * @access   public
 * @param    string  $file      The file
 * @param    boolean $retbytes  Return the bytes of file
 * @return   bool|string - If string, $status || $cnt
 */
function edd_readfile_chunked( $file, $retbytes = TRUE ) {

	$chunksize = 1 * (1024 * 1024);
	$buffer    = '';
	$cnt       = 0;
	$handle    = fopen( $file, 'r' );

	if( $size = @filesize( $requested_file ) ) header("Content-Length: " . $size );

	if ( $handle === FALSE ) return FALSE;

	while ( ! feof( $handle ) ) :
	   $buffer = fread( $handle, $chunksize );
	   echo $buffer;
	   ob_flush();
	   flush();

	   if ( $retbytes ) $cnt += strlen( $buffer );
	endwhile;

	$status = fclose( $handle );

	if ( $retbytes AND $status ) return $cnt;

	return $status;
}