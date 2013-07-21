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
				<h3>Simple, Beautiful Checkout</h3>

				<div class="feature-section">

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/17checkout.png'; ?>" class="edd-welcome-screenshots"/>

					<h4>Prettier, More Versatile Styles</h4>
					<p>We have completely rewritten the checkout CSS to make it more attractive, more flexible, and more compatible with a wider variety of themes.</p>

					<h4>Better Checkout Layout</h4>
					<p>The position of each field on the checkout has been carefully reconsidered to ensure it is in the proper location so as to best create high conversion rates.</p>

				</div>
			</div>

			<div class="changelog">
				<h3>Item Quantities</h3>

				<div class="feature-section">

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/17quantities.png'; ?>" class="edd-welcome-screenshots"/>

					<h4>Selling Licenses or Multiple Copies at Once?</h4>
					<p>With the new item quantities feature, your customers can choose how many of an item they wish to purchase at one time.</p>

					<h4>Makes Bulk Purchases Simple</h4>
					<p>No longer is purchasing many copies of the same item difficult or tedious. Simply enter the quantity and complete the purchase.</p>


				</div>
			</div>

			<div class="changelog">
				<h3>Direct to PayPal Purchase Buttons</h3>

				<div class="feature-section">

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/17direct.png'; ?>" class="edd-welcome-screenshots"/>

					<h4>Buy Now</h4>
					<p>The Direct purchase option allows you to create Buy Now buttons that take your customers straight to PayPal, completely bypassing the checkout screen.</p>

					<h4>Higher Turnover Rates</h4>
					<p>By sending your customers straight to PayPal, you can dramatically increase your conversion rates by reducing the amount of friction in the purchase process.</p>

				</div>
			</div>

			<div class="changelog">
				<h3>Multiple Discount Codes Per Purchase</h3>

				<div class="feature-section">

					<h4>Offer Bigger, Better Promotions </h4>
					<p>With the Ability to redeem multiple discount codes per purchase, you can give your customers even more incentives to purchase your products.</p>

				</div>
			</div>

			<div class="changelog">
				<h3>Additional Updates</h3>

				<div class="feature-section col three-col">
					<div>
						<h4>Brought Back [download_history]</h4>
						<p>The return of the [downloads_history] short code was one of our most common user requests since it was removed. We listened and it is back!</p>

						<h4>Full Retina Support</h4>
						<p>Every image and icon used in Easy Digital Downloads has been remade with complete support for high resolution / retina displays.</p>
					</div>

					<div>
						<h4>Customer Reports Search</h4>
						<p>Want to find out how much a customer has spent, or how many customers you have with a specific domain\'s email address? Now you can.</p>

						<h4>Better Admin Notifications</h4>
						<p>The admin sale notification emails can now be completely customized in the same way as purchase receipts. Want to know all details of the customer? Now you can.</p>
					</div>

					<div class="last-feature">
						<h4>Settings Import / Export</h4>
						<p>The ability to export and import all store settings makes the process of moving sites from development to live a lot easier.</p>

						<h4>Improved Performance</h4>
						<p>Several key areas (such as Payment History) of Easy Digital Downloads have been dramatically improved in terms of performance to make your site run better and faster.</p>
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