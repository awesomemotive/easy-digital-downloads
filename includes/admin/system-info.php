<?php
/**
 * System Info
 *
 * These are functions are used for exporting data from Easy Digital Downloads.
 *
 * @package     Easy Digital Downloads
 * @subpackage  System Info
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * System info
 *
 * Shows the system info panel which contains version data and debug info
 *
 * @since   1.4
 * @usedby   edd_settings()
 * @author   Chris Christoff
 */

function edd_system_info() {
	global $wpdb, $edd_options;

	if ( ! class_exists( 'Browser' ) )
		require_once EDD_PLUGIN_DIR . 'includes/libraries/browser.php';

	$browser =  new Browser();

?>
	<div class="wrap">
		<h2><?php _e( 'System Information', 'edd' ) ?></h2><br/>
		<form action="edit.php?post_type=download&page=edd-system-info" method="post">
			<textarea readonly="readonly" id="system-info-textarea" name="edd-sysinfo" title="<?php _e( 'To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'edd' ); ?>">
### Begin System Info ###

## Please include this information when posting support requests ##

Multi-site:               <?php echo is_multisite() ? 'Yes' . "\n" : 'No' . "\n" ?>

SITE_URL:                 <?php echo site_url() . "\n"; ?>
HOME_URL:                 <?php echo home_url() . "\n"; ?>

EDD Version:              <?php echo EDD_VERSION . "\n"; ?>
WordPress Version:        <?php echo get_bloginfo( 'version' ) . "\n"; ?>

Ajax Enabled:             <?php echo edd_is_ajax_enabled() ? "Yes\n" : "No\n"; ?>
jQuery Validation:        <?php echo isset( $edd_options['jquery_validation'] ) ? "Yes\n" : "No\n"; ?>
Guest Checkout Enabled:   <?php echo edd_no_guest_checkout() ? "No\n" : "Yes\n"; ?>


Taxes Enabled:            <?php echo edd_use_taxes() ? "Yes\n" : "No\n"; ?>
Local Taxes Only:         <?php echo edd_local_taxes_only() ? "Yes\n" : "No\n"; ?>
Taxes After Discounts:    <?php echo edd_taxes_after_discounts() ? "Yes\n" : "No\n"; ?>

<?php echo $browser ; ?>

PHP Version:              <?php echo PHP_VERSION . "\n"; ?>
MySQL Version:            <?php echo mysql_get_server_info() . "\n"; ?>
Web Server Info:          <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>

PHP Memory Limit:         <?php echo ini_get( 'memory_limit' ) . "\n"; ?>
PHP Post Max Size:        <?php echo ini_get( 'post_max_size' ) . "\n"; ?>
PHP Time Limit:           <?php echo ini_get( 'max_execution_time' ) . "\n"; ?>

WP_DEBUG:                 <?php echo defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n" ?>

WP Table Prefix:          <?php echo "Length: ". strlen( $wpdb->prefix ); echo " Status:"; if ( strlen( $wpdb->prefix )>16 ) {echo " ERROR: Too Long";} else {echo " Acceptable";} echo "\n"; ?>

Show On Front:            <?php echo get_option( 'show_on_front' ) . "\n" ?>
Page On Front:            <?php $id = get_option( 'page_on_front' ); echo get_the_title( $id ) . ' #' . $id . "\n" ?>
Page For Posts:           <?php $id = get_option( 'page_on_front' ); echo get_the_title( $id ) . ' #' . $id . "\n" ?>

Session:                  <?php echo isset( $_SESSION ) ? 'Enabled' : 'Disabled'; ?><?php echo "\n"; ?>
Session Name:             <?php echo esc_html( ini_get( 'session.name' ) ); ?><?php echo "\n"; ?>
Cookie Path:              <?php echo esc_html( ini_get( 'session.cookie_path' ) ); ?><?php echo "\n"; ?>
Save Path:                <?php echo esc_html( ini_get( 'session.save_path' ) ); ?><?php echo "\n"; ?>
Use Cookies:              <?php echo ini_get( 'session.use_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>
Use Only Cookies:         <?php echo ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>

UPLOAD_MAX_FILESIZE:      <?php if ( function_exists( 'phpversion' ) ) echo ( edd_let_to_num( ini_get( 'upload_max_filesize' ) )/( 1024*1024 ) )."MB"; ?><?php echo "\n"; ?>
POST_MAX_SIZE:            <?php if ( function_exists( 'phpversion' ) ) echo ( edd_let_to_num( ini_get( 'post_max_size' ) )/( 1024*1024 ) )."MB"; ?><?php echo "\n"; ?>
WordPress Memory Limit:   <?php echo ( edd_let_to_num( WP_MEMORY_LIMIT )/( 1024*1024 ) )."MB"; ?><?php echo "\n"; ?>
DISPLAY ERRORS:           <?php echo ( ini_get( 'display_errors' ) ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A'; ?><?php echo "\n"; ?>
FSOCKOPEN:                <?php echo ( function_exists( 'fsockopen' ) ) ? __( 'Your server supports fsockopen.', 'edd' ) : __( 'Your server does not support fsockopen.', 'edd' ); ?><?php echo "\n"; ?>

ACTIVE PLUGINS:

<?php
$plugins = get_plugins();
$active_plugins = get_option( 'active_plugins', array() );

foreach ( $plugins as $plugin_path => $plugin ):
	// If the plugin isn't active, don't show it.
	if ( ! in_array( $plugin_path, $active_plugins ) )
		continue;

echo $plugin['Name']; ?>: <?php echo $plugin['Version'] ."\n";

endforeach; ?>

CURRENT THEME:

<?php
if ( get_bloginfo( 'version' ) < '3.4' ) {
	$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );
	echo $theme_data['Name'] . ': ' . $theme_data['Version'];
} else {
	$theme_data = wp_get_theme();
	echo $theme_data->Name . ': ' . $theme_data->Version;
}
?>


### End System Info ###
			</textarea>
			<p class="submit">
				<input type="hidden" name="edd-action" value="download_sysinfo" />
				<?php submit_button( __( 'Download System Info File', 'edd' ), 'primary', 'edd-download-sysinfo', false ); ?>
			</p>
		</form>
		</div>
	</div>
<?php
}


/**
 * Generates the System Info Download file
 *
 * @since   1.4
 * @return  void
 */

function edd_generate_sysinfo_download() {
	nocache_headers();

	header( "Content-type: text/plain" );
	header( 'Content-Disposition: attachment; filename="edd-system-info.txt"' );

	echo wp_strip_all_tags( $_POST['edd-sysinfo'] );
	exit;
}
add_action( 'edd_download_sysinfo', 'edd_generate_sysinfo_download' );