<?php
/**
 * Onboarding Wizard Class.
 *
 * @package     EDD
 * @subpackage  Onboarding
 * @copyright   Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.1.1
 */

namespace EDD\Admin\Onboarding;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Onboarding Class.
 *
 * Takes care of everything related to Onboarding Wizard.
 *
 * @since 3.1.1
 */
class Wizard implements \EDD\EventManagement\SubscriberInterface {

	/**
	 * Current Onboarding step.
	 *
	 * @since 3.1.1
	 *
	 * @var string
	 */
	private $current_step = 'business_info';

	/**
	 * Current Onboarding step index.
	 *
	 * @since 3.1.1
	 *
	 * @var int
	 */
	private $current_step_index = 1;

	/**
	 * Onboarding steps.
	 *
	 * @since 3.1.1
	 *
	 * @var array
	 */
	private $onboarding_steps = array();

	/**
	 * True if user started onboarding process.
	 *
	 * @since 3.1.1
	 *
	 * @var bool
	 */
	private $onboarding_started = false;

	/**
	 * Whether onboarding has been completed once.
	 *
	 * @since 3.1.1
	 * @var bool
	 */
	private $onboarding_completed = false;

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'admin_init'                               => 'redirect',
			'admin_menu'                               => array( 'add_menu_item', 5 ),
			'wp_ajax_edd_onboarding_load_step'         => 'ajax_onboarding_load_step',
			'load-download_page_edd-onboarding-wizard' => 'load_onboarding_wizard',
			'admin_enqueue_scripts'                    => 'enqueue_onboarding_scripts',
		);
	}

	/**
	 * Maybe redirect to the onboarding wizard.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function redirect() {
		if ( wp_doing_ajax() ) {
			return;
		}
		if ( ! get_transient( 'edd_onboarding_redirect' ) ) {
			return;
		}

		delete_transient( 'edd_onboarding_redirect' );

		if ( get_option( 'edd_onboarding_prevent_redirect' ) ) {
			return;
		}

		if ( isset( $_GET['activate-multi'] ) || is_network_admin() ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		edd_redirect(
			edd_get_admin_url(
				array(
					'page' => 'edd-onboarding-wizard',
				)
			)
		);
	}

	/**
	 * Add Onboarding Wizard submenu page.
	 *
	 * @since 3.1.1
	 */
	public function add_menu_item() {
		add_submenu_page( 'edit.php?post_type=download', __( 'Setup', 'easy-digital-downloads' ), __( 'Setup', 'easy-digital-downloads' ), 'manage_shop_settings', 'edd-onboarding-wizard', array( $this, 'onboarding_wizard_sub_page' ) );
		add_action( 'admin_head', array( $this, 'adjust_menu_item_class' ) );
	}

	/**
	 * Adds the custom pro menu item class.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function adjust_menu_item_class() {
		new \EDD\Admin\Menu\LinkClass( 'edd-onboarding-wizard', 'edd-onboarding__menu-item' );
		if ( $this->has_onboarding_been_completed() ) {
			remove_submenu_page( 'edit.php?post_type=download', 'edd-onboarding-wizard' );
		}
	}

	/**
	 * Determine if we are on Onboarding Wizard screen
	 * and load all of the neccesarry hooks and actions.
	 *
	 * @since 3.1.1
	 */
	public function load_onboarding_wizard() {
		if ( ! $this->is_wizard() ) {
			return;
		}
		// Hide EDD header.
		remove_action( 'admin_notices', 'edd_admin_header', 1 );

		// Set variables.
		$this->onboarding_started = $this->has_onboarding_started();
		$this->set_onboarding_steps();
		$this->set_current_onboarding_step();

		// We don't want any notices on our screen.
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );

		// Override Stripe callback urls.
		add_filter( 'edds_stripe_connect_url', array( $this, 'update_stripe_connect_url' ), 15 );

		add_filter(
			'edd_pointers',
			function ( $pointers ) {
				return array();
			}
		);
	}

	/**
	 * Load scripts and styles.
	 *
	 * @since 3.1.1
	 */
	public function enqueue_onboarding_scripts() {
		if ( ! $this->is_wizard() ) {
			return;
		}
		wp_enqueue_style( 'edd-admin-onboarding' );
		wp_enqueue_script( 'edd-admin-onboarding' );

		wp_enqueue_style( 'edd-extension-manager' );
		wp_enqueue_script( 'edd-extension-manager' );

		wp_enqueue_media();
		wp_enqueue_editor();

		if ( array_key_exists( 'payment_methods', $this->onboarding_steps ) ) {
			edd_stripe_connect_admin_script( 'download_page_edd-settings' );
		}

		edd_email_tags_inserter_enqueue_scripts();
	}

	/**
	 * Override Stripe connect url.
	 *
	 * @since 3.1.1
	 */
	public function update_stripe_connect_url() {
		$return_url = edd_get_admin_url(
			array(
				'redirect_screen' => 'onboarding-wizard',
			)
		);

		return add_query_arg(
			array(
				'live_mode'         => (int) ! edd_is_test_mode(),
				'state'             => str_pad( wp_rand( wp_rand(), PHP_INT_MAX ), 100, wp_rand(), STR_PAD_BOTH ),
				'customer_site_url' => urlencode( esc_url_raw( $return_url ) ),
			),
			'https://easydigitaldownloads.com/?edd_gateway_connect_init=stripe_connect'
		);
	}

	/**
	 * Set onboarding steps.
	 *
	 * @since 3.1.1
	 */
	public function set_onboarding_steps() {
		$this->onboarding_steps = array(
			'business_info'    => array(
				'step_title'    => __( 'Business', 'easy-digital-downloads' ),
				'step_headline' => __( 'Tell us a little bit about your business.', 'easy-digital-downloads' ),
				'step_intro'    => __( 'Where is your business located? This helps Easy Digital Downloads configure the checkout and receipt templates.', 'easy-digital-downloads' ),
				'step_handler'  => 'BusinessInfo',
			),
			'payment_methods'  => array(
				'step_title'    => __( 'Payment Methods', 'easy-digital-downloads' ),
				'step_headline' => __( 'Start accepting payments today!', 'easy-digital-downloads' ),
				'step_intro'    => '',
				'step_handler'  => 'PaymentMethods',
			),
			'configure_emails' => array(
				'step_title'    => __( 'Emails', 'easy-digital-downloads' ),
				'step_headline' => __( 'Configure your Receipts', 'easy-digital-downloads' ),
				'step_intro'    => __( 'Customize the purchase receipt that your customers will receive.', 'easy-digital-downloads' ),
				'step_handler'  => 'ConfigureEmails',
			),
			'tools'            => array(
				'step_title'    => __( 'Tools', 'easy-digital-downloads' ),
				'step_headline' => __( 'Conversion and Optimization tools', 'easy-digital-downloads' ),
				'step_intro'    => __( 'We have selected our recommended tools and features to help boost conversions and optimize your digital store.', 'easy-digital-downloads' ),
				'step_handler'  => 'Tools',
			),
			'products'         => array(
				'step_title'    => __( 'Products', 'easy-digital-downloads' ),
				'step_headline' => __( 'What are you going to sell?', 'easy-digital-downloads' ),
				'step_intro'    => __( 'Let\'s get started creating your first awesome product.', 'easy-digital-downloads' ),
				'step_handler'  => 'Products',
			),
		);

		// If Stripe classes are not available, remove payment methods step.
		if ( ! defined( 'EDD_STRIPE_VERSION' ) ) {
			unset( $this->onboarding_steps['payment_methods'] );
		}

		// Determine products step intro.
		$products = new \WP_Query(
			array(
				'post_type'      => 'download',
				'posts_per_page' => 1,
				'no_found_rows'  => true,
				'fields'         => 'ids',
			)
		);
		if ( ! empty( $products->posts ) ) {
			$this->onboarding_steps['products']['step_intro'] = __( 'Let\'s get started with your next great product.', 'easy-digital-downloads' );
		}

		// Set step index in the array and load ajax handlers.
		$index = 1;
		foreach ( $this->onboarding_steps as $key => $value ) {
			$this->onboarding_steps[ $key ]['step_index'] = $index;
			++$index;
		}
	}

	/**
	 * Set current onboarding step.
	 *
	 * @since 3.1.1
	 */
	public function set_current_onboarding_step() {
		// If Onboarding hasn't started yet, we force the first default step.
		if ( ! $this->onboarding_started ) {
			return;
		}

		// User is requesting a specific step.
		$this->current_step = $this->get_current_step();

		// If requested step does not exist, abort.
		if ( ! isset( $this->onboarding_steps[ $this->current_step ] ) ) {
			wp_die( __( 'Unknown Onboarding Step.', 'easy-digital-downloads' ), __( 'Onboarding Wizard', 'easy-digital-downloads' ), 404 );
		}

		$this->current_step_index = $this->onboarding_steps[ $this->current_step ]['step_index'];
		if ( $this->has_onboarding_been_completed() ) {
			return;
		}
		update_option( 'edd_onboarding_latest_step', $this->current_step, false );
	}

	/**
	 * Get previous step.
	 *
	 * @since 3.1.1
	 */
	public function get_previous_step() {
		$internal_step = $this->current_step_index - 2;
		$step_keys     = array_keys( $this->onboarding_steps );
		if ( isset( $step_keys[ $internal_step ] ) ) {
			return $step_keys[ $internal_step ];
		}

		return false;
	}

	/**
	 * Get current step.
	 *
	 * @since 3.1.1
	 */
	public function get_current_step() {
		if ( isset( $_GET['current_step'] ) ) {
			return sanitize_key( $_GET['current_step'] );
		}

		return sanitize_key( get_option( 'edd_onboarding_latest_step', $this->current_step ) );
	}

	/**
	 * Get current step details.
	 *
	 * @since 3.1.1
	 */
	public function get_current_step_details() {
		return $this->onboarding_steps[ $this->get_current_step() ];
	}

	/**
	 * Get next step.
	 *
	 * @since 3.1.1
	 */
	public function get_next_step() {
		$internal_step = $this->current_step_index;
		$step_keys     = array_keys( $this->onboarding_steps );
		if ( isset( $step_keys[ $internal_step ] ) ) {
			return $step_keys[ $internal_step ];
		}

		return false;
	}

	/**
	 * Get pagination.
	 *
	 * @since 3.1.1
	 */
	public function get_step_pagination() {
		return array(
			'previous' => $this->get_previous_step(),
			'current'  => $this->get_current_step(),
			'next'     => $this->get_next_step(),
		);
	}

	/**
	 * Onboarding Wizard subpage screen.
	 *
	 * @since 3.1.1
	 */
	public function onboarding_wizard_sub_page() {
		$onboarding_initial_style = ( ! $this->onboarding_started ) ? ' style="display:none;"' : '';
		?>
		<?php wp_nonce_field( 'edd_onboarding_wizard' ); ?>
		<div class="edd-onboarding wrap">
			<div class="edd-onboarding__logo">
				<img src="<?php echo esc_url( EDD_PLUGIN_URL . 'assets/images/logo-edd-dark.svg' ); ?>" alt="">
			</div>
			<div class="edd-onboarding__wrapper">
				<div class="edd-onboarding__loading" style="display: none;">
					<div class="edd-onboarding__loading-content-wrapper">
						<div class="edd-onboarding__loading-status"></div>
					</div>
				</div>

				<?php $this->get_welcome_screen(); ?>
				<div class="edd-onboarding__after-welcome-screen"<?php echo $onboarding_initial_style; ?>>
					<div class="edd-onboarding__current-step">
						<?php $this->load_step_view(); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Welcome screen.
	 *
	 * @since 3.1.1
	 */
	public function get_welcome_screen() {
		if ( $this->onboarding_started ) {
			return;
		}

		$testimonials = array(
			array(
				'name'    => 'Joe Casabona',
				'company' => 'How I Built It',
				'content' => 'The problem with many e-commerce platforms to sell online courses is they aren\'t made with only digital goods in mind. <span class="big">EDD doesn\'t have that problem, and as a result their platform is perfectly made for selling my online courses.</span>',
				'avatar'  => 'joe.jpg',
				'stars'   => 5,
			),
			array(
				'name'    => 'Nicolas Martin',
				'company' => 'Flea Market Insiders',
				'content' => 'Before EDD\'s Recurring Payments was made available, we were only able to sell one-time subscriptions to our customers. Since implementing recurring payments, we\'ve been able to offer quarterly and yearly subscriptions and subsequently <span class="big">increase our subscriptions revenue by 200%.</span>',
				'avatar'  => 'nicolas.jpg',
				'stars'   => 5,
			),
			array(
				'name'    => 'Bob Dunn',
				'company' => 'BobWP',
				'content' => 'If anyone asks me what they should use for downloadable products on their WordPress site, <span class="big">it\'s a no-brainer as far as EDD goes.</span>',
				'avatar'  => 'bob.jpg',
				'stars'   => 5,
			),
		);
		?>
		<div class="edd-onboarding__welcome-screen">
			<div class="edd-onboarding__welcome-screen-inner">
				<h1>👋 <?php esc_html_e( 'Welcome, and thanks for choosing us!', 'easy-digital-downloads' ); ?></h1>
				<p><?php esc_html_e( 'Easy Digital Downloads setup is fast and easy. We\'ll walk you through the quick initial process. And don\'t worry. You can go back and change anything you do – at anytime. Nothing\'s permanent (unless you want it to be). So feel free to explore!', 'easy-digital-downloads' ); ?></p>
				<button class="button button-hero edd-onboarding__welcome-screen-get-started"><?php esc_html_e( 'Get Started', 'easy-digital-downloads' ); ?></button>
				<h2><?php esc_html_e( 'Creators ❤️ Easy Digital Downloads', 'easy-digital-downloads' ); ?></h2>
				<div class="edd-onboarding__testimonials-wrapper">
					<?php foreach ( $testimonials as $testimonial ) : ?>
						<div class="edd-onboarding__testimonial">
							<div class="edd-onboarding__testimonial-profile">
								<img class="edd-onboarding__testimonial-avatar" src="<?php echo esc_url( EDD_PLUGIN_URL . "assets/images/onboarding/{$testimonial['avatar']}" ); ?>" />
							</div>
							<div class="edd-onboarding__testimonial-content">
								<p><?php echo wp_kses_post( $testimonial['content'] ); ?></p>
								<div class="edd-onboarding__testimonial-info">
									<span class="testimonial-name"><?php echo esc_html( $testimonial['name'] ); ?></span>
									<span class="testimonial-company"><?php echo esc_html( $testimonial['company'] ); ?></span>
									<span class="testimonial-stars">
										<?php for ( $star = 1; $star <= $testimonial['stars']; $star++ ) : ?>
											<span class="dashicons dashicons-star-filled"></span>
										<?php endfor; ?>
									</span>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Load requested step HTML.
	 *
	 * @since 3.1.1
	 */
	private function load_step_view() {
		$current_step_details = $this->get_current_step_details();
		$pagination           = $this->get_step_pagination();
		$step_class_name      = 'EDD\\Admin\\Onboarding\\Steps\\' . $current_step_details['step_handler'];
		$step_class           = new $step_class_name();
		?>
		<input type="hidden" class="edd-onboarding_current-previous-step" value="<?php echo esc_attr( $this->get_previous_step() ); ?>">
		<input type="hidden" class="edd-onboarding_current-step" value="<?php echo esc_attr( $this->get_current_step() ); ?>">
		<input type="hidden" class="edd-onboarding_current-next-step" value="<?php echo esc_attr( $this->get_next_step() ); ?>">

		<!-- STEPS NAVIGATION -->
		<div class="edd-onboarding__steps">
			<ul>
				<?php
				foreach ( $this->onboarding_steps as $step_key => $step ) :
					$step_url = edd_get_admin_url(
						array(
							'post_type'    => 'download',
							'page'         => 'edd-onboarding-wizard',
							'current_step' => sanitize_key( $step_key ),
						)
					);

					$classes = array();
					// Determine if this step is active.
					if ( $step['step_index'] === $this->current_step_index ) {
						$classes[] = 'active-step';
					}
					// Determine if this step is completed.
					if ( $this->current_step_index > $step['step_index'] ) {
						$classes[] = 'completed-step';
					}
					?>
					<li class="<?php echo implode( ' ', array_map( 'esc_attr', $classes ) ); ?>">
						<a href="<?php echo esc_url( $step_url ); ?>">
							<span class="edd-onboarding__steps__number"><?php echo esc_html( $step['step_index'] ); ?></span>
							<small class="edd-onboarding__steps__name"><?php echo esc_html( $step['step_title'] ); ?> </small>
						</a>
					</li>
					<?php
				endforeach;
				?>
			</ul>
		</div>

		<div class="edd-onboarding__single-step">
			<!-- STEP VIEW -->
			<div class="edd-onboarding__single-step-inner">
				<h1 class="edd-onboarding__single-step-title"><?php echo esc_html( $current_step_details['step_headline'] ); ?></h1>
				<h2 class="edd-onboarding__single-step-subtitle"><?php echo esc_html( $current_step_details['step_intro'] ); ?></h2>
				<?php
				$step_class->step_html();
				?>
			</div>
			<div class="edd-onboarding__single-step-footer">
				<div>
					<?php if ( $pagination['previous'] ) : ?>
						<button class="edd-onboarding__button-back">← <?php echo esc_html_e( 'Go Back', 'easy-digital-downloads' ); ?></button>
					<?php endif; ?>
				</div>
				<div>
					<button class="button button-secondary button-hero edd-onboarding__button-skip-step"><?php echo esc_html_e( 'Skip this step', 'easy-digital-downloads' ); ?></button>
					<button class="button button-primary button-hero edd-onboarding__button-save-step"><?php echo esc_html_e( 'Save & Continue', 'easy-digital-downloads' ); ?></button>
				</div>
			</div>
		</div>
		<div class="edd-onboarding__close-and-exit">
			<button class="<?php echo esc_attr( implode( ' ', $this->get_close_exit_button_classes() ) ); ?>"><?php echo esc_html_e( 'Close and Exit Without Saving', 'easy-digital-downloads' ); ?></button>
		</div>
		<input type="hidden" id="edd-onboarding__exit" value="<?php echo esc_url( admin_url( 'edit.php?post_type=download' ) ); ?>" />
		<?php
	}

	/**
	 * Ajax callback for loading single step view.
	 *
	 * @since 3.1.1
	 */
	public function ajax_onboarding_load_step() {
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'edd_onboarding_wizard' ) ) {
			exit();
		}

		// When AJAX loads, we need to initilalize everything.
		$this->load_onboarding_wizard();

		// Now load the step.
		$this->load_step_view();
		exit;
	}

	/**
	 * Whether the current request is the onboarding wizard.
	 *
	 * @since 3.1.1
	 * @return bool
	 */
	private function is_wizard() {
		// Abort if we are not requesting Onboarding Wizard.
		if ( ! empty( $_REQUEST['page'] ) && 'edd-onboarding-wizard' !== wp_unslash( $_REQUEST['page'] ) ) {
			return false;
		}

		// Stripe calls are marked with onboardingWizard request parameter.
		if ( empty( $_REQUEST['page'] ) && empty( $_REQUEST['onboardingWizard'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Whether the onboarding wizard has started.
	 * Returns true if the onboarding has been marked as completed, too.
	 *
	 * @since 3.1.1
	 * @return bool
	 */
	private function has_onboarding_started() {
		if ( $this->onboarding_started ) {
			return true;
		}

		return get_option( 'edd_onboarding_started', false ) || $this->has_onboarding_been_completed();
	}

	/**
	 * Whether the onboarding wizard has been completed.
	 *
	 * @since 3.1.1
	 * @return bool
	 */
	private function has_onboarding_been_completed() {
		if ( $this->onboarding_completed ) {
			return true;
		}

		return get_option( 'edd_onboarding_completed', false );
	}

	/**
	 * Gets the classes for the "Close and Exit without Saving" button.
	 * If onboarding has already completed, we don't need to show the confirmation again.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	private function get_close_exit_button_classes() {
		$classes = array( 'button', 'button-link' );
		if ( ! $this->has_onboarding_been_completed() ) {
			$classes[] = 'edd-promo-notice__trigger';
		} else {
			$classes[] = 'edd-onboarding__dismiss';
		}

		return $classes;
	}
}
