<?php
/**
 * Weclome Page Class
 *
 * @package     EDD
 * @subpackage  Admin/Welcome
 * @copyright   Copyright (c) 2015, Pippin Williamson
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
			__( 'Welcome to Easy Digital Downloads', 'easy-digital-downloads' ),
			__( 'Welcome to Easy Digital Downloads', 'easy-digital-downloads' ),
			$this->minimum_capability,
			'edd-about',
			array( $this, 'about_screen' )
		);

		// Changelog Page
		add_dashboard_page(
			__( 'Easy Digital Downloads Changelog', 'easy-digital-downloads' ),
			__( 'Easy Digital Downloads Changelog', 'easy-digital-downloads' ),
			$this->minimum_capability,
			'edd-changelog',
			array( $this, 'changelog_screen' )
		);

		// Getting Started Page
		add_dashboard_page(
			__( 'Getting started with Easy Digital Downloads', 'easy-digital-downloads' ),
			__( 'Getting started with Easy Digital Downloads', 'easy-digital-downloads' ),
			$this->minimum_capability,
			'edd-getting-started',
			array( $this, 'getting_started_screen' )
		);

		// Credits Page
		add_dashboard_page(
			__( 'The people that build Easy Digital Downloads', 'easy-digital-downloads' ),
			__( 'The people that build Easy Digital Downloads', 'easy-digital-downloads' ),
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
		remove_submenu_page( 'index.php', 'edd-changelog' );
		remove_submenu_page( 'index.php', 'edd-getting-started' );
		remove_submenu_page( 'index.php', 'edd-credits' );

		?>
		<style type="text/css" media="screen">
			/*<![CDATA[*/
			.about-wrap .edd-badge { float: right; border-radius: 4px; margin: 0 0 15px 15px; max-width: 100px; }
			.about-wrap #edd-header { margin-bottom: 15px; }
			.about-wrap #edd-header h1 { margin-bottom: 15px !important; }
			.about-wrap .about-text { margin: 0 0 15px; max-width: 670px; }
			.about-wrap .feature-section { margin-top: 20px; }
			.about-wrap .feature-section-content,
			.about-wrap .feature-section-media { width: 50%; box-sizing: border-box; }
			.about-wrap .feature-section-content { float: left; padding-right: 50px; }
			.about-wrap .feature-section-content h4 { margin: 0 0 1em; }
			.about-wrap .feature-section-media { float: right; text-align: right; margin-bottom: 20px; }
			.about-wrap .feature-section-media img { border: 1px solid #ddd; }
			.about-wrap .feature-section:not(.under-the-hood) .col { margin-top: 0; }
			/* responsive */
			@media all and ( max-width: 782px ) {
				.about-wrap .feature-section-content,
				.about-wrap .feature-section-media { float: none; padding-right: 0; width: 100%; text-align: left; }
				.about-wrap .feature-section-media img { float: none; margin: 0 0 20px; }
			}
			/*]]>*/
		</style>
		<?php
	}

	/**
	 * Welcome message
	 *
	 * @access public
	 * @since 2.5
	 * @return void
	 */
	public function welcome_message() {
		list( $display_version ) = explode( '-', EDD_VERSION );
		?>
		<div id="edd-header">
			<img class="edd-badge" src="<?php echo EDD_PLUGIN_URL . 'assets/images/edd-logo.svg'; ?>" alt="<?php _e( 'Easy Digital Downloads', 'easy-digital-downloads' ); ?>" / >
			<h1><?php printf( __( 'Welcome to Easy Digital Downloads %s', 'easy-digital-downloads' ), $display_version ); ?></h1>
			<p class="about-text">
				<?php printf( __( 'Thank you for updating to the latest version! Easy Digital Downloads %s is ready to make your online store faster, safer, and better!', 'easy-digital-downloads' ), $display_version ); ?>
			</p>
		</div>
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
				<?php _e( "What's New", 'easy-digital-downloads' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'edd-getting-started' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-getting-started' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Getting Started', 'easy-digital-downloads' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'edd-credits' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-credits' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Credits', 'easy-digital-downloads' ); ?>
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
		?>
		<div class="wrap about-wrap">
			<?php
				// load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>
			<div class="changelog">
				<h3><?php _e( 'Amazon Payments', 'easy-digital-downloads' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/24-checkout.png'; ?>"/>
					</div>
					<div class="feature-section-content">
						<p><?php _e( 'With Easy Digital Downloads version 2.4, you can now accept payments through Amazon\'s Login and Pay with the new built-in payment gateway.', 'easy-digital-downloads' );?></p>

						<h4><?php _e( 'Secure Checkout', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'When using Amazon Payments, credit / debit card details are entered on Amazon\'s secure servers and never pass through your own server, making the entire process dramatically more secure and reliable.', 'easy-digital-downloads' );?></p>

						<h4><?php _e( 'Accept Credit and Debit Card Payments', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'Amazon Payments allows your customers to easily pay with their debit or credit cards. During checkout, customers will be provided an option to use a stored card or enter a new one.', 'easy-digital-downloads' );?></p>

						<h4><?php _e( 'Simple Customer Authentication', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'Customers can log into their Amazon account from your checkout screen and have all of their billing details retrieved automatically from Amazon. With just a few clicks, customers can effortlessly complete their purchase.', 'easy-digital-downloads' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Earnings / Sales By Category', 'easy-digital-downloads' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/24-category-earnings.png'; ?>"/>
					</div>
					<div class="feature-section-content">
						<p><?php _e( 'Easy Digital Downloads version 2.4 introduces a new Report that displays earnings and sales for your product categories.', 'easy-digital-downloads' );?></p>

						<h4><?php _e( 'Earnings and Sales Overview', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'Quickly see how each of your categories has performed over the lifetime of your store. The total sales and earnings are displayed, as well as the average monthly sales and earnings for each category.', 'easy-digital-downloads' );?></p>

						<h4><?php _e( 'Category Sales / Earnings Mix', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'The report includes a visual break down of the sales / earnings mix for your categories. Quickly see which categories account for the highest (or lowest) percentage of your sales and earnings.', 'easy-digital-downloads' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Improved Data Export', 'easy-digital-downloads' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/24-export.png'; ?>" class="edd-welcome-screenshots"/>
					</div>
					<div class="feature-section-content">
						<h4><?php _e( 'Big Data Support', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'With the new export processing in Easy Digital Downloads 2.4, you can easily export massive amounts of data. Need to export 20,000 payment records? No problem.', 'easy-digital-downloads' );?></p>

						<h4><?php _e( 'Standardized Customer Export', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'The Customer export has been standardized so it now produces the same data during export for all export options. It can also easily handle 20,000 or even 50,000 customer records in a single export.', 'easy-digital-downloads' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Additional Updates', 'easy-digital-downloads' );?></h3>
				<div class="feature-section three-col">
					<div class="col">
						<h4><?php _e( 'REST API Versioning', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'The REST API now supports a version parameter that allows you to specify which version of the API you wish to use.', 'easy-digital-downloads' );?></p>
					</div>
					<div class="col">
						<h4><?php _e( 'Better Cart Tax Display', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'Cart widgets now display estimated taxes for customers before reaching the checkout page.', 'easy-digital-downloads' );?></p>
					</div>
					<div class="col">
						<h4><?php _e( 'Customer > User Synchronization', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'Customer email addresses are now updated when the associated user account\'s email is changed.' ,'easy-digital-downloads' );?></p>
					</div>
					<div class="clear">
						<div class="col">
							<h4><?php _e( 'Better Test Mode Settings', 'easy-digital-downloads' );?></h4>
							<p><?php _e( 'Test Mode has been improved by moving the option to the Payment Gateways screen. Sales / earnings stats are now incremented in test mode.', 'easy-digital-downloads' );?></p>
						</div>
						<div class="col">
							<h4><?php _e( 'Exclude Taxes from Reports', 'easy-digital-downloads' );?></h4>
							<p><?php _e( 'Earnings and sales reports can now be shown exclusive of tax, allowing you to easily see how your store is performing after taxes.', 'easy-digital-downloads' );?></p>
						</div>
						<div class="col">
							<h4><?php _e( 'Default Gateway First', 'easy-digital-downloads' );?></h4>
							<p><?php _e( 'The gateway selected as the default option will always be displayed first on checkout.' ,'easy-digital-downloads' );?></p>
						</div>
					</div>
				</div>
			</div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'download', 'page' => 'edd-settings' ), 'edit.php' ) ) ); ?>"><?php _e( 'Go to Easy Digital Downloads Settings', 'easy-digital-downloads' ); ?></a> &middot;
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'edd-changelog' ), 'index.php' ) ) ); ?>"><?php _e( 'View the Full Changelog', 'easy-digital-downloads' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Changelog Screen
	 *
	 * @access public
	 * @since 2.0.3
	 * @return void
	 */
	public function changelog_screen() {
		?>
		<div class="wrap about-wrap">
			<?php
				// load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>
			<div class="changelog">
				<h3><?php _e( 'Full Changelog', 'easy-digital-downloads' );?></h3>

				<div class="feature-section">
					<?php echo $this->parse_readme(); ?>
				</div>
			</div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'post_type' => 'download', 'page' => 'edd-settings' ), 'edit.php' ) ) ); ?>"><?php _e( 'Go to Easy Digital Downloads Settings', 'easy-digital-downloads' ); ?></a>
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
		?>
		<div class="wrap about-wrap">
			<?php
				// load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>
			<p class="about-description"><?php _e( 'Use the tips below to get started using Easy Digital Downloads. You will be up and running in no time!', 'easy-digital-downloads' ); ?></p>

			<div class="changelog">
				<h3><?php _e( 'Creating Your First Download Product', 'easy-digital-downloads' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/edit-download.png'; ?>" class="edd-welcome-screenshots"/>
					</div>
					<div class="feature-section-content">
						<h4><?php printf( __( '<a href="%s">%s &rarr; Add New</a>', 'easy-digital-downloads' ), admin_url( 'post-new.php?post_type=download' ), edd_get_label_plural() ); ?></h4>
						<p><?php printf( __( 'The %s menu is your access point for all aspects of your Easy Digital Downloads product creation and setup. To create your first product, simply click Add New and then fill out the product details.', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></p>

						<h4><?php _e( 'Product Price', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'Products can have simple prices or variable prices if you wish to have more than one price point for a product. For a single price, simply enter the price. For multiple price points, click <em>Enable variable pricing</em> and enter the options.', 'easy-digital-downloads' );?></p>

						<h4><?php _e( 'Download Files', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'Uploading the downloadable files is simple. Click <em>Upload File</em> in the Download Files section and choose your download file. To add more than one file, simply click the <em>Add New</em> button.', 'easy-digital-downloads' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Display a Product Grid', 'easy-digital-downloads' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/grid.png'; ?>"/>
					</div>
					<div class="feature-section-content">
						<h4><?php _e( 'Flexible Product Grids','easy-digital-downloads' );?></h4>
						<p><?php _e( 'The [downloads] shortcode will display a product grid that works with any theme, no matter the size. It is even responsive!', 'easy-digital-downloads' );?></p>

						<h4><?php _e( 'Change the Number of Columns', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'You can easily change the number of columns by adding the columns="x" parameter:', 'easy-digital-downloads' );?></p>
						<p><pre>[downloads columns="4"]</pre></p>

						<h4><?php _e( 'Additional Display Options', 'easy-digital-downloads' ); ?></h4>
						<p><?php printf( __( 'The product grids can be customized in any way you wish and there is <a href="%s">extensive documentation</a> to assist you.', 'easy-digital-downloads' ), 'http://docs.easydigitaldownloads.com/' ); ?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Purchase Buttons Anywhere', 'easy-digital-downloads' );?></h3>
				<div class="feature-section">
					<div class="feature-section-media">
						<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/purchase-link.png'; ?>"/>
					</div>
					<div class="feature-section-content">
						<h4><?php _e( 'The <em>[purchase_link]</em> Shortcode','easy-digital-downloads' );?></h4>
						<p><?php _e( 'With easily accessible shortcodes to display purchase buttons, you can add a Buy Now or Add to Cart button for any product anywhere on your site in seconds.', 'easy-digital-downloads' );?></p>

						<h4><?php _e( 'Buy Now Buttons', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'Purchase buttons can behave as either Add to Cart or Buy Now buttons. With Buy Now buttons customers are taken straight to PayPal, giving them the most frictionless purchasing experience possible.', 'easy-digital-downloads' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Need Help?', 'easy-digital-downloads' );?></h3>
				<div class="feature-section two-col">
					<div class="col">
						<h4><?php _e( 'Phenomenal Support','easy-digital-downloads' );?></h4>
						<p><?php _e( 'We do our best to provide the best support we can. If you encounter a problem or have a question, simply open a ticket using our <a href="https://easydigitaldownloads.com/support">support form</a>.', 'easy-digital-downloads' );?></p>
					</div>
					<div class="col">
						<h4><?php _e( 'Need Even Faster Support?', 'easy-digital-downloads' );?></h4>
						<p><?php _e( 'Our <a href="https://easydigitaldownloads.com/support/pricing/">Priority Support</a> system is there for customers that need faster and/or more in-depth assistance.', 'easy-digital-downloads' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Stay Up to Date', 'easy-digital-downloads' );?></h3>
				<div class="feature-section two-col">
					<div class="col">
						<h4><?php _e( 'Get Notified of Extension Releases','easy-digital-downloads' );?></h4>
						<p><?php _e( 'New extensions that make Easy Digital Downloads even more powerful are released nearly every single week. Subscribe to the newsletter to stay up to date with our latest releases. <a href="http://eepurl.com/kaerz" target="_blank">Sign up now</a> to ensure you do not miss a release!', 'easy-digital-downloads' );?></p>
					</div>
					<div class="col">
						<h4><?php _e( 'Get Alerted About New Tutorials', 'easy-digital-downloads' );?></h4>
						<p><?php _e( '<a href="http://eepurl.com/kaerz" target="_blank">Sign up now</a> to hear about the latest tutorial releases that explain how to take Easy Digital Downloads further.', 'easy-digital-downloads' );?></p>
					</div>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Extensions for Everything', 'easy-digital-downloads' );?></h3>
				<div class="feature-section two-col">
					<div class="col">
						<h4><?php _e( 'Over 250 Extensions','easy-digital-downloads' );?></h4>
						<p><?php _e( 'Add-on plugins are available that greatly extend the default functionality of Easy Digital Downloads. There are extensions for payment processors, such as Stripe and PayPal, extensions for newsletter integrations, and many, many more.', 'easy-digital-downloads' );?></p>
					</div>
					<div class="col">
						<h4><?php _e( 'Visit the Extension Store', 'easy-digital-downloads' );?></h4>
						<p><?php _e( '<a href="https://easydigitaldownloads.com/downloads" target="_blank">The Extensions store</a> has a list of all available extensions, including convenient category filters so you can find exactly what you are looking for.', 'easy-digital-downloads' );?></p>
					</div>
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
		?>
		<div class="wrap about-wrap">
			<?php
				// load welcome message and content tabs
				$this->welcome_message();
				$this->tabs();
			?>
			<p class="about-description"><?php _e( 'Easy Digital Downloads is created by a worldwide team of developers who aim to provide the #1 eCommerce platform for selling digital goods through WordPress.', 'easy-digital-downloads' ); ?></p>

			<?php echo $this->contributors(); ?>
		</div>
		<?php
	}


	/**
	 * Parse the EDD readme.txt file
	 *
	 * @since 2.0.3
	 * @return string $readme HTML formatted readme file
	 */
	public function parse_readme() {
		$file = file_exists( EDD_PLUGIN_DIR . 'readme.txt' ) ? EDD_PLUGIN_DIR . 'readme.txt' : null;

		if ( ! $file ) {
			$readme = '<p>' . __( 'No valid changelog was found.', 'easy-digital-downloads' ) . '</p>';
		} else {
			$readme = file_get_contents( $file );
			$readme = nl2br( esc_html( $readme ) );
			$readme = explode( '== Changelog ==', $readme );
			$readme = end( $readme );

			$readme = preg_replace( '/`(.*?)`/', '<code>\\1</code>', $readme );
			$readme = preg_replace( '/[\040]\*\*(.*?)\*\*/', ' <strong>\\1</strong>', $readme );
			$readme = preg_replace( '/[\040]\*(.*?)\*/', ' <em>\\1</em>', $readme );
			$readme = preg_replace( '/= (.*?) =/', '<h4>\\1</h4>', $readme );
			$readme = preg_replace( '/\[(.*?)\]\((.*?)\)/', '<a href="\\2">\\1</a>', $readme );
		}

		return $readme;
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
				esc_html( sprintf( __( 'View %s', 'easy-digital-downloads' ), $contributor->login ) )
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

		$response = wp_remote_get( 'https://api.github.com/repos/easydigitaldownloads/Easy-Digital-Downloads/contributors?per_page=999', array( 'sslverify' => false ) );

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
	 * @return void
	 */
	public function welcome() {
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
