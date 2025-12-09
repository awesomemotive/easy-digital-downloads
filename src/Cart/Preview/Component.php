<?php
/**
 * Cart Preview Component
 *
 * Main orchestrator class for the cart preview feature.
 *
 * @package     EDD\Cart\Preview
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.2
 */

namespace EDD\Cart\Preview;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;

/**
 * Component class
 *
 * Orchestrates the cart preview feature initialization and coordination.
 *
 * @since 3.6.2
 */
class Component implements SubscriberInterface {
	use \EDD\Admin\Settings\Traits\Helpers;

	/**
	 * Assets instance.
	 *
	 * @since 3.6.2
	 * @var Assets
	 */
	private $assets;

	/**
	 * Constructor.
	 *
	 * @since 3.6.2
	 */
	public function __construct() {
		$this->assets = new Assets();
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.6.2
	 * @return array
	 */
	public static function get_subscribed_events() {
		$events = array(
			'edd_cart_preview_button' => 'render_button_setting',
		);
		if ( ! Utility::is_enabled() ) {
			return $events;
		}

		$events['wp_enqueue_scripts'] = 'enqueue_assets';

		return $events;
	}

	/**
	 * Render the cart preview button setting.
	 *
	 * @since 3.6.2
	 * @param array $args The arguments for the setting.
	 * @return void
	 */
	public function render_button_setting( $args ) {
		?>
		<div class="edd-form-row">
			<div class="edd-form-group">
				<?php
				printf( '<label for="cart_preview_button_size">%s</label>', esc_html__( 'Button Size', 'easy-digital-downloads' ) );
				$select = new \EDD\HTML\Select(
					array(
						'name'              => 'edd_settings[cart_preview_button_size]',
						'id'                => 'cart_preview_button_size',
						'options'           => array(
							'none'  => __( 'None', 'easy-digital-downloads' ),
							'small' => __( 'Small', 'easy-digital-downloads' ),
							'large' => __( 'Large', 'easy-digital-downloads' ),
						),
						'show_option_empty' => false,
						'show_option_all'   => false,
						'show_option_none'  => false,
						'selected'          => edd_get_option( 'cart_preview_button_size', 'large' ),
						'std'               => 'large',
						'data'              => array(
							'edd-requirement' => 'cart_preview_button_size',
						),
						'class'             => 'edd-requirement',
					)
				);
				$select->output();
				?>
			</div>
			<div class="<?php echo esc_attr( $this->get_requires_css_class( 'cart_preview_button_size', array( 'edd-form-group' ) ) ); ?>">
				<?php
				printf( '<label for="cart_preview_button_position">%s</label>', esc_html__( 'Button Position', 'easy-digital-downloads' ) );
				$select = new \EDD\HTML\Select(
					array(
						'name'              => 'edd_settings[cart_preview_button_position]',
						'id'                => 'cart_preview_button_position',
						'options'           => array(
							''      => __( 'Bottom Left Corner', 'easy-digital-downloads' ),
							'right' => __( 'Bottom Right Corner', 'easy-digital-downloads' ),
						),
						'show_option_all'   => false,
						'show_option_none'  => false,
						'show_option_empty' => false,
						'selected'          => edd_get_option( 'cart_preview_button_position', '' ),
					)
				);
				$select->output();
				?>
			</div>
		</div>
		<p class="description"><?php esc_html_e( 'Optionally show button to open the cart preview when items are in the cart.', 'easy-digital-downloads' ); ?></p>
		<?php
	}

	/**
	 * Enqueue assets.
	 *
	 * @since 3.6.2
	 * @return void
	 */
	public function enqueue_assets() {
		if ( ! Utility::is_enabled() ) {
			return;
		}

		if ( ! $this->assets->should_load() ) {
			return;
		}

		$this->assets->enqueue();

		add_action( 'wp_footer', array( $this, 'render_dialog' ), 100 );
	}

	/**
	 * Render the dialog HTML.
	 *
	 * @since 3.6.2
	 * @return void
	 */
	public function render_dialog() {

		/**
		 * Fires before the cart preview dialog is rendered.
		 *
		 * @since 3.6.2
		 */
		do_action( 'edd_cart_preview_before_render' );

		Utility::load_template( 'dialog.php' );

		/**
		 * Fires after the cart preview dialog is rendered.
		 *
		 * @since 3.6.2
		 */
		do_action( 'edd_cart_preview_after_render' );
	}
}
