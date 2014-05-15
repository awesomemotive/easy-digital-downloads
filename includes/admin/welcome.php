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
			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Easy Digital Downloads %s is ready to make your online store faster, safer and better!', 'edd' ), $display_version ); ?></div>
			<div class="edd-badge"><?php printf( __( 'Version %s', 'edd' ), $display_version ); ?></div>

			<?php $this->tabs(); ?>

			<div class="changelog">
				<h3><?php _e( 'Improved Order Editing', 'edd' );?></h3>

				<div class="feature-section">

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/order-details.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php _e( 'Combined View and Edit Screens', 'edd' );?></h4>
					<p><?php _e( 'The View and Edit payment screens have been combined into a single, more efficient, user-friendly screen. Add or remove products to an order, adjust amounts, add notes, or resend purchase receipts all at one time from the same screen.', 'edd' );?></p>
					<p><?php _e( 'All data associated with a payment can now be edited as well, including the customer\'s billing address.', 'edd' );?></p>

					<h4><?php _e( 'Responsive and Mobile Friendly', 'edd' );?></h4>
					<p><?php _e( 'We have followed the introduction of a responsive Dashboard in WordPress 3.8 and made our own view/edit screen for orders fully responsive and easy to use on mobile devices.', 'edd' );?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Per-Products Sales and Earnings Graphs', 'edd' );?></h3>

				<div class="feature-section">

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/product-earnings.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php _e( 'See Earnings and Sales for Any Time Period','edd' );?></h4>
					<p><?php _e( 'With 1.9 we have introduced beautiful graphs for individual products that allows you to view earnings and sales over any time period. Easily see earnings / sales for monthly, yearly, quarterly, or any other date range for any product in your store.', 'edd' );?></p>

					<h4><?php _e( 'Easily Access Reports', 'edd' );?></h4>
					<p><?php printf( __( 'Per-product earnings / sales graphs can be accessed from <em>%s &rarr; Reports &rarr; %s</em> or from the <em>Stats</em> section of the Edit screen for any %s.', 'edd' ), edd_get_label_plural(), edd_get_label_plural(), edd_get_label_singular() );?></p>


				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Dramatically Improved Taxes', 'edd' );?></h3>

				<div class="feature-section">

					<img src="<?php echo EDD_PLUGIN_URL . 'assets/images/screenshots/product-tax.png'; ?>" class="edd-welcome-screenshots"/>

					<h4><?php _e( 'Mark Products Exclusive of Tax', 'edd' );?></h4>
					<p><?php _e( 'Products in your store can now be marked as exclusive of tax, meaning customers will never have to pay tax on these products during checkout.', 'edd' );?></p>

					<h4><?php _e( 'Re-written Tax API', 'edd' );?></h4>
					<p><?php _e( 'The tax system in EDD has been plagued with bugs since it was first introduced, so in 1.9 we have completely rewritten the entire system from the ground up to ensure it is reliable and bug free.', 'edd' );?></p>
					<p><?php _e( 'It can be difficult to completely delete an entire section of old code, but we are confident the rewrite will be worth every minute of the time spent on it.', 'edd' );?></p>
					<p><?php _e( 'We are determined to continue to provide you a reliable, easy system to sell your digital products. In order to do that, sometimes we just have to swallow our pride and start over.', 'edd' );?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Better Support for Large Stores', 'edd' );?></h3>

				<div class="feature-section">

					<h4><?php _e( 'Live Search Product Drop Downs','edd' );?></h4>
					<p><?php _e( 'Every product drop down menu used in Easy Digital Downloads has been replaced with a much more performant version that includes a live Ajax search, meaning stores that have a large number of products will see a significant improvement for page load times in the WordPress Dashboard.', 'edd' );?></p>

					<h4><?php _e( 'Less Memory Intensive Log Pages', 'edd' ); ?></h4>
					<p><?php _e( 'The File Download log pages have long been memory intensive to load. By putting them through intensive memory load tests and query optimization, we were able to reduce the number of queries and amount of memory used by a huge degree, making these pages much, much faster..', 'edd' ); ?></p>

				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Additional Updates', 'edd' );?></h3>

				<div class="feature-section col three-col">
					<div>
						<h4><?php _e( 'Improved Product Creation / Editing', 'edd' );?></h4>
						<p><?php _e( 'The interface for creating / editing Download products has been dramatically improved by separating the UI out into sections that are easier to use and less cluttered.', 'edd' );?></p>

						<h4><?php _e( 'EDD_Graph Class', 'edd' );?></h4>
						<p><?php _e( 'Along with per-product earnings / sales graphs, we have introduced an EDD_Graph class that makes it exceptionally simple to generate your own custom graphs. Simply build an array of data and let the class work its magic.', 'edd' );?></p>
					</div>

					<div>
						<h4><?php _e( 'Payment Date Filters', 'edd' );?></h4>
						<p><?php _e( 'A new section has been added to the Payment History screen that allows you to filter payments by date, making it much easier to locate payments for a particular period.', 'edd' );?></p>

						<h4><?php _e( 'EDD_Email_Template_Tags Class', 'edd' );?></h4>
						<p><?php _e( 'A new API has been introduced for easily adding new template tags to purchase receipts and admin sale notifications.', 'edd' );?></p>
					</div>

					<div class="last-feature">
						<h4><?php _e( 'Resend Purchase Receipts in Bulk', 'edd' );?></h4>
						<p><?php _e( 'A new action has been added to the Bulk Actions menu in the Payment History screen that allows you to resend purchase receipt emails in bulk.' ,'edd' );?></p>

						<h4><?php _e( 'Exclude Products from Discounts','edd' );?></h4>
						<p><?php _e( 'Along with being able to assign discounts to specific products, you can also now exclude products from discount codes.', 'edd' );?></p>
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
					<p><?php _e( 'The [downloads] short code will display a product grid that works with any theme, no matter the size. It is even responsive!', 'edd' );?></p>

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

					<h4><?php _e( 'The <em>[purchase_link]</em> Short Code','edd' );?></h4>
					<p><?php _e( 'With easily accessible short codes to display purchase buttons, you can add a Buy Now or Add to Cart button for any product anywhere on your site in seconds.', 'edd' );?></p>

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

					<h4><?php _e( 'Over 190 Extensions','edd' );?></h4>
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
