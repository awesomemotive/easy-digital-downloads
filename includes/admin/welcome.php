<?php
/**
 * Weclome Page Class
 *
 * @package     Easy Digital Downloads
 * @subpackage  Welcome Page
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD Welcome Page Class
 *
 * A general class for About and Credits page.
 *
 * @access      public
 * @since       1.4
 * @return      void
 */
class EDD_Welcome {
	/**
	 * @var string
	 */
	public $minimum_capability = 'manage_options';

	/**
	 * Get things started
	 *
	 * @access      private
	 * @since       1.4
	 * @return      void
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus') );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'welcome'    ) );
	}

	/**
	 * Register dashboard pages.
	 *
	 * Later they are hidden in 'admin_head'.
	 *
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
	 * Hide Individual Dashboard Menus
	 *
	 * @since       1.4
	 * @return      void
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
		/*]]>*/
		</style>
		<?php
	}

	/**
	 * Render About Screen
	 *
	 * @since      1.4
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
				<h3><?php _e( 'Log Viewing Interface', 'edd' ); ?></h3>

				<div class="feature-section">
					<h4><?php printf( __( 'Downloads &rarr; Reports &rarr; <a href="%s">Logs</a>', 'edd' ), admin_url( 'edit.php?post_type=download&page=edd-reports&tab=logs' ) ); ?></h4>
					<p><?php _e( 'You can now view detailed log entries to see exactly what is going on behind the scenes of your store.', 'edd' ); ?></p>

					<h4><?php _e( 'File Download Logs', 'edd' ); ?></h4>
					<p><?php _e( 'See the exact files that are getting downloaded, who is downloading them, and even the IP address they are getting downloaded from.', 'edd' ); ?></p>

					<h4><?php _e( 'Download Sale Logs', 'edd' ); ?></h4>
					<p><?php _e( 'You can see exactly which products have been purchased, when they were purchased, and who purchased them.', 'edd' ); ?></p>

					<h4><?php _e( 'Payment Gateway Error Logs', 'edd' ); ?></h4>
					<p><?php _e( 'Track declined credit cards and other payment failures to help keep an eye on shop activity.', 'edd' ); ?></p>


				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Improved Purchase Summaries', 'edd' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'Receipt Short Code', 'edd' ); ?></h4>
					<p><?php _e( 'The new <code>[edd_receipt]</code> short code will display a detailed break down of customer\'s purchases after completing a payment.', 'edd' ); ?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'System Info', 'edd' ); ?></h3>

				<div class="feature-section">
					<h4><?php printf( __( 'Downloads &rarr; <a href="%s">System Info</a>', 'edd' ), admin_url( 'edit.php?post_type=download&page=edd-system-info' ) ); ?></a></h4>
					<p><?php _e( 'If you are having problems with any aspect, giving the system info download file to support will help us assist you in getting issues resolved.', 'edd' ); ?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Payment Notes', 'edd' ); ?></h3>

				<div class="feature-section">
					<h4><?php _e( 'Track Changes to Purchases', 'edd' ); ?></h4>
					<p><?php _e( 'The new notes feature for payments makes it easy for store managers to leave notes on individual purchases.', 'edd' ); ?></p>

					<p><?php printf( __( 'Simply go to Downloads &rarr; <a href="%s">Payment History</a> and click <em>Edit</em> on any payment. From this screen you can now post notes to payments.', 'edd' ), admin_url( 'edit.php?post_type=download&page=edd-payment-history' ) ); ?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Under the Hood', 'edd' ); ?></h3>

				<div class="feature-section col three-col">
					<div>
						<h4><?php _e( 'Discount Codes', 'edd' ); ?></h4>
						<p><?php _e( 'Discount codes are now stored as a custom post type and will allow for dramatically more powerful coupons in coming versions.', 'edd' ); ?></p>

						<h4><?php _e( 'Plugin Directory Structure', 'edd' ); ?></h4>
						<p><?php _e( 'We have significantly improved the file / folder organized of the plugin, making it easier for developers to get involved in development.', 'edd' ); ?></p>
					</div>

					<div>
						<h4><?php _e( 'File Optimization', 'edd' ); ?></h4>
						<p><?php _e( 'Every file in the plugin has been optimized to help slim down the overall size of Easy Digital Downloads.', 'edd' ); ?></p>

						<h4><?php _e( 'Better AJAX Functionality', 'edd' ); ?></h4>
						<p><?php _e( 'The ajaxed functions, such as payment gateway loading, have been significantly improved to make them faster.', 'edd' ); ?></p>
					</div>

					<div class="last-feature">
						<h4><?php _e( 'WordPress 3.5-ready', 'edd' ); ?></h4>
						<p><?php _e( 'Every aspect of the plugin has been fully tested with WordPress 3.5 to ensure absolute compatibility.', 'edd' ); ?></p>

						<h4><?php _e( 'Retina Ready', 'edd' ); ?></h4>
						<p><?php _e( 'All graphics have been optimized and tested with retina displays.', 'edd' ); ?></p>
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
	 * @since      1.4
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
	 * @since      1.4
	 * @return     string $contributor_list HTML formatted list of contributors.
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
	 * @since      1.4
	 * @return     array $contributors List of contributors.
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

	/**
	 * Sends user to the welcome page on first activation
	 *
	 * @since      1.4
	 * @return     void
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