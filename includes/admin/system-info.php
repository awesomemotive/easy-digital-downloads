<?php
/**
 * System info
 *
 * Shows the system info panel which contains version data and debug info
 *
 * @since 		1.4
 * @usedby 		edd_settings()
 */
function edd_system_info() { ?>
<div class="wrap">
<h2><?php _e('System Information','edd') ?></h2><br/>
<form action="./" method="post">
<a href="#" class="button-primary" id="download">Download</a><br/><br/>
<textarea readonly="readonly" id="system-info-textarea" title="To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).">

	### Begin System Info ###

	Multi-site:               <?php echo is_multisite() ? 'Yes' . "\n" : 'No' . "\n" ?>

	SITE_URL:                 <?php echo site_url() . "\n"; ?>
	HOME_URL:                 <?php echo home_url() . "\n"; ?>

	EDD Version:      		  <?php echo EDD_VERSION . "\n"; ?>
	WordPress Version:        <?php echo get_bloginfo('version') . "\n"; ?>

	<?php require_once('browser.php'); $browser =  new Browser(); echo $browser ; ?>

	PHP Version:              <?php echo PHP_VERSION . "\n"; ?>
	MySQL Version:            <?php echo mysql_get_server_info() . "\n"; ?>
	Web Server Info:          <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>

	PHP Memory Limit:         <?php echo ini_get('memory_limit') . "\n"; ?>
	PHP Post Max Size:        <?php echo ini_get('post_max_size') . "\n"; ?>

	WP_DEBUG:                 <?php echo defined('WP_DEBUG') ? WP_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n" ?>

	WP Table Prefix:          <?php global $wpdb; echo "Length: ". strlen($wpdb->prefix); echo " Status:"; if (strlen($wpdb->prefix)>16){echo " ERROR: Too Long";} else {echo " Acceptable";} echo "\n"; ?>

	Show On Front:            <?php echo get_option('show_on_front') . "\n" ?>
	Page On Front:            <?php echo get_option('page_on_front') . "\n" ?>
	Page For Posts:           <?php echo get_option('page_for_posts') . "\n" ?>

	Session:                  <?php echo isset( $_SESSION ) ? 'Enabled' : 'Disabled'; ?><?php echo "\n"; ?>
	Session Name:             <?php echo esc_html( ini_get( 'session.name' ) ); ?><?php echo "\n"; ?>
	Cookie Path:              <?php echo esc_html( ini_get( 'session.cookie_path' ) ); ?><?php echo "\n"; ?>
	Save Path:                <?php echo esc_html( ini_get( 'session.save_path' ) ); ?><?php echo "\n"; ?>
	Use Cookies:              <?php echo (ini_get('session.use_cookies') ? 'On' : 'Off'); ?><?php echo "\n"; ?>
	Use Only Cookies:         <?php echo (ini_get('session.use_only_cookies') ? 'On' : 'Off'); ?><?php echo "\n"; ?>

	UPLOAD_MAX_FILESIZE:      <?php if(function_exists('phpversion')) echo (edd_let_to_num(ini_get('upload_max_filesize'))/(1024*1024))."MB"; ?><?php echo "\n"; ?>
	POST_MAX_SIZE:            <?php if(function_exists('phpversion')) echo (edd_let_to_num(ini_get('post_max_size'))/(1024*1024))."MB"; ?><?php echo "\n"; ?>
	WordPress Memory Limit:   <?php echo (edd_let_to_num(WP_MEMORY_LIMIT)/(1024*1024))."MB"; ?><?php echo "\n"; ?>
	WP_DEBUG:                 <?php echo (WP_DEBUG) ? __('On', 'edd') : __('Off', 'edd'); ?><?php echo "\n"; ?>
	DISPLAY ERRORS:           <?php echo (ini_get('display_errors')) ? 'On (' . ini_get('display_errors') . ')' : 'N/A'; ?><?php echo "\n"; ?>
	FSOCKOPEN:                <?php echo (function_exists('fsockopen')) ? __('Your server supports fsockopen.', 'edd') : __('Your server does not support fsockopen.', 'edd'); ?><?php echo "\n"; ?>

	ACTIVE PLUGINS:

<?php
$plugins = get_plugins();
$active_plugins = get_option('active_plugins', array());

foreach ( $plugins as $plugin_path => $plugin ):

	//If the plugin isn't active, don't show it.
	if ( !in_array($plugin_path, $active_plugins) )
		continue;
?>
	<?php echo $plugin['Name']; ?>: <?php echo $plugin['Version']; ?>

<?php endforeach; ?>

	CURRENT THEME:

	<?php
	if ( get_bloginfo('version') < '3.4' ) {
		$theme_data = get_theme_data(get_stylesheet_directory() . '/style.css');
		echo $theme_data['Name'] . ': ' . $theme_data['Version'];
	} else {
		$theme_data = wp_get_theme();
		echo $theme_data->Name . ': ' . $theme_data->Version;
	}
?>


	### End System Info ###
</textarea>
</form>
</div>
</div>
<?php
}
/**
 * EDD Let To Num
 *
 * Does Size Conversions
 *
 * @since 		1.4
 * @usedby 		edd_settings()
 */
function edd_let_to_num($v) {
    $l = substr($v, -1);
    $ret = substr($v, 0, -1);
    switch(strtoupper($l)){
    case 'P':
        $ret *= 1024;
    case 'T':
        $ret *= 1024;
    case 'G':
        $ret *= 1024;
    case 'M':
        $ret *= 1024;
    case 'K':
        $ret *= 1024;
        break;
    }
    return $ret;
}