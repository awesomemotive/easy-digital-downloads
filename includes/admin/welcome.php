<?php
/**
 * Weclome Page Class
 *
 * @package     EDD
 * @subpackage  Admin/Welcome
 * @copyright   Copyright (c) 2014, Pippin Williamson
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

		// Getting Started Page
		add_dashboard_page(
			__( 'Getting started with Easy Digital Downloads', 'edd' ),
			__( 'Getting started with Easy Digital Downloads', 'edd' ),
			$this->minimum_capability,
			'edd-getting-started',
			array( $this, 'getting_started_screen' )
		);

		// Credits Page
		add_dashboard_page(
			__( 'The people that build Easy Digital Downloads', 'edd' ),
			__( 'The people that build Easy Digital Downloads', 'edd' ),
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
		remove_submenu_page( 'index.php', 'edd-getting-started' );
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

		.about-wrap .feature-section {
			margin-top: 20px;
		}

		/*]]>*/
		</style>
		<?php
	}

	/**
	 * Navigation tabs
	 *
	 * @access public
	 * @since 1.9
	 * @return void
	 */
	public function tabs() {
		$selected = isset( $_GET['page'] ) ? $_GET['page'] : 'edd-about';
		?>
		<h2 class="nav-tab-wrapper">
			<a class="nav-tab <?php echo $selected == 'edd-about' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-about' ), 'index.php' ) ) ); ?>">
				<?php _e( "What's New", 'edd' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'edd-getting-started' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-getting-started' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Getting Started', 'edd' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'edd-credits' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-credits' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Credits', 'edd' ); ?>
			</a>
		</h2>
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
			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Easy Digital Downloads %s is ready to make your online store faster, safer, and better!', 'edd' ), $display_version ); ?></div>
			<div class="edd-badge"><?php printf( __( 'Version %s', 'edd' ), $display_version ); ?></div>

			<?php $this->tabs(); ?>

			<div class="changelog">
				<h3><?php _e( 'Sequential Order Numbers', 'edd' );?></h3>

				<div class="feature-section">

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/20-sequential.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php _e( 'Prefix, Postfix, and Starting Number', 'edd' );?></h4>
					<p><?php printf( __( 'Sequential order numbers are now supported out of the box. Simply go to <a href="%s">Settings &rarr; Misc</a> to enable them. The starting number, prefix, and postfix for order numbers can all be easily configured.', 'edd' ), admin_url( 'edit.php?post_type=download&page=edd-settings&tab=misc' ) ); ?></p>

					<h4><?php _e( 'Upgrade Routine', 'edd' );?></h4>
					<p><?php _e( 'Sequential order numbers are important for some and even mandatory for others, so we want to ensure that all users can make use of them. For this reason, we have provided a one-click upgrade routine that will update all previous purchase records with sequential order numbers matching your settings.', 'edd' );?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Improved Checkout Experience', 'edd' );?></h3>

				<div class="feature-section">

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/20-register-login.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php _e( 'Registration / Login Forms', 'edd' );?></h4>
					<p><?php _e( 'The registration and login form options for the checkout form have been further refined in version 2.0. They now include granular control that let you determine exactly which forms are displayed. You can have just a login form, just a registration form, both forms, or neither. The choice is yours.', 'edd' );?></p>

					<h4><?php _e( 'Live Item Quantity Updates', 'edd' );?></h4>
					<p><?php _e( 'Cart item quantities are now updated on the fly when customers adjust them, meaning customers no longer need to click Update Cart and wait for the page to reload to see their new purchase total. This creates a more fluid and rapid checkout experience.', 'edd' );?></p>

					<h4><?php _e( '100% Discounts and Credit Cards', 'edd' );?></h4>
					<p><?php _e( 'Many users choose to offer 100% discount codes to customers, perhaps as part of a promotion or giveaway. As of version 2.0, 100% discount codes now work perfectly even when using a credit card processing payment gateway.', 'edd' );?></p>

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/20-discount.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php _e( 'Redeeming Discount Code', 'edd' );?></h4>
					<p><?php _e( 'The discount code redemption proceess during checkout is now smoother and more intuitive. Simpler checkout processes for customers means more successful sales for you.', 'edd' );?></p>
					<p><?php _e( 'In version 2.0, customers are given the opportunity to redeem their discount code before selecting their payment method, if the site has AJAX processing disabled.', 'edd' );?></p>


				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Payment History Enhancements', 'edd' );?></h3>

				<div class="feature-section">

					<h4><?php _e( 'Copy Download Link(s)','edd' );?></h4>
					<p><?php _e( 'The Purchased Downloads section of the order details screen now includes an option to copy secure download links for any file purchased, letting you easily copy-and-paste new file download links for customers.', 'edd' );?></p>

					<h4><?php _e( 'Transaction ID Searching', 'edd' ); ?></h4>
					<p><?php _e( 'Version 2.0 now supports searching for payment records by the transaction ID from the payment processor. Have you refunded a purchase in PayPal and now need to locate it in your store\'s history? Now it is even easier.', 'edd' ); ?></p>

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/20-unlimited-downloads.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php _e( 'Unlimited File Downloads', 'edd' ); ?></h4>
					<p><?php _e( 'Several versions ago, EDD supported giving specific customers unlimited file downloads for a particular purchase. This option was accidentially removed but has now been brought back from the sad, sad grave. It is now a happy feature. You can use this option to bypass the standard file download limits imposed on purchases.', 'edd' ); ?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Additional Updates', 'edd' );?></h3>

				<div class="feature-section col three-col">
					<div>
						<h4><?php _e( 'API Keys', 'edd' );?></h4>
						<p><?php _e( 'EDD has a complete REST API for interfacing with 3rd party systems, such as mobile devices. Granting users access to the API data was always a bit cumbersome, so in version 2.0 we have introduced a completely new API Keys table under the Tools page.', 'edd' );?></p>

						<h4><?php _e( 'Tools Page', 'edd' );?></h4>
						<p><?php _e( 'As more and more tools have been developed to assist with managing your store, the Tools page got a bit cluttered. We have now taken the time to introduce a proper tabbed interface to keep things neat and tidy.', 'edd' );?></p>
					</div>

					<div>
						<h4><?php _e( 'Banned Emails', 'edd' );?></h4>
						<p><?php _e( 'Along with the new Tools page, we have introduced a new tool that allows you to blacklist specific email addresses. Emails placed on this list will not be allowed to make purchases. This is useful for combatting fraud.' ,'edd' );?></p>

						<h4><?php _e( 'Shortcode: [edd_register]', 'edd' );?></h4>
						<p><?php _e( 'Many users have asked for an option to give potential customers a way to register an account on the site without being required to go through the checkout screen. The new [edd_register] shortcode lets you place a stand-alone registration form on any page.', 'edd' );?></p>
					</div>

					<div class="last-feature">
						<h4><?php _e( 'Export Earnings / Sales Over Time', 'edd' );?></h4>
						<p><?php _e( 'The export options have been improved in version 2.0. You can now export a CSV file of earnings and sales over time. Want to have a CSV that shows earnings and sale counts for the last six months? Now you can.', 'edd' );?></p>

						<h4><?php _e( 'Improved Discount Edit Screen', 'edd' ); ?></h4>
						<p><?php _e( 'We try and live up to our name and make all aspects of running your store easy. Unfortunately, the options available when creating discount codes have never been <em>easy</em>. Version 2.0 introduces several refinements to the discount edit screen that make it dramatically more intuitive.', 'edd' );?></p>
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
	 * Render Getting Started Screen
	 *
	 * @access public
	 * @since 1.9
	 * @return void
	 */
	public function getting_started_screen() {
		list( $display_version ) = explode( '-', EDD_VERSION );
		?>
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Easy Digital Downloads %s', 'edd' ), $display_version ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Easy Digital Downloads %s is ready to make your online store faster, safer and better!', 'edd' ), $display_version ); ?></div>
			<div class="edd-badge"><?php printf( __( 'Version %s', 'edd' ), $display_version ); ?></div>

			<?php $this->tabs(); ?>

			<p class="about-description"><?php _e( 'Use the tips below to get started using Easy Digital Downloads. You will be up and running in no time!', 'edd' ); ?></p>

			<div class="changelog">
				<h3><?php _e( 'Creating Your First Download Product', 'edd' );?></h3>

				<div class="feature-section">

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/edit-download.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php printf( __( '<a href="%s">%s &rarr; Add New</a>', 'edd' ), admin_url( 'post-new.php?post_type=download' ), edd_get_label_plural() ); ?></h4>
					<p><?php printf( __( 'The %s menu is your access point for all aspects of your Easy Digital Downloads product creation and setup. To create your first product, simply click Add New and then fill out the product details.', 'edd' ), edd_get_label_plural() ); ?></p>

					<h4><?php _e( 'Product Price', 'edd' );?></h4>
					<p><?php _e( 'Products can have simple prices or variable prices if you wish to have more than one price point for a product. For a single price, simply enter the price. For multiple price points, click <em>Enable variable pricing</em> and enter the options.', 'edd' );?></p>

					<h4><?php _e( 'Download Files', 'edd' );?></h4>
					<p><?php _e( 'Uploading the downloadable files is simple. Click <em>Upload File</em> in the Download Files section and choose your download file. To add more than one file, simply click the <em>Add New</em> button.', 'edd' );?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Display a Product Grid', 'edd' );?></h3>

				<div class="feature-section">

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/grid.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php _e( 'Flexible Product Grids','edd' );?></h4>
					<p><?php _e( 'The [downloads] shortcode will display a product grid that works with any theme, no matter the size. It is even responsive!', 'edd' );?></p>

					<h4><?php _e( 'Change the Number of Columns', 'edd' );?></h4>
					<p><?php _e( 'You can easily change the number of columns by adding the columns="x" parameter:', 'edd' );?></p>
					<p><pre>[downloads columns="4"]</pre></p>

					<h4><?php _e( 'Additional Display Options', 'edd' ); ?></h4>
					<p><?php printf( __( 'The product grids can be customized in any way you wish and there is <a href="%s">extensive documentation</a> to assist you.', 'edd' ), 'http://easydigitaldownloads.com/documentation' ); ?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Purchase Buttons Anywhere', 'edd' );?></h3>

				<div class="feature-section">

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/purchase-link.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php _e( 'The <em>[purchase_link]</em> Shortcode','edd' );?></h4>
					<p><?php _e( 'With easily accessible shortcodes to display purchase buttons, you can add a Buy Now or Add to Cart button for any product anywhere on your site in seconds.', 'edd' );?></p>

					<h4><?php _e( 'Buy Now Buttons', 'edd' );?></h4>
					<p><?php _e( 'Purchase buttons can behave as either Add to Cart or Buy Now buttons. With Buy Now buttons customers are taken straight to PayPal, giving them the most frictionless purchasing experience possible.', 'edd' );?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Need Help?', 'edd' );?></h3>

				<div class="feature-section">

					<h4><?php _e( 'Phenomenal Support','edd' );?></h4>
					<p><?php _e( 'We do our best to provide the best support we can. If you encounter a problem or have a question, post a question in the <a href="https://easydigitaldownloads.com/support">support forums</a>.', 'edd' );?></p>

					<h4><?php _e( 'Need Even Faster Support?', 'edd' );?></h4>
					<p><?php _e( 'Our <a href="https://easydigitaldownloads.com/support/pricing/">Priority Support forums</a> are there for customers that need faster and/or more in-depth assistance.', 'edd' );?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Stay Up to Date', 'edd' );?></h3>

				<div class="feature-section">

					<h4><?php _e( 'Get Notified of Extension Releases','edd' );?></h4>
					<p><?php _e( 'New extensions that make Easy Digital Downloads even more powerful are released nearly every single week. Subscribe to the newsletter to stay up to date with our latest releases. <a href="http://eepurl.com/kaerz" target="_blank">Signup now</a> to ensure you do not miss a release!', 'edd' );?></p>

					<h4><?php _e( 'Get Alerted About New Tutorials', 'edd' );?></h4>
					<p><?php _e( '<a href="http://eepurl.com/kaerz" target="_blank">Signup now</a> to hear about the latest tutorial releases that explain how to take Easy Digital Downloads further.', 'edd' );?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Extensions for Everything', 'edd' );?></h3>

				<div class="feature-section">

					<h4><?php _e( 'Over 250 Extensions','edd' );?></h4>
					<p><?php _e( 'Add-on plugins are available that greatly extend the default functionality of Easy Digital Downloads. There are extensions for payment processors, such as Stripe and PayPal, extensions for newsletter integrations, and many, many more.', 'edd' );?></p>

					<h4><?php _e( 'Visit the Extension Store', 'edd' );?></h4>
					<p><?php _e( '<a href="https://easydigitaldownloads.com/extensions" target="_blank">The Extensions store</a> has a list of all available extensions, including convenient category filters so you can find exactly what you are looking for.', 'edd' );?></p>

				</div>
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

			<?php $this->tabs(); ?>

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

		$upgrade = get_option( 'edd_version_upgraded_from' );

		if( ! $upgrade ) { // First time install
			wp_safe_redirect( admin_url( 'index.php?page=edd-getting-started' ) ); exit;
		} else { // Update
			wp_safe_redirect( admin_url( 'index.php?page=edd-about' ) ); exit;
		}
	}
}
new EDD_Welcome();
