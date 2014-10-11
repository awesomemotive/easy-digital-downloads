<?php
/**
 * Admin Options Page
 *
 * @package     EDD
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/* Returns list elements for jQuery tab navigation 
 * based on header callback
 * 
 * @scince 2.1.2
 * @todo Use sprintf to sanitize  $field['id'] instead using str_replace() Should be faster!
 * @return string
 */

function edd_getTabHeader($page, $section){
    global $mashsb_options;
    global $wp_settings_fields;
    
    if (!isset($wp_settings_fields[$page][$section]))
        return;
    
    echo '<ul>';
    foreach ((array) $wp_settings_fields[$page][$section] as $field) {  
    $sanitizedID = str_replace('[', '', $field['id'] );
    $sanitizedID = str_replace(']', '', $sanitizedID );     
     if (strpos($field['callback'],'header') !== false) { 
         echo '<li class="mashsb-tabs"><a href="#' . $sanitizedID . '">' . $field['title'] .'</a></li>';
     }      
    }
    echo '</ul>';
}


/**
 * Print out the settings fields for a particular settings section
 *
 * Part of the Settings API. Use this in a settings page to output
 * a specific section. Should normally be called by do_settings_sections()
 * rather than directly.
 *
 * @global $wp_settings_fields Storage array of settings fields and their pages/sections
 * @return string
 *
 * @since 2.1.2
 *
 * @param string $page Slug title of the admin page who's settings fields you want to show.
 * @param section $section Slug title of the settings section who's fields you want to show.
 * 
 * Copied from WP Core 4.0 /wp-admin/includes/template.php do_settings_fields()
 * We use our own function to be able to create jQuery tabs with easytabs()
 * 
*  We dont use tables here any longer. Are we stuck in the nineties?
 * @todo Use sprintf to sanitize  $field['id'] instead using str_replace() Should be faster?
 * @todo Push this code into EasyDigitalDownload EDD@github
 * @todo some media queries for better responsibility
 * @todo remove style overflow:auto; and put it in separate class
 */
function edd_do_settings_fields($page, $section) {
    global $wp_settings_fields;
    $header = false;
    $firstHeader = false;
    
    if (!isset($wp_settings_fields[$page][$section]))
        return;
    
    // Check first if any callback header is registered and set $header var to true than
    foreach ((array) $wp_settings_fields[$page][$section] as $field) {
       strpos($field['callback'],'header') !== false ? $header = true : $header = false; 
       if ($header === true)
               break;
    }
    
    foreach ((array) $wp_settings_fields[$page][$section] as $field) {
        
       $sanitizedID = str_replace('[', '', $field['id'] );
       $sanitizedID = str_replace(']', '', $sanitizedID );
       
       // Check if header has been created previously
       if (strpos($field['callback'],'header') !== false && $firstHeader === false) { 
           echo '<div id="' . $sanitizedID . '">'; 
           $firstHeader = true;
       } elseif (strpos($field['callback'],'header') !== false && $firstHeader === true) { 
       // Header has been created previously so we have to close the first opened div
           echo '</div><div id="' . $sanitizedID . '">'; 
       } 
        echo '<div class="row">';
        if (!empty($field['args']['label_for']))
            echo '<label for="' . esc_attr($field['args']['label_for']) . '">' . $field['title'] . '</label>';
        else
            echo '<div class="col-title">' . $field['title'] . '</div>';
        echo '<div style="overflow:auto;">';
        call_user_func($field['callback'], $field['args']);
        echo '</div>';
        echo '</div>';
        
    }
    if ($header === true){
    echo '</div>';
    }
}

/**
 * Options Page
 *
 * Renders the options page contents.
 *
 * @since 1.0
 * @global $edd_options Array of all the EDD Options
 * @return void
 */
function edd_options_page() {
	global $edd_options;

	$active_tab = isset( $_GET[ 'tab' ] ) && array_key_exists( $_GET['tab'], edd_get_settings_tabs() ) ? $_GET[ 'tab' ] : 'general';

	ob_start();
	?>
	<div class="wrap">
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( edd_get_settings_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'settings-updated' => false,
					'tab' => $tab_id
				) );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">';
					echo esc_html( $tab_name );
				echo '</a>';
			}
			?>
		</h2>
		<div id="tab_container" class="tab_container">
                        <?php edd_getTabHeader( 'edd_settings_' . $active_tab, 'edd_settings_' . $active_tab ); ?>   
                    <div class="panel-container"> <!-- new //-->
			<form method="post" action="options.php">
				<?php
				settings_fields( 'edd_settings' );
				edd_do_settings_fields( 'edd_settings_' . $active_tab, 'edd_settings_' . $active_tab );
				?>
				<!--</table>-->
				<?php submit_button(); ?>
			</form>
                    </div> <!-- new //-->
		</div><!-- #tab_container-->
	</div><!-- .wrap -->
	<?php
	echo ob_get_clean();
}
