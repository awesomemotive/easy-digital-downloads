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

		// Badge for welcome page
		$badge_url = EDD_PLUGIN_URL . 'assets/images/edd-badge.png';
		?>

		<style type="text/css" media="screen">
		/*<![CDATA[*/
		.edd-badge {
			padding-top: 142px;
			height: 55px;
			width: 185px;
			color: #666;
			font-weight: bold;
			font-size: 14px;
			text-align: center;
			text-shadow: 0 1px 0 rgba(255, 255, 255, 0.8);
			margin: 0 -5px;
			background: url('<?php echo $badge_url; ?>') no-repeat;
		}

		.about-wrap .edd-badge {
			position: absolute;
			top: 0;
			right: 0;
		}
		/*]]>*/
		</style>

		<?php
	}

	/**
	 * Output about screen.
	 * 
	 * @since 1.4
	 */
	public function about_screen() {

		list( $display_version ) = explode( '-', EDD_VERSION ); ?>

		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Easy Digital Downloads %s', 'edd' ), $display_version ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Easy Digital Downloads %s is ready to make your online shop faster, safer and better!', 'edd' ), $display_version ); ?></div>
			<div class="edd-badge"><?php printf( __( 'Version %s' ), $display_version ); ?></div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-about' ), 'index.php' ) ) ); ?>">
					<?php _e( 'What&#8217;s New', 'edd' ); ?>
				</a><a class="nav-tab" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-credits' ), 'index.php' ) ) ); ?>">
					<?php _e( 'Credits', 'edd' ); ?>
				</a>
			</h2>

			<div class="changelog">
				<h3><?php _e( 'In-depth User Profiles', 'edd' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'User Details', 'edd' ); ?></h4>
					<p><?php _e( 'Forum profiles include the details of your forum activity, including your topics and replies, subscriptions, and favorites.', 'edd' ); ?></p>

					<h4><?php _e( 'Easy Updating', 'edd' ); ?></h4>
					<p><?php _e( 'You can easily update your profile without leaving bbPress.', 'edd' ); ?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Theme Compatability', 'edd' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'Twenty Twelve', 'edd' ); ?></h4>
					<p><?php _e( 'Updated default templates are now Twenty Twelve compatible, and we refreshed our CSS to better integrate with other popular themes, too.', 'edd' ); ?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Improved User Management', 'edd' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'Dynamic User Roles and Capabilities', 'edd' ); ?></h4>
					<p><?php _e( 'bbPress now includes some fancy user-roles with smart default capabilities to help you manage your forums. New roles include Key Master (for complete administrative access), Moderator, and Participant for regular forum users.', 'edd' ); ?></p>

					<h4><?php _e( 'Manage Forum Users from WordPress', 'edd' ); ?></h4>
					<p><?php _e( 'You can assign Forums roles to users individually, or bulk update them from the WordPress Users page. Users automatically start out as forum participants.', 'edd' ); ?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Better BuddyPress Integration', 'edd' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'Use bbPress for Your BuddyPress Group Forums', 'edd' ); ?></h4>
					<p><?php _e( 'You can now use bbPress to manage your BuddyPress Group Forums, allowing for seamless integration and improved plugin performance. Plugins developed for bbPress can now be extended to improve the BuddyPress Group Forums experience.', 'edd' ); ?></p>

					<h4><?php _e( 'Activity Stream Syncing', 'edd' ); ?></h4>
					<p><?php _e( 'bbPress now keeps track of changes to topics and replies and keeps their corresponding BuddyPress Activity Stream updates synced.', 'edd' ); ?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Under the Hood', 'edd' ); ?></h3>

				<div class="feature-section col three-col">
					<div>
						<h4><?php _e( 'Template Logic', 'edd' ); ?></h4>
						<p><?php _e( 'New functions and template stacks are in place to help plugin developers extend bbPress further.', 'edd' ); ?></p> 

						<h4><?php _e( 'Plugin Directory Structure', 'edd' ); ?></h4>
						<p><?php _e( 'We simplified the bbPress plugin directory structure, making it easier for plugin developers to find the relevant code.', 'edd' ); ?></p>
					</div>

					<div>
						<h4><?php _e( 'Autocomplete', 'edd' ); ?></h4>
						<p><?php _e( 'In WordPress Admin, you now select a parent forum or topic via autocomplete rather than a dropdown.', 'edd' ); ?></p>

						<h4><?php _e( 'Fancy Editor Support', 'edd' ); ?></h4>
						<p><?php _e( 'We improved our support of the Fancy Editor, giving forum users a better experience.', 'edd' ); ?></p>
					</div>

					<div class="last-feature">
						<h4><?php _e( 'WordPress 3.5-ready', 'edd' ); ?></h4>
						<p><?php _e( 'bbPress 2.2 has been thoroughly tested against the ongoing development of WordPress 3.5.', 'edd' ); ?></p>
					</div>
				</div>
			</div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'download', 'page' => 'edd-settings' ), 'edit.php' ) ) ); ?>"><?php _e( 'Go to Easy Digital Downloads Settings', 'edd' ); ?></a>
			</div>
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
			<h1><?php printf( __( 'Welcome to Easy Digital Downloads %s', 'edd' ), $display_version ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Easy Digital Downloads %s is ready to make your online shop faster, safer and better!', 'edd' ), $display_version ); ?></div>
			<div class="edd-badge"><?php printf( __( 'Version %s' ), $display_version ); ?></div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-about' ), 'index.php' ) ) ); ?>">
					<?php _e( 'What&#8217;s New', 'edd' ); ?>
				</a><a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-credits' ), 'index.php' ) ) ); ?>">
					<?php _e( 'Credits', 'edd' ); ?>
				</a>
			</h2>

			<p class="about-description"><?php _e( 'Easy Digital Downloads is created by a worldwide team of developers who aim to provide the #1 eCommerce platform for WordPress.', 'edd' ); ?></p>

			<?php echo $this->contributors(); ?>
		</div>

		<?php
	}

	/**
	 * Render contributors list.
	 *
	 * @since 1.4
	 * 
	 * @return string HTML formatted list of contributors.
	 */
	public function contributors() {
		$contributors = $this->get_contributors();

		if ( empty( $contributors ) )
			return '';
 
		$contributor_list = '<ul class="wp-people-group">';
 
		foreach ( $contributors as $contributor ) {
			$contributor_list .= '<li class="wp-person">';
			$contributor_list .= sprintf( '<a href="%s" title="%s">',
				esc_url( 'https://github.com/' . $contributor->login ),
				esc_html( sprintf( __( 'View %s', 'edd' ), $contributor->login ) )
			);
			$contributor_list .= sprintf( '<img src="%s" width="64" height="64" class="gravatar" alt="%s" />', esc_url( $contributor->avatar_url ), esc_html( $contributor->login ) );
			$contributor_list .= '</a>';
			$contributor_list .= sprintf( '<a class="web" href="%s">%s</a>', esc_url( 'https://github.com/' . $contributor->login ), esc_html( $contributor->login ) );
			$contributor_list .= '</a>';
			$contributor_list .= '</li>';
		}
 
		$contributor_list .= '</ul>';
 
		return $contributor_list;
	}

	/**
	 * Retreive list of contributors from GitHub.
	 *
	 * @since 1.4
	 * 
	 * @return array List of contributors.
	 */
	public function get_contributors() {
		$contributors = get_transient( 'edd_contributors' );

		if ( false !== $contributors )
			return $contributors;

		$response = wp_remote_get( 'https://api.github.com/repos/pippinsplugins/Easy-Digital-Downloads/contributors', array( 'sslverify' => false ) );

		if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) )
			return array();

		$contributors = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! is_array( $contributors ) )
			return array();

		set_transient( 'edd_contributors', $contributors, 3600 );

		return $contributors;
	}
}
new EDD_Welcome();