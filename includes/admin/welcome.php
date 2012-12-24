<?php
/**
 * Weclome Page Class
 *
 * @package     Easy Digital Downloads
 * @subpackage  Dashboard Widgets
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * A general class for About and Credits page.
 *
 * @access public
 * @since  1.4
 * @return void
 */
class EDD_Welcome {

	public $minimum_capability = 'manage_options';

	/**
	 * Class loader.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus') );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
	}

	/**
	 * Register dashboard pages.
	 *
	 * Later they are removed in 'admin_head'.
	 *
	 * @since 1.4
	 * 
	 * @return void
	 */
	public function admin_menus() {

		// About
		add_dashboard_page(
			__( 'Welcome to Easy Digital Downloads', 'edd' ),
			__( 'Welcome to Easy Digital Downloads', 'edd' ),
			$this->minimum_capability,
			'edd-about',
			array( $this, 'about_screen' )
		);

		// Credits
		add_dashboard_page(
			__( 'Welcome to Easy Digital Downloads', 'edd' ),
			__( 'Welcome to Easy Digital Downloads', 'edd' ),
			$this->minimum_capability,
			'edd-credits',
			array( $this, 'credits_screen' )
		);
	}

	/**
	 * Remove individual dashboard menus.
	 * 
	 * @return void
	 */
	public function admin_head() {
		remove_submenu_page( 'index.php', 'edd-about' );
		remove_submenu_page( 'index.php', 'edd-credits' );
	}

	/**
	 * Output about screen.
	 * 
	 * @since 1.4
	 */
	public function about_screen() {

		list( $display_version ) = explode( '-', EDD_VERSION ); ?>

		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to EDD %s', 'edd' ), $display_version ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! EDD %s is ready to make your online shop a safer, faster, and better!', 'edd' ), $display_version ); ?></div>
			<div class="wp-badge"><?php printf( __( 'Version %s' ), $display_version ); ?></div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-about' ), 'index.php' ) ) ); ?>">
					<?php _e( 'What&#8217;s New', 'edd' ); ?>
				</a><a class="nav-tab" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-credits' ), 'index.php' ) ) ); ?>">
					<?php _e( 'Credits', 'edd' ); ?>
				</a>
			</h2>
		</div>

		<?php
	}

	/**
	 * Output credits screen.
	 * 
	 * @since 1.4
	 */
	public function credits_screen() {

		list( $display_version ) = explode( '-', EDD_VERSION ); ?>

		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to EDD %s', 'edd' ), $display_version ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! EDD %s is ready to make your online shop a safer, faster, and better!', 'edd' ), $display_version ); ?></div>
			<div class="wp-badge"><?php printf( __( 'Version %s' ), $display_version ); ?></div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-about' ), 'index.php' ) ) ); ?>">
					<?php _e( 'What&#8217;s New', 'edd' ); ?>
				</a><a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-credits' ), 'index.php' ) ) ); ?>">
					<?php _e( 'Credits', 'edd' ); ?>
				</a>
			</h2>
		</div>

		<?php
	}

	/**
	 * Retreive list of contributors.
	 *
	 * @since 1.4
	 * 
	 * @return array List of contributors.
	 */
	public function contributors() {
		$contributors = get_transient( 'edd_contributors' );

		if ( false !== $contributors )
			return $contributors;

		$response = wp_remote_get( 'https://api.github.com/repos/pippinsplugins/Easy-Digital-Downloads/contributors' );

		if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) )
			return array();

		$contributors = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! is_array( $contributors ) );
			return array();

		set_transient( 'edd_contributors', $contributors, DAY_IN_SECONDS );

		return $contributors;
	}
}
new EDD_Welcome();