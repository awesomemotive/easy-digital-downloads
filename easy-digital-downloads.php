<?php
/**
 * Plugin Name: Easy Digital Downloads
 * Plugin URI: https://easydigitaldownloads.com
 * Description: The easiest way to sell digital products with WordPress.
 * Author: Easy Digital Downloads
 * Author URI: https://easydigitaldownloads.com
 * Version: 2.8.16
 * Text Domain: easy-digital-downloads
 * Domain Path: languages
 *
 * Easy Digital Downloads is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Easy Digital Downloads is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Easy Digital Downloads. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package EDD
 * @category Core
 * @author Pippin Williamson
 * @version 2.8.16
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

if ( version_compare( '5.3.0', PHP_VERSION, '>=' ) ) {
	?>
	<div class="error notice is-dismissible">
		<p>
			<?php printf( esc_html__( 'Easy Digital Downloads cannot run on PHP versions older than %s. Please contact your hosting provider to update your site.', 'easy-digital-downloads' ), '5.3.0' ); ?>
		</p>
	</div>
	<?php

	return false;
}

/**
 * Setup plugin constants.
 *
 * @access private
 * @since 1.4
 * @return void
 */
//private function setup_constants() {

//}
// Plugin version.
if ( ! defined( 'EDD_VERSION' ) ) {
	define( 'EDD_VERSION', '2.8.16' );
}

// Plugin Folder Path.
if ( ! defined( 'EDD_PLUGIN_DIR' ) ) {
	define( 'EDD_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

// Plugin Folder URL.
if ( ! defined( 'EDD_PLUGIN_URL' ) ) {
	define( 'EDD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// Plugin Root File.
if ( ! defined( 'EDD_PLUGIN_FILE' ) ) {
	define( 'EDD_PLUGIN_FILE', __FILE__ );
}

// Make sure CAL_GREGORIAN is defined.
if ( ! defined( 'CAL_GREGORIAN' ) ) {
	define( 'CAL_GREGORIAN', 1 );
}

// Plugin namespace root.
$edd['root']             = array( 'EDD' => __DIR__ . '/includes' );
$edd['extra_classes']    = array();
$edd['misnamed_classes'] = array(
	'class-edd-payement-stats',
	'class-edd-email-tags',
);

require_once __DIR__ . '/includes/class-edd-autoloader.php';
$edd['loader'] = 'EDD\\Autoloader';
new $edd['loader']( $edd['root'], $edd['extra_classes'], $edd['misnamed_classes'] );

/**
 * The main function for that returns Easy_Digital_Downloads
 *
 * The main function responsible for returning the one true Easy_Digital_Downloads
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $edd = EDD(); ?>
 *
 * @since 1.4
 * @return object|Easy_Digital_Downloads The one true Easy_Digital_Downloads Instance.
 */
function EDD() {
	return Easy_Digital_Downloads::instance();
}

// Get EDD Running.
EDD();
