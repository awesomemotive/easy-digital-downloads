<?php

/**
 * WP-Simple-Settings-Framework
 *
 * Copyright (c) 2012 Matt Gates.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of the
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @subpackage  WP-Simple-Settings-Framework
 * @copyright   2012 Matt Gates.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://mgates.me
 * @version     1.1
 * @author      Matt Gates <info@mgates.me>
 * @package     WordPress
 */


if ( ! class_exists( 'SF_Settings_API' ) ) {

	class SF_Settings_API
	{

		private $data = array();

		/**
		 * Init
		 *
		 * @param string $id
		 * @param string $title
		 * @param string $menu  (optional)
		 * @param string $file
		 */
		public function __construct( $id, $title, $menu = '', $file )
		{
			$this->assets_url = trailingslashit( plugins_url( 'assets/' , dirname( __FILE__ ) ) );
			$this->id = $id;
			$this->title = $title;
			$this->menu = empty( $menu ) ? 'plugins.php' : $menu;

			$this->file = $file;

			$this->includes();
			$this->actions();
		}


		// ==================================================================
		//
		// Getter and setter.
		//
		// ------------------------------------------------------------------

		/**
		 * Setter
		 *
		 * @param unknown $name
		 * @param unknown $value
		 */
		public function __set( $name, $value )
		{
			if ( isset ( $this->data[$name] ) && is_array( $this->data[$name] ) ) {
				$this->data[$name] = array_merge( $this->data[$name], $value );
			} else {
				$this->data[$name] = $value;
			}
		}


		/**
		 * Getter
		 *
		 * @param unknown $name
		 * @return unknown
		 */
		public function __get( $name )
		{
			if ( array_key_exists( $name, $this->data ) ) {
				return $this->data[$name];
			}
			return null;
		}


		/**
		 * Isset
		 *
		 * @param unknown $name
		 * @return unknown
		 */
		public function __isset( $name )
		{
			return isset( $this->data[$name] );
		}


		/**
		 * Unset
		 *
		 * @param unknown $name
		 */
		public function __unset( $name )
		{
			unset( $this->data[$name] );
		}


		/**
		 * Add a "Settings" link to the plugins.php page
		 *
		 * @param array $links
		 * @param array $file
		 * @return array
		 */
		public function add_settings_link( $links, $file )
		{
			$this_plugin = plugin_basename( $this->file );
			$page = strpos( $this->menu, '.php' ) ? $this->menu : 'admin.php';
			if ( $file == $this_plugin ) {
				$settings_link = '<a href="'.$page.'?page='.$this->id.'">' . __( 'Settings', 'geczy' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
			return $links;
		}


		// ==================================================================
		//
		// Begin initialization.
		//
		// ------------------------------------------------------------------

		/**
		 * Core files
		 */
		private function includes()
		{
			require_once dirname( __FILE__ ) . '/sf-class-sanitize.php';
			require_once dirname( __FILE__ ) . '/sf-class-format-options.php';
			new SF_Sanitize;
		}


		/**
		 * Hooks
		 */
		private function actions()
		{
			add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );
			add_action( 'admin_init', array( &$this, 'register_options' ) );
			add_action( 'admin_menu', array( &$this, 'create_menu' ) );
			add_filter( 'plugin_action_links', array( &$this, 'add_settings_link' ), 10, 2 );
		}


		/**
		 * Admin scripts and styles
		 */
		public function admin_enqueue_scripts()
		{
			wp_register_script( 'bootstrap-tooltip' , $this->assets_url . 'js/bootstrap-tooltip.js' ,  array( 'jquery' ), '1.0' );
			wp_register_script( 'select2' , $this->assets_url . 'js/select2/select2.min.js' ,  array( 'jquery' ), '1.0' );
			wp_register_script( 'sf-scripts' , $this->assets_url . 'js/sf-jquery.js' ,  array( 'jquery' ), '1.0' );
			wp_register_style( 'select2' , $this->assets_url . 'js/select2/select2.css' );
			wp_register_style( 'sf-styles' , $this->assets_url . 'css/sf-styles.css' );
		}


		/**
		 * Admin scripts and styles
		 */
		public function admin_print_scripts()
		{
			wp_enqueue_script( 'bootstrap-tooltip' );
			wp_enqueue_script( 'select2' );
			wp_enqueue_script( 'sf-scripts' );
			wp_enqueue_style( 'select2' );
			wp_enqueue_style( 'sf-styles' );
		}


		/**
		 * Register setting
		 */
		public function register_options()
		{
			register_setting( $this->id . '_options_nonce', $this->id . '_options', array( &$this, 'validate_options' ) );
		}


		/**
		 * Create menu
		 */
		public function create_menu()
		{
			$page = add_submenu_page( $this->menu, $this->title, $this->title, 'manage_options', $this->id, array( &$this, 'init_settings_page' ) );
			add_action( 'admin_print_scripts-' . $page, array( &$this, 'admin_print_scripts' ) );
		}


		/**
		 * Parse options into tabbed organization
		 *
		 * @return array
		 */
		private function parse_options()
		{
			$options = $this->options;

			foreach ( $options as $option ) {

				if ( $option['type'] == 'heading' ) {
					$tab_name = sanitize_title( $option['name'] );
					$this->tab_headers = array( $tab_name => $option['name'] );

					continue;
				}

				$option['tab'] = $tab_name;
				$tabs[$tab_name][] = $option;

			}

			$this->tabs = $tabs;

			return $tabs;
		}


		/**
		 * Load the options array from a file
		 *
		 * @param string $option_file
		 */
		public function load_options( $option_file )
		{
			if ( !empty( $this->options ) ) return;

			if ( file_exists( $option_file ) ) {
				require $option_file;
				$this->options = apply_filters( $this->id . '_options', $options );
				$this->parse_options();

				$this->current_options = get_option( $this->id . '_options' );

				/* If the option has no saved data, load the defaults. */
				/* @TODO: Can prob add this to the activation hook. */
				if ( !$this->current_options ) {
					$this->set_defaults();
				}
			} else {
				wp_die( __( 'Could not load settings at: ', 'geczy' ) . '<br/><code>' . $option_file . '</code>', __( 'Error - WP Settings Framework', 'geczy' ) );
			}
		}


		/**
		 * Sanitize and validate post fields
		 *
		 * @param unknown $input
		 * @return unknown
		 */
		public function validate_options( $input )
		{
			if ( !isset( $_POST['update'] ) )
				return $this->get_defaults();

			$clean = array();
			$options = $this->options;

			$tabname = $_POST['currentTab'];

			foreach ( $this->current_options as $id => $value ) :
				$clean[$id] = $value;
			endforeach;

			foreach ( $this->tabs[$tabname] as $option ) :

				if ( ! isset( $option['id'] ) )
					continue;

				if ( ! isset( $option['type'] ) )
					continue;

				$id = preg_replace( '/[^a-zA-Z0-9._\-]/', '', strtolower( $option['id'] ) );

			// Set checkbox to false if it wasn't sent in the $_POST
			if ( 'checkbox' == $option['type'] && ! isset( $input[$id] ) )
				$input[$id] = 0;

			// For a value to be submitted to database it must pass through a sanitization filter
			if ( has_filter( 'geczy_sanitize_' . $option['type'] ) ) {
				$clean[$id] = apply_filters( 'geczy_sanitize_' . $option['type'], $input[$id], $option );
			}

			endforeach;

			do_action( $this->id . '_options_updated', $clean );
			add_settings_error( $this->id, 'save_options', __( 'Settings saved.', 'geczy' ), 'updated' );

			return apply_filters( $this->id . '_options_on_update', $clean );
		}


		/**
		 * Create default options
		 */
		private function set_defaults()
		{
			$options = $this->get_defaults();
			update_option( $this->id . '_options', $options );
		}


		/**
		 * Retrieve default options
		 *
		 * @return array
		 */
		private function get_defaults()
		{
			$output = array();
			$config = $this->options;

			foreach ( $config as $option ) {
				if ( ! isset( $option['id'] ) || ! isset( $option['std'] ) || ! isset( $option['type'] ) )
					continue;

				if ( has_filter( 'geczy_sanitize_' . $option['type'] ) ) {
					$output[$option['id']] = apply_filters( 'geczy_sanitize_' . $option['type'], $option['std'], $option );
				}
			}

			return $output;
		}


		/**
		 * HTML header
		 */
		private function template_header()
		{
?>
			<div class="wrap">
				<?php screen_icon(); ?><h2><?php echo $this->title; ?></h2>

				<h2 class="nav-tab-wrapper">
					<?php echo $this->display_tabs(); ?>
				</h2><?php

			if ( !empty ( $_REQUEST['settings-updated'] ) )
				settings_errors();

		}


		/**
		 * HTML body
		 *
		 * @return unknown
		 */
		private function template_body()
		{

			if ( empty( $this->options ) ) return false;

			$options = $this->options;
			$tabs = $this->get_tabs();
			$tabname = !empty ( $_GET['tab'] ) ? $_GET['tab'] : $tabs[0]['slug'];

			$options = apply_filters( $this->id . '_options_tab-' . $tabname, $this->tabs[$tabname] ); ?>

			<form method="post" action="options.php">
				<?php settings_fields( $this->id . '_options_nonce' ); ?>
				<table class="form-table">

				<?php
			foreach ( $options as $value ) :
				SF_Format_Options::settings_options_format( $value );
			endforeach;

			do_action( $this->id . '_options_tab-' . $tabname );
?>

				</table>

				<p class="submit">
					<input type="hidden" name="currentTab" value="<?php echo $tabname; ?>">
					<input type="submit" name="update" class="button-primary" value="<?php echo sprintf( __( 'Save %s changes', 'geczy' ), $this->tab_headers[$tabname] ); ?>" />
				</p>
			</form> <?php

		}


		/**
		 * HTML footer
		 */
		private function template_footer()
		{
			echo '</div>';
		}


		/**
		 * Create the settings page
		 */
		public function init_settings_page()
		{

			$this->template_header();
			$this->template_body();
			$this->template_footer();

		}


		/**
		 * Retrieve tabs
		 *
		 * @return array
		 */
		private function get_tabs()
		{
			$tabs = array();
			foreach ( $this->options as $option ) {

				if ( $option['type'] != 'heading' )
					continue;

				$option['slug'] = sanitize_title( $option['name'] );
				unset( $option['type'] );

				$tabs[] = $option;
			}
			return $tabs;
		}


		/**
		 * Heading for navigation
		 *
		 * @return string
		 */
		private function display_tabs()
		{
			$tabs = $this->get_tabs();
			$tabname = !empty ( $_GET['tab'] ) ? $_GET['tab'] : $tabs[0]['slug'];
			$menu = '';

			foreach ( $tabs as $tab ) {
				$class = $tabname == $tab['slug'] ? 'nav-tab-active' : '';

				$fields = array(
					'page' => $this->id,
					'tab'  => $tab['slug'],
				);

				$query = http_build_query(array_merge($_GET, $fields));
				$menu .= sprintf( '<a id="%s-tab" class="nav-tab %s" title="%s" href="?%s">%s</a>', $tab['slug'], $class, $tab['name'], $query, esc_html( $tab['name'] ) );
			}

			return $menu;
		}


		/**
		 * Update an option
		 *
		 * @param string $name
		 * @param string $value
		 * @return bool
		 */
		public function update_option( $name, $value )
		{
			// Overwrite the key/value pair
			$this->current_options = array( $name => $value ) + $this->current_options;

			return update_option( $this->id .'_options', $this->current_options );
		}


		/**
		 * Get an option
		 *
		 * @param string $name
		 * @param string $default (optional)
		 * @return bool
		 */
		public function get_option( $name, $default = false )
		{
			return isset( $this->current_options[$name] ) ? $this->current_options[$name] : $default;
		}


	}


}
