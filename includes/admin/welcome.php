<?php
/**
 * Weclome Page Class
 *
 * @package     EDD
 * @subpackage  Admin/Welcome
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Welcome Class
 *
 * A general class for About and Credits page.
 *
 * @since 1.4
 */
class EDD_Welcome {
	/**
	 * @var string The capability users should have to view the page
	 */
	public $minimum_capability = 'manage_options';

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since 1.4
	 * @return void
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus') );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'welcome'    ) );
	}

	/**
	 * Register the Dashboard Pages which are later hidden but these pages
	 * are used to render the Welcome and Credits pages.
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function admin_menus() {
		// About Page
		add_dashboard_page(
			__( 'Welcome to Easy Digital Downloads', 'edd' ),
			__( 'Welcome to Easy Digital Downloads', 'edd' ),
			$this->minimum_capability,
			'edd-about',
			array( $this, 'about_screen' )
		);

		// Credits Page
		add_dashboard_page(
			__( 'Welcome to Easy Digital Downloads', 'edd' ),
			__( 'Welcome to Easy Digital Downloads', 'edd' ),
			$this->minimum_capability,
			'edd-credits',
			array( $this, 'credits_screen' )
		);
	}

	/**
	 * Hide Individual Dashboard Pages
	 *
	 * @access public
	 * @since 1.4
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
			padding-top: 150px;
			height: 52px;
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

		.edd-welcome-screenshots {
			float: right;
			margin-left: 10px!important;
		}
		/*]]>*/
		</style>
		<?php
	}

	/**
	 * Render About Screen
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function about_screen() {
		list( $display_version ) = explode( '-', EDD_VERSION );
		?>
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Easy Digital Downloads %s', 'edd' ), $display_version ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Easy Digital Downloads %s is ready to make your online store faster, safer and better!', 'edd' ), $display_version ); ?></div>
			<div class="edd-badge"><?php printf( __( 'Version %s', 'edd' ), $display_version ); ?></div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-about' ), 'index.php' ) ) ); ?>">
					<?php _e( "What's New", 'edd' ); ?>
				</a><a class="nav-tab" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-credits' ), 'index.php' ) ) ); ?>">
					<?php _e( 'Credits', 'edd' ); ?>
				</a>
			</h2>

			<div class="changelog">
				<h3><?php _e( 'Bundled Products', 'edd' ); ?></h3>

				<div class="feature-section">

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/bundles.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php _e( 'Combine Multiple Products into Bundles', 'edd' ); ?></h4>
					<p><?php _e( 'A bundled product is a group of other Downloads in your store that are purchased as a single item, usually at a discount.', 'edd' ); ?></p>

					<h4><?php _e( 'Simplify Your Admin Tasks', 'edd' ); ?></h4>
					<p><?php _e( 'Prior to Bundles, you were forced to create a new product and then manually add all of the necessary files from the other Downloads to it. No longer! Bundled products automatically grab the file downloads from products included in the bundle.', 'edd' ); ?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Improved Order Details Interface', 'edd' ); ?></h3>

				<div class="feature-section">

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/order-details.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php _e( 'Better Order Detail Display', 'edd' ); ?></h4>
					<p><?php _e( 'By doing away with the View Order Details pop up, we\'ve made it possible to present the order details in a much more clear manner.', 'edd' ); ?></p>

					<h4><?php _e( 'More Developer Friendly', 'edd' ); ?></h4>
					<p><?php _e( 'The new Order Details display is much more developer friendly with several hooks that make it possible to easily add additional data about the order via extensions.', 'edd' ); ?></p>


				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Multiple Tax Rates', 'edd' ); ?></h3>

				<div class="feature-section">

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/tax-rates.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php _e( 'Define Rates for Specific Countries and States / Provinces', 'edd' ); ?></h4>
					<p><?php _e( 'You can now setup tax rates for specific countries and states / provinces in those countries.', 'edd' );  ?></p>

					<h4><?php _e( 'No More Local-Tax Opt-In', 'edd' ); ?></h4>
					<p><?php _e( 'Taxes are now automatically calculated based on the customer\'s billing address. If you have taxes enabled, billing detail fields will be automatically added to the checkout form.', 'edd' );  ?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Improved Developer Documentation', 'edd' ); ?></h3>

				<div class="feature-section">

					<h4><?php _e( 'Code Reference', 'edd' ); ?></h4>
					<p><?php _e( 'A complete code reference has been made available for developers at <a href="https://easydigitaldownloads.com/codex/index.html">/codex</a> on the Easy Digital Downloads website.', 'edd' );  ?></p>

					<h4><?php _e( 'Action Hooks', 'edd' ); ?></h4>
					<p><?php _e( 'Along with the complete code reference, we have been working to bring the <a href="https://easydigitaldownloads.com/docs/section/actions/">Actions reference</a> up to date with all of the action hooks available in Easy Digital Downloads.', 'edd' );  ?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Discounts API Endpoint', 'edd' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'Retrieve Available Discounts', 'edd' ); ?></h4>
					<p><?php _e( 'The EDD RESTful API now includes a /discounts endpoint that can be used for retrieving details about the available discounts in your store. Want to create a feed on another website of the discounts available? You can now do that.', 'edd' );  ?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Additional Updates', 'edd' ); ?></h3>

				<div class="feature-section col three-col">
					<div>
						<h4><?php _e( 'EDD_Cron Class', 'edd' ); ?></h4>
						<p><?php printf( __( 'The new %EDD_Cron class%s provides a simple way to hook into routinely scheduled events in Easy Digital Downloads.', 'edd' ), '<a href="https://github.com/easydigitaldownloads/Easy-Digital-Downloads/blob/master/includes/class-edd-cron.php" target="_blank">', '</a>' ); ?></p>

						<h4><?php _e( 'Improved Country and State / Province Fields ', 'edd' ); ?></h4>
						<p><?php _e( 'We have added drop down fields for the states / provinces of 12 additional countries, providing customers in those countries a much better checkout experience.', 'edd' ); ?></p>
					</div>

					<div>
						<h4><?php _e( 'More Reliable File Download Methods', 'edd' ); ?></h4>
						<p><?php _e( 'EDD now supports delivering file downloads via X-Sendfile, X-Lighttpd-Sendfile, and X-Accel-Redirect depending on your server config.', 'edd' ); ?></p>

						<h4><?php _e( 'Lookup Previous Guest Purchases on User Registration', 'edd' ); ?></h4>
						<p><?php _e( 'Anytime a new user is added, EDD will look up any purchases the user may have made as a guest and attribute them to the newly created account.', 'edd' ); ?></p>
					</div>

					<div class="last-feature">
						<h4><?php _e( 'Itemized PayPal Purchases', 'edd' ); ?></h4>
						<p><?php _e( 'Purchases made through PayPal will now show itemized details in the PayPal order summary box.', 'edd' ); ?></p>

						<h4><?php _e( 'SKU Support', 'edd' ); ?></h4>
						<p><?php _e( 'Adding product SKUs to Downloads is now supported and can be enabled in Downloads > Settings > Misc.', 'edd' ); ?></p>
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
	 * Render Credits Screen
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function credits_screen() {
		list( $display_version ) = explode( '-', EDD_VERSION );
		?>
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Easy Digital Downloads %s', 'edd' ), $display_version ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Easy Digital Downloads %s is ready to make your online store faster, safer and better!', 'edd' ), $display_version ); ?></div>
			<div class="edd-badge"><?php printf( __( 'Version %s', 'edd' ), $display_version ); ?></div>

			<h2 class="nav-tab-wrapper">
				<a class="nav-tab" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-about' ), 'index.php' ) ) ); ?>">
					<?php _e( "What's New", 'edd' ); ?>
				</a><a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-credits' ), 'index.php' ) ) ); ?>">
					<?php _e( 'Credits', 'edd' ); ?>
				</a>
			</h2>

			<p class="about-description"><?php _e( 'Easy Digital Downloads is created by a worldwide team of developers who aim to provide the #1 eCommerce platform for selling digital goods through WordPress.', 'edd' ); ?></p>

			<?php echo $this->contributors(); ?>
		</div>
		<?php
	}


	/**
	 * Render Contributors List
	 *
	 * @since 1.4
	 * @uses EDD_Welcome::get_contributors()
	 * @return string $contributor_list HTML formatted list of all the contributors for EDD
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
	 * @access public
	 * @since 1.4
	 * @return array $contributors List of contributors
	 */
	public function get_contributors() {
		$contributors = get_transient( 'edd_contributors' );

		if ( false !== $contributors )
			return $contributors;

		$response = wp_remote_get( 'https://api.github.com/repos/easydigitaldownloads/Easy-Digital-Downloads/contributors', array( 'sslverify' => false ) );

		if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) )
			return array();

		$contributors = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! is_array( $contributors ) )
			return array();

		set_transient( 'edd_contributors', $contributors, 3600 );

		return $contributors;
	}

	/**
	 * Sends user to the Welcome page on first activation of EDD as well as each
	 * time EDD is upgraded to a new version
	 *
	 * @access public
	 * @since 1.4
	 * @global $edd_options Array of all the EDD Options
	 * @return void
	 */
	public function welcome() {
		global $edd_options;

		// Bail if no activation redirect
		if ( ! get_transient( '_edd_activation_redirect' ) )
			return;

		// Delete the redirect transient
		delete_transient( '_edd_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
			return;

		wp_safe_redirect( admin_url( 'index.php?page=edd-about' ) ); exit;
	}
}
new EDD_Welcome();