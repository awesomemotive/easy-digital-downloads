<?php
/**
 * Onboarding Wizard Class.
 *
 * @package     EDD
 * @subpackage  Onboarding
 * @copyright   Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.2
 */

namespace EDD\Onboarding;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

require_once EDD_PLUGIN_DIR . 'includes/admin/onboarding/helpers.php';
require_once EDD_PLUGIN_DIR . 'includes/admin/onboarding/steps/step-business_info.php';
require_once EDD_PLUGIN_DIR . 'includes/admin/onboarding/steps/step-payment_methods.php';
require_once EDD_PLUGIN_DIR . 'includes/admin/onboarding/steps/step-configure_emails.php';
require_once EDD_PLUGIN_DIR . 'includes/admin/onboarding/steps/step-tools.php';
require_once EDD_PLUGIN_DIR . 'includes/admin/onboarding/steps/step-products.php';

/**
 * EDD_Onboarding Class.
 *
 * Takes care of everything related to Onboarding Wizard.
 *
 * @since 3.2
 */
class OnboardingWizard {

	/**
	 * Current Onboarding step.
	 *
	 * @since 3.2
	 *
	 * @var string
	 */
	private $current_step = 'business_info';

	/**
	 * Current Onboarding step index.
	 *
	 * @since 3.2
	 *
	 * @var int
	 */
	private $current_step_index = 1;

	/**
	 * Onboarding steps.
	 *
	 * @since 3.2
	 *
	 * @var array
	 */
	private $onboarding_steps = array();

	/**
	 * True if user started onboarding process.
	 *
	 * @since 3.2
	 *
	 * @var bool
	 */
	private $onboarding_started = false;

	/**
	 * Class constructor.
	 *
	 * @since 3.2
	 */
	public function __construct() {
		if ( ! is_admin() || wp_doing_cron() ) {
			return;
		}

		// If Onboarding was already completed, abort.
		if ( get_option( 'edd_onboarding_completed', false ) ) {
			return;
		}

		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
		add_action( 'admin_menu', array( $this, 'add_menu_item_clss' ) );

		// Abort if we are not requesting Onboarding Wizard.
		if ( ! isset( $_REQUEST['page'] ) || 'edd-onboarding-wizard' !== wp_unslash( $_REQUEST['page'] ) ) {
			return;
		}

		add_action( 'admin_init', array( $this, 'load_onboarding_wizard' ) );

		// Ajax handlers.
		add_action( 'wp_ajax_edd_onboarding_started', array( $this, 'ajax_onboarding_started' ) );
		add_action( 'wp_ajax_edd_onboarding_load_step', array( $this, 'ajax_onboarding_load_step' ) );
		add_action( 'wp_ajax_edd_onboarding_completed', array( $this, 'ajax_onboarding_completed' ) );
	}

	/**
	 * Add Onboarding Wizard submenu page.
	 *
	 * @since 3.2
	 */
	public function add_menu_item() {
		add_submenu_page( 'edit.php?post_type=download', __( 'Setup', 'easy-digital-downloads' ), __( 'Setup', 'easy-digital-downloads' ), 'manage_shop_settings', 'edd-onboarding-wizard', array( $this, 'onboarding_wizard_sub_page' ) );
	}

	/**
	 * Add class to the Onboarding Wizard subpage menu item.
	 *
	 * @since 3.2
	 */
	public function add_menu_item_clss() {
		global $submenu;
		$edd_submenu     = $submenu[ 'edit.php?post_type=download' ];
		$onboarding_menu = __( 'Setup', 'easy-digital-downloads' );

		if ( empty( $edd_submenu ) ) {
			return;
		}

		foreach ( $edd_submenu as $key => $value ) {
			if ( $onboarding_menu == $value[0] ) {
				$edd_submenu[ $key ][] = 'edd-onboarding__menu-item';
				break;
			}
		}

		$submenu[ 'edit.php?post_type=download' ] = $edd_submenu;
	}

	/**
	 * Determine if we are on Onboarding Wizard screen
	 * and load all of the neccesarry hooks and actions.
	 *
	 * @since 3.2
	 */
	public function load_onboarding_wizard() {

		// Set variables.
		$this->onboarding_started = get_option( 'edd_onboarding_started', false );
		$this->set_onboarding_steps();
		$this->set_current_onboarding_step();

		// We don't want any notices on our screen.
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );

		// Load scripts and styles.
		$this->enqueue_onboarding_scripts();
	}

	/**
	 * Load scripts and styles.
	 *
	 * @since 3.2
	 */
	public function enqueue_onboarding_scripts() {
		wp_enqueue_style( 'edd-admin-onboarding' );
		wp_enqueue_script( 'edd-admin-onboarding' );

		wp_enqueue_style( 'edd-extension-manager' );
		wp_enqueue_script( 'edd-extension-manager' );

		wp_enqueue_media();
		wp_enqueue_editor();

		if ( array_key_exists( 'payment_methods', $this->onboarding_steps ) ) {
			edd_stripe_connect_admin_script( 'download_page_edd-settings' );
		}
	}

	/**
	 * Set onboarding steps.
	 *
	 * @since 3.2
	 */
	public function set_onboarding_steps() {
		$this->onboarding_steps = array(
			'business_info' => array(
				'step_title'    => __( 'Business', 'easy-digital-downloads' ),
				'step_headline' => __( 'Tell us a little bit about your business.', 'easy-digital-downloads' ),
				'step_intro'    => __( 'Where is your business located? This helps Easy Digital Downloads configure the checkout and receipt templates.', 'easy-digital-downloads' ),
				'step_handler'  => 'BusinessInfo',
			),
			'payment_methods' => array(
				'step_title'    => __( 'Payment Methods', 'easy-digital-downloads' ),
				'step_headline' => __( 'Start accepting payments today!', 'easy-digital-downloads' ),
				'step_intro'    => __( 'Connect with Stripe.', 'easy-digital-downloads' ),
				'step_handler'  => 'PaymentMethods',
			),
			'configure_emails' => array(
				'step_title'    => __( 'Emails', 'easy-digital-downloads' ),
				'step_headline' => __( 'Configure your Emails', 'easy-digital-downloads' ),
				'step_intro'    => __( 'So that your dear users will receive good emails.', 'easy-digital-downloads' ),
				'step_handler'  => 'ConfigureEmails',
			),
			'tools' => array(
				'step_title'    => __( 'Tools', 'easy-digital-downloads' ),
				'step_headline' => __( 'Conversion and Optimization tools', 'easy-digital-downloads' ),
				'step_intro'    => __( 'Below, we have selected our recommended tools and features to help boost conversions and optimize your digital store.', 'easy-digital-downloads' ),
				'step_handler'  => 'Tools',
			),
			'products' => array(
				'step_title'    => __( 'Products', 'easy-digital-downloads' ),
				'step_headline' => __( 'What are you going to sell?', 'easy-digital-downloads' ),
				'step_intro'    => __( 'Let’s get started with your first product.', 'easy-digital-downloads' ),
				'step_handler'  => 'Products',
			),
		);

		// If Stripe classes are not available, remove payment methods step.
		if ( ! defined( 'EDD_STRIPE_VERSION' ) ) {
			unset( $this->onboarding_steps['payment_methods'] );
		}

		// Set step index in the array and load ajax handlers.
		$index = 1;
		foreach ( $this->onboarding_steps as $key => $value ) {
			$this->onboarding_steps[ $key ]['step_index'] = $index;
			$index++;

			// Initialize step logic.
			if ( function_exists( 'EDD\\Onboarding\\Steps\\' . $value['step_handler'] . '\\initialize' ) ) {
				call_user_func( 'EDD\\Onboarding\\Steps\\' . $value['step_handler'] . '\\initialize' );
			}
		}
	}

	/**
	 * Set current onboarding step.
	 *
	 * @since 3.2
	 */
	public function set_current_onboarding_step() {
		// If Onboarding hasn't started yet, we force the first default step.
		if ( ! $this->onboarding_started ) {
			return;
		}

		// User is requesting a specific step.
		if( isset( $_GET['current_step'] ) ) {
			$this->current_step = sanitize_key( $_GET['current_step'] );
		} else {
			$this->current_step = sanitize_key( get_option( 'edd_onboarding_latest_step', $this->current_step ) );
		}

		// If requested step does not exist, abort.
		if ( ! isset( $this->onboarding_steps[ $this->current_step ] ) ) {
			wp_die( __( 'Unknown Onboarding Step.', 'easy-digital-downloads' ), __( 'Onboarding Wizard', 'easy-digital-downloads' ), 404 );
		}

		$this->current_step_index = $this->onboarding_steps[ $this->current_step ]['step_index'];
		update_option( 'edd_onboarding_latest_step', $this->current_step );
	}

	/**
	 * Get previous step.
	 *
	 * @since 3.2
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
	 * @since 3.2
	 */
	public function get_current_step() {
		return $this->current_step;
	}

	/**
	 * Get current step details.
	 *
	 * @since 3.2
	 */
	public function get_current_step_details() {
		return $this->onboarding_steps[ $this->get_current_step() ];
	}

	/**
	 * Get next step.
	 *
	 * @since 3.2
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
	 * @since 3.2
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
	 * @since 3.2
	 */
	public function onboarding_wizard_sub_page() {
		?>
		<?php wp_nonce_field( 'edd_onboarding_wizard' ); ?>
		<div class="edd-onboarding">
			<div class="edd-onboarding__logo">
				<img src="<?php echo esc_url( EDD_PLUGIN_URL . '/assets/images/logo-edd-dark.svg' ); ?>">
			</div>
			<div class="edd-onboarding__wrapper">
				<div class="edd-onboarding__loading" style="display: none;">
					<div class="edd-onboarding__loading-content-wrapper">
						<img src="<?php echo esc_url( EDD_PLUGIN_URL . '/assets/images/oval-ajax-loader.svg' ); ?>" alt="">
						<div class="edd-onboarding__loading-status"></div>
					</div>
				</div>
				<div class="edd-onboarding__current-step">
					<?php $this->load_step_view(); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Load requested step HTML.
	 *
	 * @since 3.2
	 */
	private function load_step_view() {
		$current_step_details = $this->get_current_step_details();
		$pagination           = $this->get_step_pagination();

		$onboarding_initial_style = ( ! $this->onboarding_started ) ? ' style="display:none;"' : '';
		?>
		<input type="hidden" class="edd-onboarding_current-previous-step" value="<?php echo esc_attr( $this->get_previous_step() );?>">
		<input type="hidden" class="edd-onboarding_current-step" value="<?php echo esc_attr( $this->get_current_step() );?>">
		<input type="hidden" class="edd-onboarding_current-next-step" value="<?php echo esc_attr( $this->get_next_step() );?>">

		<!-- STEPS NAVIGATION -->
		<div class="edd-onboarding__steps"<?php echo $onboarding_initial_style; ?>>
			<ul>
				<?php
				foreach( $this->onboarding_steps as $step_key => $step ) :
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
				<?php echo call_user_func( 'EDD\\Onboarding\\Steps\\' . $current_step_details['step_handler'] . '\\step_html' );?>
			</div>
			<div class="edd-onboarding__single-step-footer">
				<div>
					<?php if ( $pagination['previous'] ) : ?>
						<a href="#" class="edd-onboarding__button-back">← <?php echo esc_html( __( 'Go Back', 'easy-digital-downloads' ) ); ?></a>
					<?php endif; ?>
				</div>
				<div>
					<a href="#" class="button button-secondary edd-onboarding__button-supportive edd-onboarding__button-skip-step"><?php echo esc_html( __( 'Skip this step', 'easy-digital-downloads' ) ); ?></a>
					<a href="#" class="button button-primary edd-onboarding__button-save-step"><?php echo esc_html( __( 'Save & Continue', 'easy-digital-downloads' ) ); ?></a>
				</div>
			</div>
		</div>
		<div class="edd-onboarding__close-and-exit-wrapper"<?php echo $onboarding_initial_style; ?>>
			<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download' ) ); ?>" class="edd-onboarding__close-and-exit"><?php echo esc_html( __( 'Close and Exit Without Saving', 'easy-digital-downloads' ) ); ?></a>
		</div>
		<?php
	}

	/**
	 * Ajax callback when user started the Onboarding flow.
	 *
	 * @since 3.2
	 */
	public function ajax_onboarding_started() {
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'edd_onboarding_wizard' ) ) {
			exit();
		}

		update_option( 'edd_onboarding_started', true );
		exit;
	}

	/**
	 * Ajax callback for loading single step view.
	 *
	 * @since 3.2
	 */
	public function ajax_onboarding_load_step() {
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'edd_onboarding_wizard' ) ) {
			exit();
		}

		ob_start();
		$this->load_step_view();
		echo ob_get_clean();
		exit;
	}

	/**
	 * Ajax callback for completing the Onboarding.
	 *
	 * @since 3.2
	 */
	public function ajax_onboarding_completed() {
		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'edd_onboarding_wizard' ) ) {
			exit();
		}

		update_option( 'edd_onboarding_completed', true );
		update_option( 'edd_tracking_notice', true );
		exit;
	}
}

new OnboardingWizard();
