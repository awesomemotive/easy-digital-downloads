<?php
/**
 * Onboarding Wizard Class.
 *
 * @package     EDD
 * @subpackage  Emails
 * @copyright   Copyright (c) 2022, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.1
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Onboarding Class.
 *
 * Takes care of everything related to Onboarding Wizard.
 *
 * @since 3.2
 */
class EDD_Onboarding {

	/**
	 * Current Onboarding step.
	 */
	private $current_step = 'business_info';

	/**
	 * Current Onboarding step index.
	 */
	private $current_step_index = 1;

	/**
	 * Onboarding steps.
	 */
	private $onboarding_steps = array();

	/**
	 * Class constructor.
	 *
	 * @since 3.2
	 */
	public function __construct() {
		// If Onboarding Wizard was already completed, we can abort.
		if ( ! is_admin() || wp_doing_cron() ) {
			return;
		}

		add_action( 'admin_menu', array( $this, 'add_menu_item' ), 99999 );
		add_action( 'admin_head', array( $this, 'hide_menu_item' ) );
		add_action( 'admin_init', array( $this, 'load_onboarding_wizard' ) );
	}

	/**
	 * Add Onboarding Wizard submenu page.
	 *
	 * @since 3.2
	 */
	public function add_menu_item() {
		add_submenu_page( 'edit.php?post_type=download', __( 'Onboarding', 'easy-digital-downloads' ), __( 'Onboarding', 'easy-digital-downloads' ), 'manage_shop_settings', 'edd-onboarding-wizard', array( $this, 'onboarding_wizard_sub_page' ) );
	}

	/**
	 * Hide Onboarding Wizard submenu page from the menu.
	 *
	 * @since 3.2
	 */
	public function hide_menu_item() {
		remove_submenu_page( 'edit.php?post_type=download', 'edd-onboarding' );
	}

	/**
	 * Determine if we are on Onboarding Wizard screen
	 * and load all of the neccesarry hooks and actions.
	 *
	 * @since 3.2
	 */
	public function load_onboarding_wizard() {
		if ( ! isset( $_GET['page'] ) || 'edd-onboarding-wizard' !== wp_unslash( $_GET['page'] ) ) {
			return;
		}

		// Set onboarding steps.
		$this->set_onboarding_steps();

		// Determine current step.
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
		wp_enqueue_media();
	}

	/**
	 * Set onboarding steps.
	 *
	 * @since 3.2
	 */
	public function set_onboarding_steps() {
		$this->onboarding_steps = array(
			'business_info' => array(
				'step_title'          => __( 'Business', 'easy-digital-downloads' ),
				'step_headline'       => __( 'Tell us a little bit about your business.', 'easy-digital-downloads' ),
				'step_intro'          => __( 'Where is your business located? This helps Easy Digital Downloads configure the checkout and receipt templates.', 'easy-digital-downloads' ),
				'step_view'           => '',
				'step_submit_handler' => '',
			),
			'payment_methods' => array(
				'step_title'          => __( 'Payment Methods', 'easy-digital-downloads' ),
				'step_headline'       => __( 'Start accepting payments today!', 'easy-digital-downloads' ),
				'step_intro'          => __( 'Connect with Stripe.', 'easy-digital-downloads' ),
				'step_view'           => '',
				'step_submit_handler' => '',
			),
			'configure_emails' => array(
				'step_title'          => __( 'Emails', 'easy-digital-downloads' ),
				'step_headline'       => __( 'Configure your Emails', 'easy-digital-downloads' ),
				'step_intro'          => __( 'So that your dear users will receive good emails.', 'easy-digital-downloads' ),
				'step_view'           => '',
				'step_submit_handler' => '',
			),
			'tools' => array(
				'step_title'          => __( 'Tools', 'easy-digital-downloads' ),
				'step_headline'       => __( 'Conversion and Optimization tools', 'easy-digital-downloads' ),
				'step_intro'          => __( 'Below, we have selected our recommended tools and features to help boost conversions and optimize your digital store.', 'easy-digital-downloads' ),
				'step_view'           => '',
				'step_submit_handler' => '',
			),
			'products' => array(
				'step_title'          => __( 'Products', 'easy-digital-downloads' ),
				'step_headline'       => __( 'What are you going to sell?', 'easy-digital-downloads' ),
				'step_intro'          => __( 'Let’s get started with your first product.', 'easy-digital-downloads' ),
				'step_view'           => '',
				'step_submit_handler' => '',
			),
		);

		// Set their index in the array.
		$index = 1;
		foreach ( $this->onboarding_steps as $key => $value ) {
			$this->onboarding_steps[ $key ]['step_index'] = $index;
			$index++;
		}
	}

	/**
	 * Set current onboarding step.
	 *
	 * @since 3.2
	 */
	public function set_current_onboarding_step() {
		if( isset( $_GET['current_step'] ) ){
			$this->current_step      = sanitize_key( $_GET['current_step'] );
			// If requested step does not exist, abort.
			if ( ! isset( $this->onboarding_steps[ $this->current_step ] ) ) {
				wp_die( __( 'Unknown Onboarding Step.', 'easy-digital-downloads' ), __( 'Onboarding', 'easy-digital-downloads' ), 404 );
			}

			$this->current_step_index = $this->onboarding_steps[ $this->current_step ]['step_index'];
		}
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
		return $this->onboarding_steps[ $this->current_step ];
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
		<style>
			#edd-header {
				display: none;
			}
		</style>
		<div class="edd-onboarding">
			<div class="edd-onboarding__logo">
				<img src="<?php echo esc_url( EDD_PLUGIN_URL . '/assets/images/logo-edd-dark.svg' ); ?>">
			</div>
			<div class="edd-onboarding__wrapper">
				<div class="edd-loader">
					LOADING!
				</div>
				<div class="edd-onboarding__current-step">
					<?php $this->load_step_view(); ?>
				</div>
			</div>
		</div>
		<?php
	}


	/**
	 * Onboarding Wizard subpage screen.
	 *
	 * @since 3.2
	 */
	public function load_step_view() {
		$current_step_details = $this->get_current_step_details();
		$pagination           = $this->get_step_pagination();
		?>
		<div class="edd-onboarding__steps">
			<ul>
				<?php foreach( $this->onboarding_steps as $step_key => $step ):
					$step_url = edd_get_admin_url(
						array(
							'post_type'    => 'download',
							'page'         => 'edd-onboarding-wizard',
							'current_step' => sanitize_key( $step_key ),
						)
					);

					$classes = array();
					// Determine if this step is active.
					if ( $this->current_step_index === $step['step_index'] ) {
						$classes[] = 'active-step';
					}
					// Determine if this step is active.
					if ( $this->current_step_index > $step['step_index'] ) {
						$classes[] = 'completed-step';
					}
					?>
					<li class="<?php echo implode( ' ', array_map( 'esc_attr', $classes ) ) ?>">
						<a href="<?php echo esc_url( $step_url );?>">(<?php echo esc_html( $step['step_index'] ); ?>) - <?php echo esc_html( $step['step_title'] ); ?></a>
					</li>
					<?php
				endforeach;
				?>
			</ul>
		</div>

		<div class="edd-onboarding__single-step">
			<div class="edd-onboarding__single-step-inner">
				<span class="edd-onboarding__steps-indicator"><?php echo esc_html( __( 'Step', 'easy-digital-downloads' ) ); ?> <?php echo $this->current_step_index;?> / <?php echo count( $this->onboarding_steps ); ?></span>
				<h1 class="edd-onboarding__single-step-title"><?php echo esc_html( $current_step_details['step_headline'] ); ?></h1>
				<h2 class="edd-onboarding__single-step-subtitle"><?php echo esc_html( $current_step_details['step_intro'] ); ?></h2>

				<?php include EDD_PLUGIN_DIR . "includes/admin/onboarding/views/step-{$this->current_step}.php";?>

			</div>

			<div class="edd-onboarding__single-step-footer">
				<?php if ( $pagination['previous'] ) : ?>
					<a href="">← <?php echo esc_html( __( 'Go Back', 'easy-digital-downloads' ) ); ?></a>
				<?php endif;?>

				<a class="button button-secondary" href=""><?php echo esc_html( __( 'Skip this step', 'easy-digital-downloads' ) ); ?></a>
				<a class="button button-primary" href=""><?php echo esc_html( __( 'Save & Continue', 'easy-digital-downloads' ) ); ?></a>

				<br>
				<br>


			</div>

		</div>

		<div style="text-align: center;"><a href="" style="color: black; opacity: 0.5;"><?php echo esc_html( __( 'Close and Exit Without Saving', 'easy-digital-downloads' ) ); ?></a></div>

		<pre><?php print_r( $this->onboarding_steps );?></pre>
			<pre><?php print_r( $this->current_step );?></pre>
			<pre><?php print_r( $this->current_step_index );?></pre>
			<pre><?php print_r( array_keys( $this->onboarding_steps ) ); ?></pre>
		<?php
	}

}

new EDD_Onboarding();
