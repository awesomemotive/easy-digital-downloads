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
	 * Holder of all Onboarding steps.
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

		// Determine current step.
		if( isset( $_GET['current_step'] ) ){
			$this->current_step = sanitize_key( $_GET['current_step'] );
		}

		// If requested step does not exist, abort.
		if ( ! isset( $this->onboarding_steps[ $this->current_step ] ) ) {
			die;
		}

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
	 * Get current step.
	 *
	 * @since 3.2
	 */
	public function get_current_step() {
		return $this->current_step;
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
				<div class="edd-onboarding__steps">
					<ul>
						<li>✅ 1</li>
						<li>2</li>
						<li>3</li>
						<li>4</li>
						<li>5</li>
					</ul>
				</div>

				<div class="edd-onboarding__single-step">
					<div class="edd-onboarding__single-step-inner">
						<h1 class="edd-onboarding__single-step-title">Tell us a little bit about your business.</h1>
						<h2 class="edd-onboarding__single-step-subtitle">Where is your business located? This helps Easy Digital Downloads configure the checkout and receipt templates.</h2>

						<div>

						<table class="form-table" role="presentation">
							<tbody>
								<tr>
									<th scope="row">
										<label for="edd_settings[business_settings]">
										<h3>Business Info</h3>
										</label>
									</th>
									<td><span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<strong>Business Information</strong>: Easy Digital Downloads uses the following business information for things like pre-populating tax fields, and connecting third-party services with the same information."></span></td>
								</tr>
								<tr>
									<th scope="row"><label for="edd_settings[entity_name]">Business Name</label></th>
									<td>
										<input type="text" class=" regular-text" id="edd_settings[entity_name]" name="edd_settings[entity_name]" value="" placeholder="EDD Localhost ZAN" style="background-image: url(&quot;data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAAXNSR0IArs4c6QAAAfBJREFUWAntVk1OwkAUZkoDKza4Utm61iP0AqyIDXahN2BjwiHYGU+gizap4QDuegWN7lyCbMSlCQjU7yO0TOlAi6GwgJc0fT/fzPfmzet0crmD7HsFBAvQbrcrw+Gw5fu+AfOYvgylJ4TwCoVCs1ardYTruqfj8fgV5OUMSVVT93VdP9dAzpVvm5wJHZFbg2LQ2pEYOlZ/oiDvwNcsFoseY4PBwMCrhaeCJyKWZU37KOJcYdi27QdhcuuBIb073BvTNL8ln4NeeR6NRi/wxZKQcGurQs5oNhqLshzVTMBewW/LMU3TTNlO0ieTiStjYhUIyi6DAp0xbEdgTt+LE0aCKQw24U4llsCs4ZRJrYopB6RwqnpA1YQ5NGFZ1YQ41Z5S8IQQdP5laEBRJcD4Vj5DEsW2gE6s6g3d/YP/g+BDnT7GNi2qCjTwGd6riBzHaaCEd3Js01vwCPIbmWBRx1nwAN/1ov+/drgFWIlfKpVukyYihtgkXNp4mABK+1GtVr+SBhJDbBIubVw+Cd/TDgKO2DPiN3YUo6y/nDCNEIsqTKH1en2tcwA9FKEItyDi3aIh8Gl1sRrVnSDzNFDJT1bAy5xpOYGn5fP5JuL95ZjMIn1ya7j5dPGfv0A5eAnpZUY3n5jXcoec5J67D9q+VuAPM47D3XaSeL4AAAAASUVORK5CYII=&quot;); background-repeat: no-repeat; background-attachment: scroll; background-size: 16px 18px; background-position: 98% 50%; cursor: auto;">
										<p class="description"> The official (legal) name of your store. Defaults to Site Title if empty.</p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="edd_settings[entity_type]">Business Type</label></th>
									<td>
										<select id="edd_settings[entity_type]" name="edd_settings[entity_type]" class="" data-placeholder="">
										<option value="individual">Individual</option>
										<option value="company">Company</option>
										</select>
										<p class="description"> Choose "Individual" if you do not have an official/legal business ID, or "Company" if a registered business entity exists.</p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="edd_settings[business_address]">Business Address</label></th>
									<td>
										<input type="text" class=" regular-text" id="edd_settings[business_address]" name="edd_settings[business_address]" value="">
										<p class="description"> </p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="edd_settings[business_address_2]">Business Address (Extra)</label></th>
									<td>
										<input type="text" class=" regular-text" id="edd_settings[business_address_2]" name="edd_settings[business_address_2]" value="">
										<p class="description"> </p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="edd_settings[business_city]">Business City</label></th>
									<td>
										<input type="text" class=" regular-text" id="edd_settings[business_city]" name="edd_settings[business_city]" value="">
										<p class="description"> </p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="edd_settings[business_postal_code]">Business Postal Code</label></th>
									<td>
										<input type="text" class=" medium-text" id="edd_settings[business_postal_code]" name="edd_settings[business_postal_code]" value="">
										<p class="description"> </p>
									</td>
								</tr>
							</tbody>
						</table>

						</div>

					</div>

					<div class="edd-onboarding__single-step-footer">
						<a href="">Back</a>
						<a href="">Save</a>
					</div>

				</div>

			</div>
		</div>
		<?php
	}

}

new EDD_Onboarding();
