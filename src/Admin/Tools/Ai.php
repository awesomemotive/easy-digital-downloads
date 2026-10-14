<?php
/**
 * AI tab.
 *
 * @package     EDD\Admin\Tools
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.1
 */

namespace EDD\Admin\Tools;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Abilities\Loader as Abilities;
use EDD\Admin\Extensions\Extension_Manager;
use EDD\EventManagement\SubscriberInterface;

/**
 * AI tab.
 *
 * Promotes WPVibe as the recommended way to connect the store to an AI
 * assistant, and hosts the toggle which decides whether those assistants may
 * change store data.
 *
 * @since 3.7.1
 */
class Ai implements SubscriberInterface {

	use \EDD\Admin\Settings\Traits\AjaxToggle;

	/**
	 * The WPVibe plugin basename.
	 *
	 * @since 3.7.1
	 * @var string
	 */
	const WPVIBE_PLUGIN = 'vibe-ai/vibe-ai.php';

	/**
	 * The WPVibe download URL on WordPress.org.
	 *
	 * @since 3.7.1
	 * @var string
	 */
	const WPVIBE_DOWNLOAD_URL = 'https://downloads.wordpress.org/plugin/vibe-ai.latest-stable.zip';

	/**
	 * The extension manager, which installs and activates WPVibe.
	 *
	 * @since 3.7.1
	 * @var Extension_Manager|null
	 */
	private $manager;

	/**
	 * Whether WPVibe is active, resolved on first use.
	 *
	 * @since 3.7.1
	 * @var bool|null
	 */
	private $wpvibe_active;

	/**
	 * Get the subscribed events.
	 *
	 * @since 3.7.1
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		/**
		 * Without the Abilities API there is nothing to describe and nothing for the
		 * write setting to act on, so the tab body stays unhooked. This guard also
		 * withholds `edd_toggle_setting_handlers`, which registers this class as an
		 * AJAX toggle handler for `Abilities::WRITE_SETTING`, so it has to stay in
		 * place even though `Screen::get_active_tab()` now validates the tab itself.
		 */
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return array();
		}

		return array(
			'edd_tools_tab_ai'            => 'render',
			'edd_toggle_setting_handlers' => 'register_handler',
			'admin_enqueue_scripts'       => 'enqueue',
		);
	}

	/**
	 * Get the settings this handler allows to be toggled via AJAX.
	 *
	 * @since 3.7.1
	 * @return array
	 */
	public static function get_allowed_ajax_settings(): array {
		return array( Abilities::WRITE_SETTING );
	}

	/**
	 * Render the AI tab.
	 *
	 * @since 3.7.1
	 * @return void
	 */
	public function render(): void {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return;
		}
		?>
		<div class="edd-ai-mcp">
			<?php
			$this->render_hero();
			$this->render_capabilities();
			?>
		</div>
		<?php
	}

	/**
	 * Enqueue the styles and the extension manager assets.
	 *
	 * Runs on admin_enqueue_scripts so the stylesheet is in the head; enqueuing
	 * from render() prints it after the dark hero has already painted.
	 *
	 * @since 3.7.1
	 * @return void
	 */
	public function enqueue(): void {
		if ( ! $this->is_current_tab() ) {
			return;
		}

		// Registers and enqueues the install/activate script this tab's buttons rely on.
		$this->manager()->enqueue();

		wp_enqueue_style( 'edd-admin-tools-ai-mcp' );
	}

	/**
	 * Render the hero: the pitch, the WPVibe button, the write toggle, and the client list.
	 *
	 * @since 3.7.1
	 * @return void
	 */
	private function render_hero(): void {
		?>
		<section class="edd-ai-mcp__hero">
			<div class="edd-ai-mcp__copy">
				<p class="edd-ai-mcp__eyebrow"><?php esc_html_e( 'WordPress Abilities API + Easy Digital Downloads', 'easy-digital-downloads' ); ?></p>
				<h2 class="edd-ai-mcp__title"><?php esc_html_e( 'Run Your Store With Your Favorite AI', 'easy-digital-downloads' ); ?></h2>
				<p class="edd-ai-mcp__lede">
					<?php
					echo wp_kses(
						sprintf(
							/* translators: %s: Link to the WPVibe website */
							__( 'Connect your store to AI assistants like Claude, ChatGPT, and Cursor, then ask them about your sales, look up a customer, or build a discount in plain language. No exports, no copy-pasting. Connect them with the free %s plugin.', 'easy-digital-downloads' ),
							sprintf(
								'<a href="%s" target="_blank" rel="noopener noreferrer"><strong>WPVibe</strong></a>',
								esc_url( 'https://wpvibe.ai/' )
							)
						),
						array(
							'a'      => array(
								'href'   => array(),
								'target' => array(),
								'rel'    => array(),
							),
							'strong' => array(),
						)
					);
					?>
				</p>

				<div class="edd-ai-mcp__cta">
					<?php
					$this->render_wpvibe_button();
					$this->render_toggle();
					?>
				</div>

				<p class="edd-ai-mcp__note">
					<?php esc_html_e( 'Everything below is registered with the WordPress Abilities API, so a store already running an AI connector or MCP bridge can use it without adding WPVibe. WPVibe is the one we recommend for a store that does not have one yet.', 'easy-digital-downloads' ); ?>
				</p>

				<?php if ( ! $this->is_wpvibe_active() && ! $this->manager()->can_install( 'plugin' ) ) : ?>
					<p class="edd-ai-mcp__note">
						<?php esc_html_e( 'This site does not allow plugins to be installed from the dashboard, so WPVibe has to be added manually.', 'easy-digital-downloads' ); ?>
					</p>
				<?php endif; ?>
			</div>

			<?php $this->render_clients(); ?>
		</section>
		<?php
	}

	/**
	 * Render the WPVibe install, activate, or set up button.
	 *
	 * The two steps are what the extension manager script swaps between: it hides
	 * the button it just acted on and reveals the next step.
	 *
	 * @since 3.7.1
	 * @return void
	 */
	private function render_wpvibe_button(): void {
		if ( $this->is_wpvibe_connected() ) {
			$this->render_wpvibe_connected();

			return;
		}

		$is_active = $this->is_wpvibe_active();
		?>
		<div class="edd-extension-manager__group edd-extension-manager__actions">
			<?php if ( ! $is_active ) : ?>
				<div class="edd-extension-manager__step">
					<?php $this->manager()->button( $this->get_button_parameters() ); ?>
				</div>
			<?php endif; ?>

			<div class="edd-extension-manager__step"<?php echo $is_active ? '' : ' style="display:none;"'; ?>>
				<?php $this->manager()->link( $this->get_link_parameters() ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the connected state, in place of the set-up button.
	 *
	 * @since 3.7.1
	 *
	 * @return void
	 */
	private function render_wpvibe_connected(): void {
		?>
		<div class="edd-extension-manager__group edd-extension-manager__actions">
			<div class="edd-extension-manager__step">
				<p class="edd-ai-mcp__connected">
					<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
					<?php esc_html_e( 'Connected', 'easy-digital-downloads' ); ?>
				</p>
			</div>

			<div class="edd-extension-manager__step">
				<?php $this->manager()->link( $this->get_link_parameters( true ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Get the parameters for the install or activate button.
	 *
	 * @since 3.7.1
	 * @return array
	 */
	private function get_button_parameters(): array {
		if ( ! $this->manager()->is_plugin_installed( self::WPVIBE_PLUGIN ) ) {
			if ( ! $this->manager()->can_install( 'plugin' ) ) {
				return array(
					'button_class' => 'button-primary edd-ai-mcp__wpvibe',
					'button_text'  => __( 'Get WPVibe', 'easy-digital-downloads' ),
					'href'         => 'https://wordpress.org/plugins/vibe-ai/',
					'new_tab'      => true,
				);
			}

			return array(
				'button_class' => 'button-primary edd-ai-mcp__wpvibe',
				'plugin'       => self::WPVIBE_DOWNLOAD_URL,
				'action'       => 'install',
				'type'         => 'plugin',
				'button_text'  => __( 'Install & Activate WPVibe', 'easy-digital-downloads' ),
			);
		}

		return array(
			'button_class' => 'button-primary edd-ai-mcp__wpvibe',
			'plugin'       => self::WPVIBE_PLUGIN,
			'action'       => 'activate',
			'type'         => 'plugin',
			'button_text'  => __( 'Activate WPVibe', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Get the parameters for the link to WPVibe once it is active.
	 *
	 * @since 3.7.1
	 *
	 * @param bool $is_connected Whether the store is already connected to WPVibe.
	 * @return array
	 */
	private function get_link_parameters( bool $is_connected = false ): array {
		return array(
			'button_class' => 'button-primary edd-ai-mcp__wpvibe',
			'button_text'  => $is_connected
				? __( 'Manage WPVibe', 'easy-digital-downloads' )
				: __( 'Set Up WPVibe', 'easy-digital-downloads' ),
			'href'         => admin_url( 'admin.php?page=vibe-ai' ),
		);
	}

	/**
	 * Render the write-access toggle.
	 *
	 * @since 3.7.1
	 * @return void
	 */
	private function render_toggle(): void {
		$toggle = new \EDD\HTML\CheckboxToggle(
			array(
				'label'   => __( 'Allow AI to Change Store Data', 'easy-digital-downloads' ),
				'name'    => Abilities::WRITE_SETTING,
				'current' => Abilities::writes_enabled(),
				'tooltip' => array(
					'title'   => __( 'Changing store data', 'easy-digital-downloads' ),
					'content' => __( 'Off, an assistant can only look things up. On, it can create and edit orders, customers, products, and discounts, and send receipts. It is still limited to what the connected user account can do in the dashboard.', 'easy-digital-downloads' ),
				),
				'data'    => array(
					'setting' => Abilities::WRITE_SETTING,
					'nonce'   => wp_create_nonce( 'edd-toggle-nonce' ),
				),
			)
		);
		?>
		<div class="edd-ai-mcp__toggle">
			<?php $toggle->output(); ?>
		</div>
		<?php
	}

	/**
	 * Render the list of AI clients which can talk to the store.
	 *
	 * @since 3.7.1
	 * @return void
	 */
	private function render_clients(): void {
		$clients = array(
			'claude'  => __( 'Claude', 'easy-digital-downloads' ),
			'chatgpt' => __( 'ChatGPT', 'easy-digital-downloads' ),
			'cursor'  => __( 'Cursor', 'easy-digital-downloads' ),
		);
		?>
		<aside class="edd-ai-mcp__clients" aria-label="<?php esc_attr_e( 'Supported AI clients', 'easy-digital-downloads' ); ?>">
			<?php foreach ( $clients as $slug => $label ) : ?>
				<div class="edd-ai-mcp__client">
					<span class="edd-ai-mcp__client-icon">
						<img src="<?php echo esc_url( $this->get_image_url( $slug ) ); ?>" alt="" role="presentation" />
					</span>
					<span class="edd-ai-mcp__client-label"><?php echo esc_html( $label ); ?></span>
				</div>
			<?php endforeach; ?>
			<p class="edd-ai-mcp__clients-note"><?php esc_html_e( '+ Any MCP Client', 'easy-digital-downloads' ); ?></p>
		</aside>
		<?php
	}

	/**
	 * Render the capability cards.
	 *
	 * @since 3.7.1
	 * @return void
	 */
	private function render_capabilities(): void {
		?>
		<section class="edd-ai-mcp__capabilities">
			<header class="edd-ai-mcp__capabilities-head">
				<h3 class="edd-ai-mcp__capabilities-title"><?php esc_html_e( 'Everything Easy Digital Downloads Can Do With AI', 'easy-digital-downloads' ); ?></h3>
				<?php $summary = $this->get_summary(); ?>
				<?php if ( $summary ) : ?>
					<p class="edd-ai-mcp__docs description"><?php echo esc_html( $summary ); ?></p>
				<?php endif; ?>
			</header>

			<div class="edd-ai-mcp__cards">
				<?php foreach ( $this->get_cards() as $card ) : ?>
					<article class="edd-ai-mcp__card">
						<header class="edd-ai-mcp__card-head">
							<span class="edd-ai-mcp__card-icon">
								<span class="dashicons dashicons-<?php echo esc_attr( $card['icon'] ); ?>" aria-hidden="true"></span>
							</span>
							<h4 class="edd-ai-mcp__card-title"><?php echo esc_html( $card['title'] ); ?></h4>
						</header>

						<ul class="edd-ai-mcp__card-list">
							<?php foreach ( $card['items'] as $item ) : ?>
								<li>
									<span class="edd-ai-mcp__card-dot" aria-hidden="true"></span>
									<span><?php echo esc_html( $item ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</article>
				<?php endforeach; ?>
			</div>

			<?php $this->render_disclosure(); ?>
		</section>
		<?php
	}

	/**
	 * Render what a connected assistant can read without the write toggle.
	 *
	 * @since 3.7.1
	 * @return void
	 */
	private function render_disclosure(): void {
		$disclosures = $this->get_disclosures();
		if ( empty( $disclosures ) ) {
			return;
		}
		?>
		<footer class="edd-ai-mcp__disclosure">
			<h4><?php esc_html_e( 'What a Connected Assistant Can See', 'easy-digital-downloads' ); ?></h4>
			<?php foreach ( $disclosures as $disclosure ) : ?>
				<p><?php echo esc_html( $disclosure ); ?></p>
			<?php endforeach; ?>
		</footer>
		<?php
	}

	/**
	 * Get the paragraphs disclosing what a connected assistant can read.
	 *
	 * @since 3.7.1
	 * @return array
	 */
	private function get_disclosures(): array {
		/**
		 * Filters the paragraphs disclosing what a connected assistant can read.
		 *
		 * Extensions add a paragraph for the data their own read abilities expose,
		 * keyed by ability slug, as plain text. Anything empty is skipped.
		 *
		 * Only disclose what the store can currently expose: check the ability is
		 * available first, via EDD\Abilities\Registry::get().
		 *
		 * @since 3.7.1
		 *
		 * @param array $disclosures The disclosure paragraphs, keyed by ability slug where one applies.
		 */
		$disclosures = (array) apply_filters( 'edd/ai/mcp_disclosures', $this->get_core_disclosures() );

		return array_filter( array_map( 'strval', $disclosures ) );
	}

	/**
	 * Get the disclosures for the data EDD itself exposes.
	 *
	 * @since 3.7.1
	 * @return array
	 */
	private function get_core_disclosures(): array {
		return array(
			'reads'   => __( 'A connected assistant can read customer names, email addresses, and mailing addresses; order details and the email address on each order; file download records, including the IP address each download came from; and who your store emails were sent to.', 'easy-digital-downloads' ),
			'account' => __( 'It reads that data as the account you connect it with, and sees only what that account can already see in the dashboard.', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Get the capability cards.
	 *
	 * @since 3.7.1
	 * @return array
	 */
	private function get_cards(): array {
		/**
		 * Filters the cards describing what the store can do with AI.
		 *
		 * Extensions add a card for their own abilities. Each card is an array of
		 * `icon` (a dashicon slug, without the `dashicons-` prefix), `title`, and
		 * `items` (a list of short plain-language strings). A card without a title
		 * or without items is skipped.
		 *
		 * Only advertise what the store can currently do: check the ability is
		 * available first, via EDD\Abilities\Registry::get().
		 *
		 * @since 3.7.1
		 *
		 * @param array $cards The cards to display.
		 */
		$cards = (array) apply_filters( 'edd/ai/mcp_cards', $this->get_core_cards() );

		return array_filter( array_map( array( $this, 'parse_card' ), $cards ) );
	}

	/**
	 * Normalize a single card, discarding anything unusable.
	 *
	 * @since 3.7.1
	 *
	 * @param mixed $card The card to parse.
	 * @return array|false The parsed card, or false when it cannot be displayed.
	 */
	private function parse_card( $card ) {
		if ( ! is_array( $card ) || empty( $card['title'] ) || empty( $card['items'] ) ) {
			return false;
		}

		$items = array_filter( array_map( 'strval', (array) $card['items'] ) );
		if ( empty( $items ) ) {
			return false;
		}

		return array(
			'icon'  => ! empty( $card['icon'] ) ? sanitize_html_class( $card['icon'] ) : 'admin-generic',
			'title' => (string) $card['title'],
			'items' => $items,
		);
	}

	/**
	 * Get the cards for the abilities EDD itself provides.
	 *
	 * @since 3.7.1
	 * @return array
	 */
	private function get_core_cards(): array {
		return array(
			array(
				'icon'  => 'cart',
				'title' => __( 'Orders', 'easy-digital-downloads' ),
				'items' => array(
					__( 'Look up an order, or list them by status, customer, or date', 'easy-digital-downloads' ),
					__( 'Resend an order receipt to the customer', 'easy-digital-downloads' ),
					__( 'Read and add internal order notes', 'easy-digital-downloads' ),
				),
			),
			array(
				'icon'  => 'groups',
				'title' => __( 'Customers', 'easy-digital-downloads' ),
				'items' => array(
					__( 'Find a customer by email address or name', 'easy-digital-downloads' ),
					__( 'Create a customer or update their details', 'easy-digital-downloads' ),
					__( 'Read and add internal customer notes', 'easy-digital-downloads' ),
				),
			),
			array(
				'icon'  => 'tag',
				'title' => __( 'Products & Discounts', 'easy-digital-downloads' ),
				'items' => array(
					__( 'Create a product or change its price and status', 'easy-digital-downloads' ),
					__( 'Build a percentage or flat-rate discount code, inactive by default', 'easy-digital-downloads' ),
					__( 'Review which discounts are running', 'easy-digital-downloads' ),
				),
			),
			array(
				'icon'  => 'chart-bar',
				'title' => __( 'Reports', 'easy-digital-downloads' ),
				'items' => array(
					__( 'Ask how the store performed this month or last', 'easy-digital-downloads' ),
					__( 'Break earnings down for a single product', 'easy-digital-downloads' ),
					__( 'Check store health, tax rates, and email logs', 'easy-digital-downloads' ),
				),
			),
		);
	}

	/**
	 * Get a sentence summarizing what this store describes to an assistant.
	 *
	 * @since 3.7.1
	 * @return string The summary as plain text, or an empty string when nothing is registered.
	 */
	private function get_summary(): string {
		$counts    = self::get_ability_counts();
		$sentences = array();

		if ( $counts['read'] ) {
			$sentences[] = sprintf(
				/* translators: %d: the number of things AI can look up. */
				_n(
					'AI can look up %d type of store information.',
					'AI can look up %d types of store information.',
					$counts['read'],
					'easy-digital-downloads'
				),
				absint( $counts['read'] )
			);
		}

		if ( $counts['write'] ) {
			$sentences[] = sprintf(
				/* translators: %d: the number of changes AI can make. */
				_n(
					'Allow the AI to change store data and it can make %d type of change.',
					'Allow the AI to change store data and it can make %d types of changes.',
					$counts['write'],
					'easy-digital-downloads'
				),
				absint( $counts['write'] )
			);
		}

		return implode( ' ', $sentences );
	}

	/**
	 * Count the registered EDD abilities by whether they read or write.
	 *
	 * @since 3.7.1
	 * @return array
	 */
	private static function get_ability_counts(): array {
		$counts = array(
			'read'  => 0,
			'write' => 0,
		);

		if ( ! function_exists( 'wp_get_abilities' ) ) {
			return $counts;
		}

		foreach ( wp_get_abilities() as $ability ) {
			// Count by category, so an extension's abilities are included whatever they are named.
			$category = $ability->get_category();
			$slug     = is_object( $category ) ? $category->get_slug() : (string) $category;

			if ( Abilities::CATEGORY !== $slug ) {
				continue;
			}

			$annotations = $ability->get_meta()['annotations'] ?? array();
			$key         = empty( $annotations['readonly'] ) ? 'write' : 'read';

			++$counts[ $key ];
		}

		return $counts;
	}

	/**
	 * Whether WPVibe is active on this site.
	 *
	 * @since 3.7.1
	 * @return bool
	 */
	private function is_wpvibe_active(): bool {
		if ( is_null( $this->wpvibe_active ) ) {
			$this->wpvibe_active = $this->manager()->is_plugin_active( self::WPVIBE_PLUGIN );
		}

		return $this->wpvibe_active;
	}

	/**
	 * Whether WPVibe reports this site as connected to an assistant.
	 *
	 * WPVibe records the last authenticated request and counts a site as
	 * connected for 30 days after it, which is the same rule its own status
	 * badge uses. Guarded because the class is not part of its public API.
	 *
	 * @since 3.7.1
	 *
	 * @return bool
	 */
	private function is_wpvibe_connected(): bool {
		if ( ! $this->is_wpvibe_active() ) {
			return false;
		}

		if ( ! class_exists( '\WPVibe_White_Label' ) || ! method_exists( '\WPVibe_White_Label', 'site_is_connected' ) ) {
			return false;
		}

		return (bool) \WPVibe_White_Label::site_is_connected();
	}

	/**
	 * Gets the extension manager, building it on first use.
	 *
	 * Its constructor builds a Pass_Manager, which reads the pass license and a
	 * non-autoloaded option, so it is not built for admin requests that never
	 * reach this tab.
	 *
	 * @since 3.7.1
	 * @return Extension_Manager
	 */
	private function manager(): Extension_Manager {
		if ( is_null( $this->manager ) ) {
			$this->manager = new Extension_Manager();
		}

		return $this->manager;
	}

	/**
	 * Whether the request is for this tab, by a user who can see it.
	 *
	 * @since 3.7.1
	 * @return bool
	 */
	private function is_current_tab(): bool {
		if ( ! edd_is_admin_page( 'tools' ) || ! current_user_can( 'manage_shop_settings' ) ) {
			return false;
		}

		return 'ai' === filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS );
	}

	/**
	 * Get the URL for one of the tab's images.
	 *
	 * @since 3.7.1
	 *
	 * @param string $slug The image slug, without the extension.
	 * @return string
	 */
	private function get_image_url( string $slug ): string {
		return EDD_PLUGIN_URL . 'assets/images/ai-mcp/' . $slug . '.svg';
	}
}
