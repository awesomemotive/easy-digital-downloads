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
	 * @since 1.4
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
				<h3><?php _e( 'A Great Checkout Experience', 'edd' );?></h3>

				<div class="feature-section">

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/17checkout.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php _e( 'Simple, Beautiful Checkout', 'edd' );?></h4>
					<p><?php _e( 'We have worked tirelessly to continually improve the checkout experience of Easy Digital Downloads, and with just a few subtle tweaks, we have made the experience in Easy Digital Downloads version 1.8 even better than before.', 'edd' );?></p>

					<h4><?php _e( 'Better Checkout Layout', 'edd' );?></h4>
					<p><?php _e( 'The position of each field on the checkout has been carefully reconsidered to ensure it is in the proper location so as to best create high conversion rates.', 'edd' );?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Cart Saving', 'edd' );?></h3>

				<div class="feature-section">

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/18cart-saving.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php _e( 'Allow Customers to Save Their Carts for Later','edd' );?></h4>
					<p><?php _e( 'With Cart Saving, customers can save their shopping carts and then come back and restore them at a later point.', 'edd' );?></p>

					<h4><?php _e( 'Encourage Customers to Come Back', 'edd' );?></h4>
					<p><?php _e( 'By making it easier for customers to save their cart and return later, you can increase the conversion rate of the customers that need time to think about their purchase.', 'edd' );?></p>


				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Better Purchase Button Colors', 'edd' );?></h3>

				<div class="feature-section">

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/18-button-colors.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php _e( 'Eight Button Colors', 'edd' );?></h4>
					<p><?php _e( 'With eight beautifully button colors to choose from, you will almost certainly find the color to match your site.', 'edd' );?></p>

					<h4><?php _e( 'Simpler; Cleaner', 'edd' );?></h4>
					<p><?php _e( 'Purchase buttons are cleaner, simpler, and just all around better with Easy Digital Downloads 1.8.', 'edd' );?></p>
					<p><?php _e( 'By simplifying one of the most important aspects of your digital store, we ensure better compatibility with more themes and easier customization for advanced users and developers.', 'edd' );?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Better APIs for Developers', 'edd' );?></h3>

				<div class="feature-section">

					<h4><?php _e( 'EDD_Payment_Stats','edd' );?></h4>
					<p><?php _e( 'The new EDD_Payment_Stats class provides a simple way to retrieve earnings and sales stats for the store, or any specific Download product, for any date range. Get sales or earnings for this month, last month, this year, or even any custom date range.', 'edd' );?></p>

					<h4><?php _e( 'EDD_Payments_Query', 'edd' ); ?></h4>
					<p><?php _e( 'Easily retrieve payment data for any Download product or the entire store. EDD_Payments_Query even allows you to pass in any date range to retrieve payments for a specific period. EDD_Payments_Query works nearly identical to WP_Query, so it is simple and familiar.', 'edd' ); ?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Additional Updates', 'edd' );?></h3>

				<div class="feature-section col three-col">
					<div>
						<h4><?php _e( 'Retina Ready Checkout', 'edd' );?></h4>
						<p><?php _e( 'Every icon and image asset used by the Easy Digital Downloads checkout is now fully retina ready to ensure your most important page always looks great.', 'edd' );?></p>

						<h4><?php _e( 'Improved Settings API', 'edd' );?></h4>
						<p><?php _e( 'The EDD settings API has been dramatically simplified to be more performant, provide better filters, and even support custom settings tabs.', 'edd' );?></p>
					</div>

					<div>
						<h4><?php _e( 'Live Dashboard Updates', 'edd' );?></h4>
						<p><?php _e( 'The Dashboard summary widget now updates live with the WP Heartbeat API, meaning you can literally watch your stats update live as sales come in.', 'edd' );?></p>

						<h4><?php _e( 'Category Filters for Downloads Reports', 'edd' );?></h4>
						<p><?php _e( 'The Downloads Reports view now supports filtering Downloads by category, making it easier to see earnings and sales based on product categories.', 'edd' );?></p>
					</div>

					<div class="last-feature">
						<h4><?php _e( 'Tools Menu', 'edd' );?></h4>
						<p><?php _e( 'A new Tools submenu has been added to the main Downloads menu that houses settings import / export, as well as other utilities added by extensions.' ,'edd' );?></p>

						<h4><?php _e( 'Bulk Payment History Update','edd' );?></h4>
						<p><?php _e( 'The bulk update options for Payments have been updated to include all payment status options, making it easier to manage payment updates in bulk.', 'edd' );?></p>
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
