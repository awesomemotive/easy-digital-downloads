<?php
/**
 * Process Download
 *
 * @package     EDD
 * @subpackage  Functions
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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
	if ( ! isset( $_GET['download_id'] ) && isset( $_GET['download'] ) ) {
		$_GET['download_id'] = $_GET['download'];
	}

	$args = apply_filters( 'edd_process_download_args', array(
		'download' => ( isset( $_GET['download_id'] ) )  ? (int) $_GET['download_id']                       : '',
		'email'    => ( isset( $_GET['email'] ) )        ? rawurldecode( $_GET['email'] )                   : '',
		'expire'   => ( isset( $_GET['expire'] ) )       ? rawurldecode( $_GET['expire'] )                  : '',
		'file_key' => ( isset( $_GET['file'] ) )         ? (int) $_GET['file']                              : '',
		'price_id' => ( isset( $_GET['price_id'] ) )     ? (int) $_GET['price_id']                          : false,
		'key'      => ( isset( $_GET['download_key'] ) ) ? $_GET['download_key']                            : '',
		'eddfile'  => ( isset( $_GET['eddfile'] ) )      ? $_GET['eddfile']                                 : '',
		'ttl'      => ( isset( $_GET['ttl'] ) )          ? $_GET['ttl']                                     : '',
		'token'    => ( isset( $_GET['token'] ) )        ? $_GET['token']                                   : ''
	) );

	if ( ! empty( $args['eddfile'] ) && ! empty( $args['ttl'] ) && ! empty( $args['token'] ) ) {

		// Validate a signed URL that edd_process_signed_download_urlcontains a token
		$args = edd_process_signed_download_url( $args );

		// Backfill some legacy super globals for backwards compatibility
		$_GET['download_id']  = $args['download'];
		$_GET['email']        = $args['email'];
		$_GET['expire']       = $args['expire'];
		$_GET['download_key'] = $args['key'];
		$_GET['price_id']     = $args['price_id'];
	} elseif ( ! empty( $args['download'] ) && ! empty( $args['key'] ) && ! empty( $args['email'] ) && ! empty( $args['expire'] ) && isset( $args['file_key'] ) ) {

		// Validate a legacy URL without a token
		$args = edd_process_legacy_download_url( $args );
	} else {
		return;
	}

	$args['has_access'] = apply_filters( 'edd_file_download_has_access', $args['has_access'], $args['payment'], $args );

	if ( $args['payment'] && $args['has_access'] ) {

		// We've verified that the user should have access, now see if we need to require the user to be logged in.
		$require_login = edd_get_option( 'require_login_to_download', false );
		if ( $require_login && ! is_user_logged_in() ) {

			$parts = parse_url( add_query_arg( array() ) );
			wp_parse_str( $parts['query'], $file_download_args );

			EDD()->session->set( 'edd_require_login_to_download_redirect', $file_download_args );
			$login_page = wp_login_url( edd_get_file_download_login_redirect( $file_download_args ) );

			// Redirect to the login page, and have it continue the download upon successful login.
			wp_safe_redirect( $login_page );
			edd_die();
		}

		do_action( 'edd_process_verified_download', $args['download'], $args['email'], $args['payment'], $args );

		// Determine the download method set in settings
		$method  = edd_get_file_download_method();

		// Payment has been verified, setup the download
		$download_files = edd_get_download_files( $args['download'] );
		$attachment_id  = ! empty( $download_files[ $args['file_key'] ]['attachment_id'] ) ? absint( $download_files[ $args['file_key'] ]['attachment_id'] ) : false;
		$thumbnail_size = ! empty( $download_files[ $args['file_key'] ]['thumbnail_size'] ) ? sanitize_text_field( $download_files[ $args['file_key'] ]['thumbnail_size'] ) : false;
		$requested_file = isset( $download_files[ $args['file_key'] ]['file'] ) ? $download_files[ $args['file_key'] ]['file'] : '';

		/*
		 * If we have an attachment ID stored, use get_attached_file() to retrieve absolute URL
		 * If this fails or returns a relative path, we fail back to our own absolute URL detection
		 */
		$from_attachment_id = false;
		if ( edd_is_local_file( $requested_file ) && $attachment_id && 'attachment' == get_post_type( $attachment_id ) ) {
			if ( 'pdf' === strtolower( edd_get_file_extension( $requested_file ) ) ) {
				// Do not ever grab the thumbnail for PDFs. See https://github.com/easydigitaldownloads/easy-digital-downloads/issues/5491
				$thumbnail_size = false;
			}

			if ( 'redirect' === $method ) {
				if ( $thumbnail_size ) {
					$attached_file = wp_get_attachment_image_url( $attachment_id, $thumbnail_size, false );
				} else {
					$attached_file = wp_get_attachment_url( $attachment_id );
				}
			} else {
				if ( $thumbnail_size ) {
					$attachment_data = wp_get_attachment_image_src( $attachment_id, $thumbnail_size, false );

					if ( false !== $attachment_data && ! empty( $attachment_data[0] ) && filter_var( $attachment_data[0], FILTER_VALIDATE_URL) !== false ) {
						$attached_file  = $attachment_data['0'];
						$attached_file  = str_replace( site_url(), '', $attached_file );
						$attached_file  = realpath( ABSPATH . $attached_file );
					}
				}

				if ( empty( $attached_file ) ) {
					$attached_file = get_attached_file( $attachment_id, false );
				}

				// Confirm the file exists
				if ( ! file_exists( $attached_file ) ) {
					$attached_file = false;
				}
			}

			if ( $attached_file ) {
				$from_attachment_id = true;
				$requested_file     = $attached_file;
			}
		}

		// Allow the file to be altered before any headers are sent
		$requested_file = apply_filters( 'edd_requested_file', $requested_file, $download_files, $args['file_key'], $args );

		if ( 'x_sendfile' == $method && ( ! function_exists( 'apache_get_modules' ) || ! in_array( 'mod_xsendfile', apache_get_modules() ) ) ) {
			// If X-Sendfile is selected but is not supported, fallback to Direct
			$method = 'direct';
		}

		$file_details = parse_url( $requested_file );
		$schemes      = array( 'http', 'https' ); // Direct URL schemes

		$supported_streams = stream_get_wrappers();
		if ( strtoupper( substr( PHP_OS, 0, 3 ) ) !== 'WIN' && isset( $file_details['scheme'] ) && ! in_array( $file_details['scheme'], $supported_streams ) ) {
			wp_die( __( 'Error 103: Error downloading file. Please contact support.', 'easy-digital-downloads' ), __( 'File download error', 'easy-digital-downloads' ), 501 );
		}

		if ( ( ! isset( $file_details['scheme'] ) || ! in_array( $file_details['scheme'], $schemes ) ) && isset( $file_details['path'] ) && file_exists( $requested_file ) ) {

			/**
			 * Download method is set to Redirect in settings but an absolute path was provided
			 * We need to switch to a direct download in order for the file to download properly
			 */
			$method = 'direct';
		}

		/**
		 * Allow extensions to run actions prior to recording the file download log entry
		 *
		 * @since 2.6.14
		 */
		do_action( 'edd_process_download_pre_record_log', $requested_file, $args, $method );

		edd_record_download_in_log( $args['download'], $args['file_key'], array(), edd_get_ip(), $args['payment'], $args['price_id'] );

		$file_extension = edd_get_file_extension( $requested_file );
		$ctype          = edd_get_file_ctype( $file_extension );

		edd_set_time_limit( false );

		// If we're using an attachment ID to get the file, even by path, we can ignore this check.
		if ( false === $from_attachment_id ) {
			$file_is_in_allowed_location = edd_local_file_location_is_allowed( $file_details, $schemes, $requested_file );
			if ( false === $file_is_in_allowed_location ) {
				wp_die( __( 'Sorry, this file could not be downloaded.', 'easy-digital-downloads' ), __( 'Error Downloading File', 'easy-digital-downloads' ), 403 );
			}
		}

		@session_write_close();
		if ( function_exists( 'apache_setenv' ) ) {
			@apache_setenv( 'no-gzip', 1 );
		}
		@ini_set( 'zlib.output_compression', 'Off' );

		do_action( 'edd_process_download_headers', $requested_file, $args['download'], $args['email'], $args['payment'] );

		nocache_headers();
		header( 'Robots: none' );
		header( 'Content-Type: ' . $ctype );
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename="' . apply_filters( 'edd_requested_file_name', basename( $requested_file ), $args ) . '"' );
		header( 'Content-Transfer-Encoding: binary' );

		// If the file isn't locally hosted, process the redirect
		if ( filter_var( $requested_file, FILTER_VALIDATE_URL ) && ! edd_is_local_file( $requested_file ) ) {
			edd_deliver_download( $requested_file, true );
			exit;
		}

		switch ( $method ) {
			case 'redirect' :

				// Redirect straight to the file
				edd_deliver_download( $requested_file, true );
				break;
			case 'direct':
			default:
				$direct    = false;
				$file_path = $requested_file;

				if ( ( ! isset( $file_details['scheme'] ) || ! in_array( $file_details['scheme'], $schemes ) ) && isset( $file_details['path'] ) && file_exists( $requested_file ) ) {

					/** This is an absolute path */
					$direct    = true;
					$file_path = $requested_file;
				} else if ( defined( 'UPLOADS' ) && strpos( $requested_file, UPLOADS ) !== false ) {

					/**
					 * This is a local file given by URL so we need to figure out the path
					 * UPLOADS is always relative to ABSPATH
					 * site_url() is the URL to where WordPress is installed
					 */
					$file_path = str_replace( site_url(), '', $requested_file );
					$file_path = realpath( ABSPATH . $file_path );
					$direct    = true;
				} else if ( strpos( $requested_file, content_url() ) !== false ) {

					/** This is a local file given by URL so we need to figure out the path */
					$file_path = str_replace( content_url(), WP_CONTENT_DIR, $requested_file );
					$file_path = realpath( $file_path );
					$direct    = true;
				} else if ( strpos( $requested_file, set_url_scheme( content_url(), 'https' ) ) !== false ) {

					/** This is a local file given by an HTTPS URL so we need to figure out the path */
					$file_path = str_replace( set_url_scheme( content_url(), 'https' ), WP_CONTENT_DIR, $requested_file );
					$file_path = realpath( $file_path );
					$direct    = true;
				}

				// Set the file size header
				header( "Content-Length: " . @filesize( $file_path ) );

				// Now deliver the file based on the kind of software the server is running / has enabled
				if ( stristr( getenv( 'SERVER_SOFTWARE' ), 'lighttpd' ) ) {
					header( "X-LIGHTTPD-send-file: $file_path" );

				} elseif ( $direct && ( stristr( getenv( 'SERVER_SOFTWARE' ), 'nginx' ) || stristr( getenv( 'SERVER_SOFTWARE' ), 'cherokee' ) ) ) {
					$ignore_x_accel_redirect_header = apply_filters( 'edd_ignore_x_accel_redirect', false );

					if ( ! $ignore_x_accel_redirect_header ) {
						// We need a path relative to the domain
						$file_path = str_ireplace( realpath( $_SERVER['DOCUMENT_ROOT'] ), '', $file_path );
						header( "X-Accel-Redirect: /$file_path" );
					}
				}

				if ( $direct ) {
					edd_deliver_download( $file_path );
				} else {

					// The file supplied does not have a discoverable absolute path
					edd_deliver_download( $requested_file, true );
				}
				break;
		}

		edd_die();
	} else {
		$error_message = '';

		if ( ! $args['payment'] ) {
			$error_message .= 'Error 101: ';
		}

		if ( ! $args['has_access'] ) {
			$error_message .= 'Error 102: ';
		}

		$error_message .= __( 'You do not have permission to download this file', 'easy-digital-downloads' );
		wp_die( apply_filters( 'edd_deny_download_message', $error_message, __( 'Purchase Verification Failed', 'easy-digital-downloads' ) ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	exit;
}
add_action( 'init', 'edd_process_download', 100 );

/**
 * Deliver the download file
 *
 * If enabled, the file is symlinked to better support large file downloads
 *
 * @param    string    $file
 * @param    bool      $redirect True if we should perform a header redirect instead of calling edd_readfile_chunked()
 * @return   void
 */
function edd_deliver_download( $file = '', $redirect = false ) {

	/*
	 * If symlinks are enabled, a link to the file will be created
	 * This symlink is used to hide the true location of the file, even when the file URL is revealed
	 * The symlink is deleted after it is used
	 */
	if( edd_symlink_file_downloads() && edd_is_local_file( $file ) ) {

		$file = edd_get_local_path_from_url( $file );

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
		if ( ! wp_next_scheduled( 'edd_cleanup_file_symlinks' ) ) {
			wp_schedule_single_event( current_time( 'timestamp' ) + 60, 'edd_cleanup_file_symlinks' );
		}

		// Make sure the symlink doesn't already exist before we create it
		if( ! file_exists( $path ) ) {
			$link = @symlink( realpath( $file ), $path );
		} else {
			$link = true;
		}

		if( $link ) {
			// Send the browser to the file
			header( 'Location: ' . $url );
		} else {
			edd_readfile_chunked( $file );
		}

	} elseif( $redirect ) {
		header( 'Location: ' . $file );

	} else {

		// Read the file and deliver it in chunks
		edd_readfile_chunked( $file );
	}
}

/**
 * Determine if the file being requested is hosted locally or not
 *
 * @since  2.5.10
 * @since  3.1.0.3 - Updated to also check home_url (which is what previous versions of EDD were using).
 *
 * @param  string $requested_file The file being requested
 * @return bool                   If the file is hosted locally or not
 */
function edd_is_local_file( $requested_file ) {
	// By default, we assume the file is not locally hosted.
	$is_local_file = false;

	// Grab the home_url and site_url values, so we can use them to test file location.
	$site_url = preg_replace('#^https?://#', '', site_url() );
	$home_url = preg_replace('#^https?://#', '', home_url() );

	// Sanitize the requested file.
	$requested_file = preg_replace('#^(https?|file)://#', '', $requested_file );

	// First, check the Site URL.
	$is_local_url_site_url  = strpos( $requested_file, $site_url ) === 0;
	$is_local_path_site_url = strpos( $requested_file, '/' ) === 0;

	$is_local_file = ( $is_local_url_site_url || $is_local_path_site_url );

	/**
	 * If the site_url and home_url are different, and we still didn't detect a local file, try
	 * again with the home_url value.
	 */
	if ( $home_url !== $site_url && false === $is_local_file ) {
		$is_local_url_home_url  = strpos( $requested_file, $home_url ) === 0;
		$is_local_path_home_url = strpos( $requested_file, '/' ) === 0;

		$is_local_file = ( $is_local_url_home_url || $is_local_path_home_url );
	}

	/**
	 * Allow filtering the edd_is_local_file detection.
	 *
	 * EDD tries to identify if a file is hosted locally, so that we can use the proper file delivery method.
	 * By default we check the site_url, and then the home_url (in the event that those settings are different).
	 *
	 * @since 3.1.0.3
	 *
	 * @param boolean $is_local_file  If the file is hosted locally, on the server and within the site's contents.
	 * @param string  $requested_file The file that is being requested to download.
	 */
	return apply_filters( 'edd_is_local_file', $is_local_file, $requested_file );
}

/**
 * Given the URL to a file, determine it's local path
 *
 * Used during the symlink process to determine where to make the symlink point to
 *
 * @since  2.5.10
 * @param  string $url The URL of the file requested
 * @return string      If found to be locally hosted, the path to the file
 */
function edd_get_local_path_from_url( $url ) {

	$file       = $url;
	$upload_dir = wp_upload_dir();
	$edd_dir    = edd_get_uploads_base_dir();
	$upload_url = $upload_dir['baseurl'] . '/' . $edd_dir;

	if( defined( 'UPLOADS' ) && strpos( $file, UPLOADS ) !== false ) {

		/**
		 * This is a local file given by URL so we need to figure out the path
		 * UPLOADS is always relative to ABSPATH
		 * site_url() is the URL to where WordPress is installed
		 */
		$file = str_replace( site_url(), '', $file );

	} else if( strpos( $file, $upload_url ) !== false ) {

		/** This is a local file given by URL so we need to figure out the path */
		$file = str_replace( $upload_url, edd_get_upload_dir(), $file );

	} else if( strpos( $file, set_url_scheme( $upload_url, 'https' ) ) !== false ) {

		/** This is a local file given by an HTTPS URL so we need to figure out the path */
		$file = str_replace( set_url_scheme( $upload_url, 'https' ), edd_get_upload_dir(), $file );

	} elseif( strpos( $file, content_url() ) !== false ) {

		$file = str_replace( content_url(), WP_CONTENT_DIR, $file );

	}

	return $file;

}

/**
 * Get the file content type
 *
 * @param    string    file extension
 * @return   string
 */
function edd_get_file_ctype( $extension ) {
	switch( $extension ):
		case 'ac'       : $ctype = "application/pkix-attr-cert"; break;
		case 'adp'      : $ctype = "audio/adpcm"; break;
		case 'ai'       : $ctype = "application/postscript"; break;
		case 'aif'      : $ctype = "audio/x-aiff"; break;
		case 'aifc'     : $ctype = "audio/x-aiff"; break;
		case 'aiff'     : $ctype = "audio/x-aiff"; break;
		case 'air'      : $ctype = "application/vnd.adobe.air-application-installer-package+zip"; break;
		case 'apk'      : $ctype = "application/vnd.android.package-archive"; break;
		case 'asc'      : $ctype = "application/pgp-signature"; break;
		case 'atom'     : $ctype = "application/atom+xml"; break;
		case 'atomcat'  : $ctype = "application/atomcat+xml"; break;
		case 'atomsvc'  : $ctype = "application/atomsvc+xml"; break;
		case 'au'       : $ctype = "audio/basic"; break;
		case 'aw'       : $ctype = "application/applixware"; break;
		case 'avi'      : $ctype = "video/x-msvideo"; break;
		case 'bcpio'    : $ctype = "application/x-bcpio"; break;
		case 'bin'      : $ctype = "application/octet-stream"; break;
		case 'bmp'      : $ctype = "image/bmp"; break;
		case 'boz'      : $ctype = "application/x-bzip2"; break;
		case 'bpk'      : $ctype = "application/octet-stream"; break;
		case 'bz'       : $ctype = "application/x-bzip"; break;
		case 'bz2'      : $ctype = "application/x-bzip2"; break;
		case 'ccxml'    : $ctype = "application/ccxml+xml"; break;
		case 'cdmia'    : $ctype = "application/cdmi-capability"; break;
		case 'cdmic'    : $ctype = "application/cdmi-container"; break;
		case 'cdmid'    : $ctype = "application/cdmi-domain"; break;
		case 'cdmio'    : $ctype = "application/cdmi-object"; break;
		case 'cdmiq'    : $ctype = "application/cdmi-queue"; break;
		case 'cdf'      : $ctype = "application/x-netcdf"; break;
		case 'cer'      : $ctype = "application/pkix-cert"; break;
		case 'cgm'      : $ctype = "image/cgm"; break;
		case 'class'    : $ctype = "application/octet-stream"; break;
		case 'cpio'     : $ctype = "application/x-cpio"; break;
		case 'cpt'      : $ctype = "application/mac-compactpro"; break;
		case 'crl'      : $ctype = "application/pkix-crl"; break;
		case 'csh'      : $ctype = "application/x-csh"; break;
		case 'css'      : $ctype = "text/css"; break;
		case 'cu'       : $ctype = "application/cu-seeme"; break;
		case 'davmount' : $ctype = "application/davmount+xml"; break;
		case 'dbk'      : $ctype = "application/docbook+xml"; break;
		case 'dcr'      : $ctype = "application/x-director"; break;
		case 'deploy'   : $ctype = "application/octet-stream"; break;
		case 'dif'      : $ctype = "video/x-dv"; break;
		case 'dir'      : $ctype = "application/x-director"; break;
		case 'dist'     : $ctype = "application/octet-stream"; break;
		case 'distz'    : $ctype = "application/octet-stream"; break;
		case 'djv'      : $ctype = "image/vnd.djvu"; break;
		case 'djvu'     : $ctype = "image/vnd.djvu"; break;
		case 'dll'      : $ctype = "application/octet-stream"; break;
		case 'dmg'      : $ctype = "application/octet-stream"; break;
		case 'dms'      : $ctype = "application/octet-stream"; break;
		case 'doc'      : $ctype = "application/msword"; break;
		case 'docx'     : $ctype = "application/vnd.openxmlformats-officedocument.wordprocessingml.document"; break;
		case 'dotx'     : $ctype = "application/vnd.openxmlformats-officedocument.wordprocessingml.template"; break;
		case 'dssc'     : $ctype = "application/dssc+der"; break;
		case 'dtd'      : $ctype = "application/xml-dtd"; break;
		case 'dump'     : $ctype = "application/octet-stream"; break;
		case 'dv'       : $ctype = "video/x-dv"; break;
		case 'dvi'      : $ctype = "application/x-dvi"; break;
		case 'dxr'      : $ctype = "application/x-director"; break;
		case 'ecma'     : $ctype = "application/ecmascript"; break;
		case 'elc'      : $ctype = "application/octet-stream"; break;
		case 'emma'     : $ctype = "application/emma+xml"; break;
		case 'eps'      : $ctype = "application/postscript"; break;
		case 'epub'     : $ctype = "application/epub+zip"; break;
		case 'etx'      : $ctype = "text/x-setext"; break;
		case 'exe'      : $ctype = "application/octet-stream"; break;
		case 'exi'      : $ctype = "application/exi"; break;
		case 'ez'       : $ctype = "application/andrew-inset"; break;
		case 'f4v'      : $ctype = "video/x-f4v"; break;
		case 'fli'      : $ctype = "video/x-fli"; break;
		case 'flv'      : $ctype = "video/x-flv"; break;
		case 'gif'      : $ctype = "image/gif"; break;
		case 'gml'      : $ctype = "application/srgs"; break;
		case 'gpx'      : $ctype = "application/gml+xml"; break;
		case 'gram'     : $ctype = "application/gpx+xml"; break;
		case 'grxml'    : $ctype = "application/srgs+xml"; break;
		case 'gtar'     : $ctype = "application/x-gtar"; break;
		case 'gxf'      : $ctype = "application/gxf"; break;
		case 'hdf'      : $ctype = "application/x-hdf"; break;
		case 'hqx'      : $ctype = "application/mac-binhex40"; break;
		case 'htm'      : $ctype = "text/html"; break;
		case 'html'     : $ctype = "text/html"; break;
		case 'ice'      : $ctype = "x-conference/x-cooltalk"; break;
		case 'ico'      : $ctype = "image/x-icon"; break;
		case 'ics'      : $ctype = "text/calendar"; break;
		case 'ief'      : $ctype = "image/ief"; break;
		case 'ifb'      : $ctype = "text/calendar"; break;
		case 'iges'     : $ctype = "model/iges"; break;
		case 'igs'      : $ctype = "model/iges"; break;
		case 'ink'      : $ctype = "application/inkml+xml"; break;
		case 'inkml'    : $ctype = "application/inkml+xml"; break;
		case 'ipfix'    : $ctype = "application/ipfix"; break;
		case 'jar'      : $ctype = "application/java-archive"; break;
		case 'jnlp'     : $ctype = "application/x-java-jnlp-file"; break;
		case 'jp2'      : $ctype = "image/jp2"; break;
		case 'jpe'      : $ctype = "image/jpeg"; break;
		case 'jpeg'     : $ctype = "image/jpeg"; break;
		case 'jpg'      : $ctype = "image/jpeg"; break;
		case 'js'       : $ctype = "application/javascript"; break;
		case 'json'     : $ctype = "application/json"; break;
		case 'jsonml'   : $ctype = "application/jsonml+json"; break;
		case 'kar'      : $ctype = "audio/midi"; break;
		case 'latex'    : $ctype = "application/x-latex"; break;
		case 'lha'      : $ctype = "application/octet-stream"; break;
		case 'lrf'      : $ctype = "application/octet-stream"; break;
		case 'lzh'      : $ctype = "application/octet-stream"; break;
		case 'lostxml'  : $ctype = "application/lost+xml"; break;
		case 'm3u'      : $ctype = "audio/x-mpegurl"; break;
		case 'm4a'      : $ctype = "audio/mp4a-latm"; break;
		case 'm4b'      : $ctype = "audio/mp4a-latm"; break;
		case 'm4p'      : $ctype = "audio/mp4a-latm"; break;
		case 'm4u'      : $ctype = "video/vnd.mpegurl"; break;
		case 'm4v'      : $ctype = "video/x-m4v"; break;
		case 'm21'      : $ctype = "application/mp21"; break;
		case 'ma'       : $ctype = "application/mathematica"; break;
		case 'mac'      : $ctype = "image/x-macpaint"; break;
		case 'mads'     : $ctype = "application/mads+xml"; break;
		case 'man'      : $ctype = "application/x-troff-man"; break;
		case 'mar'      : $ctype = "application/octet-stream"; break;
		case 'mathml'   : $ctype = "application/mathml+xml"; break;
		case 'mbox'     : $ctype = "application/mbox"; break;
		case 'me'       : $ctype = "application/x-troff-me"; break;
		case 'mesh'     : $ctype = "model/mesh"; break;
		case 'metalink' : $ctype = "application/metalink+xml"; break;
		case 'meta4'    : $ctype = "application/metalink4+xml"; break;
		case 'mets'     : $ctype = "application/mets+xml"; break;
		case 'mid'      : $ctype = "audio/midi"; break;
		case 'midi'     : $ctype = "audio/midi"; break;
		case 'mif'      : $ctype = "application/vnd.mif"; break;
		case 'mods'     : $ctype = "application/mods+xml"; break;
		case 'mov'      : $ctype = "video/quicktime"; break;
		case 'movie'    : $ctype = "video/x-sgi-movie"; break;
		case 'm1v'      : $ctype = "video/mpeg"; break;
		case 'm2v'      : $ctype = "video/mpeg"; break;
		case 'mp2'      : $ctype = "audio/mpeg"; break;
		case 'mp2a'     : $ctype = "audio/mpeg"; break;
		case 'mp21'     : $ctype = "application/mp21"; break;
		case 'mp3'      : $ctype = "audio/mpeg"; break;
		case 'mp3a'     : $ctype = "audio/mpeg"; break;
		case 'mp4'      : $ctype = "video/mp4"; break;
		case 'mp4s'     : $ctype = "application/mp4"; break;
		case 'mpe'      : $ctype = "video/mpeg"; break;
		case 'mpeg'     : $ctype = "video/mpeg"; break;
		case 'mpg'      : $ctype = "video/mpeg"; break;
		case 'mpg4'     : $ctype = "video/mpeg"; break;
		case 'mpga'     : $ctype = "audio/mpeg"; break;
		case 'mrc'      : $ctype = "application/marc"; break;
		case 'mrcx'     : $ctype = "application/marcxml+xml"; break;
		case 'ms'       : $ctype = "application/x-troff-ms"; break;
		case 'mscml'    : $ctype = "application/mediaservercontrol+xml"; break;
		case 'msh'      : $ctype = "model/mesh"; break;
		case 'mxf'      : $ctype = "application/mxf"; break;
		case 'mxu'      : $ctype = "video/vnd.mpegurl"; break;
		case 'nc'       : $ctype = "application/x-netcdf"; break;
		case 'oda'      : $ctype = "application/oda"; break;
		case 'oga'      : $ctype = "application/ogg"; break;
		case 'ogg'      : $ctype = "application/ogg"; break;
		case 'ogx'      : $ctype = "application/ogg"; break;
		case 'omdoc'    : $ctype = "application/omdoc+xml"; break;
		case 'onetoc'   : $ctype = "application/onenote"; break;
		case 'onetoc2'  : $ctype = "application/onenote"; break;
		case 'onetmp'   : $ctype = "application/onenote"; break;
		case 'onepkg'   : $ctype = "application/onenote"; break;
		case 'opf'      : $ctype = "application/oebps-package+xml"; break;
		case 'oxps'     : $ctype = "application/oxps"; break;
		case 'p7c'      : $ctype = "application/pkcs7-mime"; break;
		case 'p7m'      : $ctype = "application/pkcs7-mime"; break;
		case 'p7s'      : $ctype = "application/pkcs7-signature"; break;
		case 'p8'       : $ctype = "application/pkcs8"; break;
		case 'p10'      : $ctype = "application/pkcs10"; break;
		case 'pbm'      : $ctype = "image/x-portable-bitmap"; break;
		case 'pct'      : $ctype = "image/pict"; break;
		case 'pdb'      : $ctype = "chemical/x-pdb"; break;
		case 'pdf'      : $ctype = "application/pdf"; break;
		case 'pki'      : $ctype = "application/pkixcmp"; break;
		case 'pkipath'  : $ctype = "application/pkix-pkipath"; break;
		case 'pfr'      : $ctype = "application/font-tdpfr"; break;
		case 'pgm'      : $ctype = "image/x-portable-graymap"; break;
		case 'pgn'      : $ctype = "application/x-chess-pgn"; break;
		case 'pgp'      : $ctype = "application/pgp-encrypted"; break;
		case 'pic'      : $ctype = "image/pict"; break;
		case 'pict'     : $ctype = "image/pict"; break;
		case 'pkg'      : $ctype = "application/octet-stream"; break;
		case 'png'      : $ctype = "image/png"; break;
		case 'pnm'      : $ctype = "image/x-portable-anymap"; break;
		case 'pnt'      : $ctype = "image/x-macpaint"; break;
		case 'pntg'     : $ctype = "image/x-macpaint"; break;
		case 'pot'      : $ctype = "application/vnd.ms-powerpoint"; break;
		case 'potx'     : $ctype = "application/vnd.openxmlformats-officedocument.presentationml.template"; break;
		case 'ppm'      : $ctype = "image/x-portable-pixmap"; break;
		case 'pps'      : $ctype = "application/vnd.ms-powerpoint"; break;
		case 'ppsx'     : $ctype = "application/vnd.openxmlformats-officedocument.presentationml.slideshow"; break;
		case 'ppt'      : $ctype = "application/vnd.ms-powerpoint"; break;
		case 'pptx'     : $ctype = "application/vnd.openxmlformats-officedocument.presentationml.presentation"; break;
		case 'prf'      : $ctype = "application/pics-rules"; break;
		case 'ps'       : $ctype = "application/postscript"; break;
		case 'psd'      : $ctype = "image/photoshop"; break;
		case 'qt'       : $ctype = "video/quicktime"; break;
		case 'qti'      : $ctype = "image/x-quicktime"; break;
		case 'qtif'     : $ctype = "image/x-quicktime"; break;
		case 'ra'       : $ctype = "audio/x-pn-realaudio"; break;
		case 'ram'      : $ctype = "audio/x-pn-realaudio"; break;
		case 'ras'      : $ctype = "image/x-cmu-raster"; break;
		case 'rdf'      : $ctype = "application/rdf+xml"; break;
		case 'rgb'      : $ctype = "image/x-rgb"; break;
		case 'rm'       : $ctype = "application/vnd.rn-realmedia"; break;
		case 'rmi'      : $ctype = "audio/midi"; break;
		case 'roff'     : $ctype = "application/x-troff"; break;
		case 'rss'      : $ctype = "application/rss+xml"; break;
		case 'rtf'      : $ctype = "text/rtf"; break;
		case 'rtx'      : $ctype = "text/richtext"; break;
		case 'sgm'      : $ctype = "text/sgml"; break;
		case 'sgml'     : $ctype = "text/sgml"; break;
		case 'sh'       : $ctype = "application/x-sh"; break;
		case 'shar'     : $ctype = "application/x-shar"; break;
		case 'sig'      : $ctype = "application/pgp-signature"; break;
		case 'silo'     : $ctype = "model/mesh"; break;
		case 'sit'      : $ctype = "application/x-stuffit"; break;
		case 'skd'      : $ctype = "application/x-koan"; break;
		case 'skm'      : $ctype = "application/x-koan"; break;
		case 'skp'      : $ctype = "application/x-koan"; break;
		case 'skt'      : $ctype = "application/x-koan"; break;
		case 'sldx'     : $ctype = "application/vnd.openxmlformats-officedocument.presentationml.slide"; break;
		case 'smi'      : $ctype = "application/smil"; break;
		case 'smil'     : $ctype = "application/smil"; break;
		case 'snd'      : $ctype = "audio/basic"; break;
		case 'so'       : $ctype = "application/octet-stream"; break;
		case 'spl'      : $ctype = "application/x-futuresplash"; break;
		case 'spx'      : $ctype = "audio/ogg"; break;
		case 'src'      : $ctype = "application/x-wais-source"; break;
		case 'stk'      : $ctype = "application/hyperstudio"; break;
		case 'sv4cpio'  : $ctype = "application/x-sv4cpio"; break;
		case 'sv4crc'   : $ctype = "application/x-sv4crc"; break;
		case 'svg'      : $ctype = "image/svg+xml"; break;
		case 'swf'      : $ctype = "application/x-shockwave-flash"; break;
		case 't'        : $ctype = "application/x-troff"; break;
		case 'tar'      : $ctype = "application/x-tar"; break;
		case 'tcl'      : $ctype = "application/x-tcl"; break;
		case 'tex'      : $ctype = "application/x-tex"; break;
		case 'texi'     : $ctype = "application/x-texinfo"; break;
		case 'texinfo'  : $ctype = "application/x-texinfo"; break;
		case 'tif'      : $ctype = "image/tiff"; break;
		case 'tiff'     : $ctype = "image/tiff"; break;
		case 'torrent'  : $ctype = "application/x-bittorrent"; break;
		case 'tr'       : $ctype = "application/x-troff"; break;
		case 'tsv'      : $ctype = "text/tab-separated-values"; break;
		case 'txt'      : $ctype = "text/plain"; break;
		case 'ustar'    : $ctype = "application/x-ustar"; break;
		case 'vcd'      : $ctype = "application/x-cdlink"; break;
		case 'vrml'     : $ctype = "model/vrml"; break;
		case 'vsd'      : $ctype = "application/vnd.visio"; break;
		case 'vss'      : $ctype = "application/vnd.visio"; break;
		case 'vst'      : $ctype = "application/vnd.visio"; break;
		case 'vsw'      : $ctype = "application/vnd.visio"; break;
		case 'vxml'     : $ctype = "application/voicexml+xml"; break;
		case 'wav'      : $ctype = "audio/x-wav"; break;
		case 'wbmp'     : $ctype = "image/vnd.wap.wbmp"; break;
		case 'wbmxl'    : $ctype = "application/vnd.wap.wbxml"; break;
		case 'webp'     : $ctype = "image/webp"; break;
		case 'wm'       : $ctype = "video/x-ms-wm"; break;
		case 'wml'      : $ctype = "text/vnd.wap.wml"; break;
		case 'wmlc'     : $ctype = "application/vnd.wap.wmlc"; break;
		case 'wmls'     : $ctype = "text/vnd.wap.wmlscript"; break;
		case 'wmlsc'    : $ctype = "application/vnd.wap.wmlscriptc"; break;
		case 'wmv'      : $ctype = "video/x-ms-wmv"; break;
		case 'wmx'      : $ctype = "video/x-ms-wmx"; break;
		case 'wrl'      : $ctype = "model/vrml"; break;
		case 'xbm'      : $ctype = "image/x-xbitmap"; break;
		case 'xdssc'    : $ctype = "application/dssc+xml"; break;
		case 'xer'      : $ctype = "application/patch-ops-error+xml"; break;
		case 'xht'      : $ctype = "application/xhtml+xml"; break;
		case 'xhtml'    : $ctype = "application/xhtml+xml"; break;
		case 'xla'      : $ctype = "application/vnd.ms-excel"; break;
		case 'xlam'     : $ctype = "application/vnd.ms-excel.addin.macroEnabled.12"; break;
		case 'xlc'      : $ctype = "application/vnd.ms-excel"; break;
		case 'xlm'      : $ctype = "application/vnd.ms-excel"; break;
		case 'xls'      : $ctype = "application/vnd.ms-excel"; break;
		case 'xlsx'     : $ctype = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"; break;
		case 'xlsb'     : $ctype = "application/vnd.ms-excel.sheet.binary.macroEnabled.12"; break;
		case 'xlt'      : $ctype = "application/vnd.ms-excel"; break;
		case 'xltx'     : $ctype = "application/vnd.openxmlformats-officedocument.spreadsheetml.template"; break;
		case 'xlw'      : $ctype = "application/vnd.ms-excel"; break;
		case 'xml'      : $ctype = "application/xml"; break;
		case 'xpm'      : $ctype = "image/x-xpixmap"; break;
		case 'xsl'      : $ctype = "application/xml"; break;
		case 'xslt'     : $ctype = "application/xslt+xml"; break;
		case 'xul'      : $ctype = "application/vnd.mozilla.xul+xml"; break;
		case 'xwd'      : $ctype = "image/x-xwindowdump"; break;
		case 'xyz'      : $ctype = "chemical/x-xyz"; break;
		case 'zip'      : $ctype = "application/zip"; break;
		default         : $ctype = "application/force-download";
	endswitch;

	if( wp_is_mobile() ) {
		$ctype = 'application/octet-stream';
	}

	return apply_filters( 'edd_file_ctype', $ctype );
}

/**
 * Reads file in chunks so big downloads are possible without changing PHP.INI
 * See http://codeigniter.com/wiki/Download_helper_for_large_files/
 *
 * @param    string  $file     The file
 * @param    boolean $retbytes Return the bytes of file
 *
 * @return   bool|string        If string, $status || $cnt
 */
function edd_readfile_chunked( $file, $retbytes = true ) {
	while ( ob_get_level() > 0 ) {
		ob_end_clean();
	}

	ob_start();

	// If output buffers exist, make sure they are closed. See https://github.com/easydigitaldownloads/easy-digital-downloads/issues/6387
	if ( ob_get_length() ) {
		ob_clean();
	}

	$chunksize = 1024 * 1024;
	$buffer    = '';
	$cnt       = 0;
	$handle    = @fopen( $file, 'r' );

	if ( $size = @filesize( $file ) ) {
		header( "Content-Length: " . $size );
	}

	if ( false === $handle ) {
		return false;
	}

	if ( isset( $_SERVER['HTTP_RANGE'] ) ) {
		list( $size_unit, $range ) = explode( '=', $_SERVER['HTTP_RANGE'], 2 );
		if ( 'bytes' === $size_unit ) {
			if ( strpos( ',', $range ) ) {
				list( $range ) = explode( ',', $range, 1 );
			}
		} else {
			$range = '';
			header( 'HTTP/1.1 416 Requested Range Not Satisfiable' );
			exit;
		}
	} else {
		$range = '';
	}

	if ( empty( $range ) ) {
		$seek_start = null;
		$seek_end   = null;
	} else {
		list( $seek_start, $seek_end ) = explode( '-', $range, 2 );
	}

	$seek_end   = ( empty( $seek_end ) ) ? ( $size - 1 ) : min( abs( intval( $seek_end ) ), ( $size - 1 ) );
	$seek_start = ( empty( $seek_start ) || $seek_end < abs( intval( $seek_start ) ) ) ? 0 : max( abs( intval( $seek_start ) ), 0 );

	// Only send partial content header if downloading a piece of the file (IE workaround)
	if ( $seek_start > 0 || $seek_end < ( $size - 1 ) ) {
		header( 'HTTP/1.1 206 Partial Content' );
		header( 'Content-Range: bytes ' . $seek_start . '-' . $seek_end . '/' . $size );
		header( 'Content-Length: ' . ( $seek_end - $seek_start + 1 ) );
	} else {
		header( "Content-Length: $size" );
	}

	header( 'Accept-Ranges: bytes' );

	edd_set_time_limit( false );

	fseek( $handle, $seek_start );

	while ( ! @feof( $handle ) ) {
		$buffer = @fread( $handle, $chunksize );
		echo $buffer;
		ob_flush();

		if ( ob_get_length() ) {
			ob_flush();
			flush();
		}

		if ( $retbytes ) {
			$cnt += strlen( $buffer );
		}

		if ( connection_status() != 0 ) {
			@fclose( $handle );
			exit;
		}
	}

	ob_flush();

	$status = @fclose( $handle );

	if ( $retbytes && $status ) {
		return $cnt;
	}

	return $status;
}

/**
 * Used to process an old URL format for downloads
 *
 * @since  2.3
 * @param  array $args Arguments provided to download a file
 * @return array       Same arguments, with the status of verification added
 */
function edd_process_legacy_download_url( $args ) {

	// Verify the payment
	$args['payment'] = edd_verify_download_link( $args['download'], $args['key'], $args['email'], $args['expire'], $args['file_key'] );

	// Defaulting this to true for now because the method below doesn't work well
	$args['has_access'] = true;

	$args['payment']    = $args['payment'];
	$args['has_access'] = $args['has_access'];

	return $args;
}

/**
 * Used to process a signed URL for processing downloads
 *
 * @since  2.3
 * @param  array $args Arguments provided to download a file
 * @return array       Same arguments, with the status of verification added
 */
function edd_process_signed_download_url( $args ) {

	$parts = parse_url( add_query_arg( array() ) );
	wp_parse_str( $parts['query'], $query_args );
	$url = add_query_arg( $query_args, site_url() );

	$valid_token = edd_validate_url_token( $url );

	// Bail if the token isn't valid.
	// The request should pass through EDD, or custom handling can be enabled with the action.
	if ( ! $valid_token ) {
		$args['payment']    = false;
		$args['has_access'] = false;

		return $args;
	}

	$order_parts = explode( ':', rawurldecode( $_GET['eddfile'] ) );
	$price_id    = isset( $order_parts[3] ) ? (int) $order_parts[3] : null;

	// Check to make sure not at download limit
	if ( edd_is_file_at_download_limit( $order_parts[1], $order_parts[0], $order_parts[2], $price_id ) ) {
		wp_die( apply_filters( 'edd_download_limit_reached_text', __( 'Sorry but you have hit your download limit for this file.', 'easy-digital-downloads' ) ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	$order            = edd_get_order( $order_parts[0] );
	$args['expire']   = $_GET['ttl'];
	$args['download'] = $order_parts[1];
	$args['payment']  = $order->id;
	$args['file_key'] = $order_parts[2];
	$args['price_id'] = $price_id;
	$args['email']    = $order->email;
	$args['key']      = $order->payment_key;

	// Access is granted if there's at least one `complete` order item that matches the order + download + price ID.
	$args['has_access'] = edd_order_grants_access_to_download_files( array(
		'order_id'   => $order->id,
		'product_id' => $args['download'],
		'price_id'   => $args['price_id'],
	) );

	return $args;
}

/**
 * Determines whether or not a given order grants access to download files associated with a given
 * product ID and price ID combination. Returns true if there's at least one deliverable order item
 * matching the requirements.
 *
 * @param array $args
 *
 * @since 3.0
 * @return bool
 */
function edd_order_grants_access_to_download_files( $args ) {
	$args = wp_parse_args( $args, array(
		'order_id'   => 0,
		'product_id' => 0,
		'price_id'   => null,
	) );

	// Order and product IDs are required.
	if ( empty( $args['order_id'] ) || empty( $args['product_id'] ) ) {
		return false;
	}

	$args['status'] = edd_get_deliverable_order_item_statuses();
	if ( is_null( $args['price_id'] ) ) {
		unset( $args['price_id'] );
	}

	// Check if the download was purchased directly.
	$order_items = edd_count_order_items( $args );

	if ( $order_items > 0 ) {
		return true;
	}

	$order_items = edd_get_order_items(
		array(
			'order_id' => $args['order_id'],
			'status'   => edd_get_deliverable_order_item_statuses(),
			'fields'   => 'product_id',
		)
	);

	// Unlikely, but return false if there are no order items found at all.
	if ( empty( $order_items ) ) {
		return false;
	}

	// Include some fallback checks for incorrectly created download URLs and bundled items.
	$product_to_check = isset( $args['price_id'] ) && is_numeric( $args['price_id'] ) ? "{$args['product_id']}_{$args['price_id']}" : $args['product_id'];
	foreach ( $order_items as $product_id ) {
		$download = edd_get_download( $product_id );
		if ( ! $download instanceof EDD_Download ) {
			continue;
		}

		// Check if the requested download is part of a bundle.
		if ( 'bundle' === $download->type && in_array( $product_to_check, $download->get_bundled_downloads() ) ) {
			return true;
		}

		// Check if the requested download is not variably priced but incorrectly included a price ID.
		if ( empty( $args['price_id'] ) && $args['product_id'] == $product_id && ! $download->has_variable_prices() ) {
			return true;
		}
	}

	return false;
}

/**
 * Determines if we should use symbolic links during the file download process
 *
 * @since  2.5
 * @return bool
 */
function edd_symlink_file_downloads() {
	$symlink = edd_get_option( 'symlink_file_downloads', false ) && function_exists( 'symlink' );
	return (bool) apply_filters( 'edd_symlink_file_downloads', $symlink );
}

/**
 * Given a local URL, make sure the requests matches the request scheme
 *
 * @since  2.5.10
 * @param  string $requested_file The Requested File
 * @param  array  $download_files The download files
 * @param  string $file_key       The file key
 * @return string                 The file (if local) with the matched scheme
 */
function edd_set_requested_file_scheme( $requested_file, $download_files, $file_key ) {

	// If it's a URL and it's local, let's make sure the scheme matches the requested scheme
	if ( filter_var( $requested_file, FILTER_VALIDATE_URL ) && edd_is_local_file( $requested_file ) ) {

		if ( false === strpos( $requested_file, 'https://' ) && is_ssl() ) {
			$requested_file = str_replace( 'http://', 'https://', $requested_file );
		} elseif ( ! is_ssl() && 0 === strpos( $requested_file, 'https://' ) ) {
			$requested_file = str_replace( 'https://', 'http://', $requested_file );
		}

	}

	return $requested_file;

}
add_filter( 'edd_requested_file', 'edd_set_requested_file_scheme', 10, 3 );

/**
 * Perform a head request on file URLs before attempting to download to check if they are accessible.
 *
 * @since  2.6.14
 * @param  string $requested_file The Requested File
 * @param  array  $args           Arguments
 * @param  string $method         The download mehtod being sed
 * @return void
 */
function edd_check_file_url_head( $requested_file, $args, $method ) {

	// If this is a file URL (not a path), perform a head request to determine if it's valid
	if( filter_var( $requested_file, FILTER_VALIDATE_URL ) && ! edd_is_local_file( $requested_file ) ) {

		$valid   = true;
		$request = wp_remote_head( $requested_file );

		if( is_wp_error( $request ) ) {

			$valid   = false;
			$message = $request;
			$title   = __( 'Invalid file', 'easy-digital-downloads' );

		}

		if( 404 === wp_remote_retrieve_response_code( $request ) ) {

			$valid   = false;
			$message = __( 'The requested file could not be found. Error 404.', 'easy-digital-downloads' );
			$title   = __( 'File not found', 'easy-digital-downloads' );

		}

		if( ! $valid ) {

			do_action( 'edd_check_file_url_head_invalid', $requested_file, $args, $method );
			wp_die( $message, $title, array( 'response' => 403 ) );

		}

	}

}

/**
 * Determines if a file should be allowed to be downloaded by making sure it's within the wp-content directory.
 *
 * @since 2.9.13
 *
 * @param $file_details
 * @param $schemas
 * @param $requested_file
 *
 * @return boolean
 */
function edd_local_file_location_is_allowed( $file_details, $schemas, $requested_file ) {
	$should_allow = true;

	// If the file is an absolute path, make sure it's in the wp-content directory, to prevent store owners from accidentally allowing privileged files from being downloaded.
	if ( ( ! isset( $file_details['scheme'] ) || ! in_array( $file_details['scheme'], $schemas ) ) && isset( $file_details['path'] ) ) {

		/** This is an absolute path */
		$requested_file         = wp_normalize_path( realpath( $requested_file ) );
		$normalized_abspath     = wp_normalize_path( ABSPATH );
		$normalized_content_dir = wp_normalize_path( WP_CONTENT_DIR );

		if ( 0 !== strpos( $requested_file, $normalized_abspath ) || false === strpos( $requested_file, $normalized_content_dir ) ) {
			// If the file is not within the WP_CONTENT_DIR, it should not be able to be downloaded.
			$should_allow = false;
		}

	}

	return apply_filters( 'edd_local_file_location_is_allowed', $should_allow, $file_details, $schemas, $requested_file );
}

/**
 * Detect downloading a file immediately after a forced login.
 *
 * When the store requires being logged in to download files, this handles the file download after logging in.
 * We need this otherwise the file is downloaded immediately after successfully logging in, but the page never changes.
 *
 * @since 3.1
 */
function edd_redirect_file_download_after_login() {
	$token = isset( $_GET['_token'] ) ? sanitize_text_field( $_GET['_token'] ) : false;

	// No nonce provided, redirect to the homepage.
	if ( empty( $token ) ) {
		wp_safe_redirect( home_url() );
	}

	$redirect_session_data = EDD()->session->get( 'edd_require_login_to_download_redirect' );

	// Nonce verification failed, redirect to the homepage.
	if ( ! \EDD\Utils\Tokenizer::is_token_valid( $token, $redirect_session_data ) ) {
		wp_safe_redirect( home_url() );
	}

	EDD()->session->set( 'edd_require_login_to_download_redirect', '' );

	// No file download session data, redirect to the homepage.
	if ( empty( $redirect_session_data ) ) {
		wp_safe_redirect( home_url() );
	}

	// Add some Javascript to download the file and then clear the query args from the page.
	add_action( 'wp_footer', function() use ($redirect_session_data) {
		printf('
			<script type="text/javascript">
			(function(){
				var download_link = document.createElement("a");
				download_link.href = "' . add_query_arg( $redirect_session_data, home_url( 'index.php' ) ) . '";
				download_link.setAttribute("download", "");
				document.body.appendChild(download_link);
				download_link.click();

				setTimeout(
					() => {
						window.location.replace( window.location.href.split(/[?#]/)[0] );
					}, 250
				);
			})();
			</script>
		');
	} );
}
add_action( 'edd_process_file_download_after_login', 'edd_redirect_file_download_after_login', 10, 2 );

/**
 * Filter removed in EDD 2.7
 *
 * @see https://github.com/easydigitaldownloads/easy-digital-downloads/issues/5450
 */
// add_action( 'edd_process_download_pre_record_log', 'edd_check_file_url_head', 10, 3 );
