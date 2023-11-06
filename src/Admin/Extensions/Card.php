<?php
/**
 * Extension Card builder.
 *
 * @package EDD
 * @subpackage Extensions
 * @copyright 2022 Easy Digital Downloads
 */
namespace EDD\Admin\Extensions;

class Card {
	use Traits\Buttons;

	/**
	 * The product data.
	 *
	 * @var \EDD\Admin\Extensions\ProductData
	 */
	private $product;

	/**
	 * The parameters if the plugin is not active.
	 *
	 * @var array
	 */
	private $inactive_parameters = array();

	/**
	 * The parameters if the plugin is active.
	 *
	 * @var array
	 */
	private $active_parameters = array();

	/**
	 * The required pass ID.
	 *
	 * @var int
	 */
	private $required_pass_id;

	/**
	 * Whether the current plugin is active.
	 *
	 * @var bool
	 */
	private $is_plugin_active = false;

	/**
	 * Whether the current plugin is installed.
	 *
	 * @var bool
	 */
	private $is_plugin_installed = false;

	/**
	 * The plugin version.
	 *
	 * @since 3.1.1
	 * @var bool|string
	 */
	private $version = false;

	public function __construct( ProductData $product, $args ) {
		$this->product             = $product;
		$this->inactive_parameters = $args['inactive_parameters'];
		$this->active_parameters   = $args['active_parameters'];
		$this->required_pass_id    = $args['required_pass_id'];
		$this->is_plugin_active    = $args['is_plugin_active'];
		$this->is_plugin_installed = $args['is_plugin_installed'];
		$this->version             = $args['version'];

		if ( ! empty( $this->product->style ) && 'installer' === $this->product->style ) {
			$this->do_card_extension_installer();
		} else {
			$this->do_card_product_education();
		}
	}

	/**
	 * Outputs the card with the product education style markup.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function do_card_product_education() {
		?>
		<div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $this->get_card_classes() ) ) ); ?>">
			<?php $this->do_title(); ?>
			<div class="edd-extension-manager__body">
				<?php
				$this->do_image();
				$this->do_description();
				$this->do_features();
				$this->do_actions();
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Outputs the product card with the extension installer style markup.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function do_card_extension_installer() {
		$filter_terms = $this->get_filter_terms();
		?>
		<div
			class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $this->get_card_classes() ) ) ); ?>"
			<?php if ( $filter_terms ) : ?>
				data-filter="<?php echo esc_attr( $this->get_filter_terms() ); ?>"
			<?php endif; ?>
		>
			<div class="edd-extension-manager__body">
				<?php
				$this->do_icon();
				echo '<div class="edd-extension-manager__content">';
					$this->do_title( true );
					$this->do_description();
				echo '</div>';
				$this->do_settings_link( $this->product );
				?>
			</div>
			<div class="edd-extension-manager__actions">
				<?php
				$this->do_version();
				$this->do_installer_action();
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Gets the settings link.
	 *
	 * @since 3.1.1
	 * @param ProductData $product_data The product data.
	 * @return void
	 */
	protected function do_settings_link( $product_data ) {}

	/**
	 * Outputs the extension title.
	 *
	 * @since 3.1.1
	 * @param bool $link Whether the title should be linked.
	 * @return void
	 */
	private function do_title( $link = false ) {
		$title = ! empty( $this->product->heading ) ? $this->product->heading : $this->product->title;
		$url   = false;
		if ( $link && ! empty( $this->product->slug ) ) {
			$url = edd_link_helper(
				'https://easydigitaldownloads.com/downloads/' . esc_attr( $this->product->slug ),
				array(
					'utm_content' => esc_attr( $this->product->slug ),
					'utm_medium'  => 'extensions-page',
				),
				false
			);
		}
		?>
		<h3 class="edd-extension-manager__title">
			<?php
			if ( $url ) {
				printf(
					'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
					esc_url( $url ),
					esc_html( $title )
				);
			} else {
				echo esc_html( $title );
			}
			?>
		</h3>
		<?php
	}

	/**
	 * Outputs the extension image.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function do_image() {
		if ( empty( $this->product->image ) ) {
			return;
		}
		?>
		<div class="edd-extension-manager__image">
			<img alt="" src="<?php echo esc_url( $this->product->image ); ?>" />
		</div>
		<?php
	}

	/**
	 * Outputs the extension icon.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function do_icon() {
		if ( empty( $this->product->icon ) ) {
			return;
		}
		?>
		<div class="edd-extension-manager__icon">
			<img alt="" src="<?php echo esc_url( $this->product->icon ); ?>" />
			<?php $this->do_recommended(); ?>
		</div>
		<?php
	}

	/**
	 * Outputs the extension description.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function do_description() {
		if ( empty( $this->product->description ) ) {
			return;
		}
		?>
		<div class="edd-extension-manager__description"><?php echo wp_kses_post( wpautop( $this->product->description ) ); ?></div>
		<?php
	}

	/**
	 * Outputs the extension features.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function do_features() {
		if ( empty( $this->product->features ) || ! is_array( $this->product->features ) ) {
			return;
		}
		?>
		<div class="edd-extension-manager__features">
			<ul>
			<?php foreach ( $this->product->features as $feature ) : ?>
				<li><span class="dashicons dashicons-yes"></span><?php echo esc_html( $feature ); ?></li>
			<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Outputs the extension actions.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function do_actions() {
		?>
		<div class="edd-extension-manager__group edd-extension-manager__actions">
			<?php
			if ( ! $this->is_plugin_active && ! empty( $this->inactive_parameters['button_text'] ) ) {
				?>
				<div class="edd-extension-manager__step">
					<?php $this->button( $this->inactive_parameters ); ?>
				</div>
				<?php
			}
			?>
			<div class="edd-extension-manager__step">
				<?php $this->link( $this->active_parameters ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Adds a recommended tag to the extension card.
	 *
	 * @since 3.2.0
	 * @return void
	 */
	private function do_recommended() {
		if ( ! in_array( 'recommended', $this->get_product_terms(), true ) ) {
			return;
		}
		?>
		<div class="edd-plugin__recommended">
			<?php esc_html_e( 'Recommended', 'easy-digital-downloads' ); ?>
		</div>
		<?php
	}

	/**
	 * If the plugin version is known, output it on the card.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	private function do_version() {
		if ( ! $this->version ) {
			return;
		}
		?>
		<div class="edd-plugin__version">
			<?php
			/* translators: the plugin version */
			printf( esc_html__( 'Version: %s', 'easy-digital-downloads' ), esc_html( $this->version ) );
			?>
		</div>
		<?php
	}

	/**
	 * Installer cards have custom actions to output: activate/deactivate button; install button; upgrade link.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	protected function do_installer_action() {
		$args = $this->active_parameters;
		if ( ! $this->is_plugin_active && ! empty( $this->inactive_parameters['button_text'] ) ) {
			$args = $this->inactive_parameters;
		}
		?>
		<div class="edd-extension-manager__control">
			<?php $this->select_installer_action( $args ); ?>
		</div>
		<?php
	}

	/**
	 * Selects which action button should show.
	 *
	 * @since 3.1.1
	 * @param array $args
	 * @return void
	 */
	protected function select_installer_action( $args ) {
		if ( ! $this->is_plugin_active && ! empty( $this->inactive_parameters['button_text'] ) ) {
			$this->button( $this->inactive_parameters );
			return;
		}

		$this->link( $this->active_parameters );
	}

	/**
	 * Gets the CSS classes for the single extension card.
	 *
	 * @since 2.11.4
	 * @return array The array of CSS classes.
	 */
	private function get_card_classes() {
		$base_class   = 'edd-extension-manager__card';
		$card_classes = array(
			$base_class,
		);
		if ( $this->is_plugin_installed ) {
			$card_classes[] = 'edd-plugin__installed';
			if ( $this->is_plugin_active ) {
				$card_classes[] = 'edd-plugin__active';
			} else {
				$card_classes[] = 'edd-plugin__inactive';
			}
		}
		$variation = 'stacked';
		if ( ! empty( $this->product->style ) ) {
			$variation = $this->product->style;
		}
		if ( 'detailed-2col' === $variation && ( empty( $this->product->features ) || ! is_array( $this->product->features ) ) ) {
			$variation = 'detailed';
		}
		$card_classes[] = "{$base_class}--{$variation}";

		return $card_classes;
	}

	/**
	 * Gets the data-filter terms for a card.
	 *
	 * @since 3.1.1
	 * @return string
	 */
	private function get_filter_terms() {
		$terms = $this->get_product_terms();
		if ( ! empty( $this->product->tab ) ) {
			$terms[] = $this->product->tab;
		}

		return implode( ',', array_map( 'strtolower', array_filter( $terms ) ) );
	}

	/**
	 * Gets the product terms for a card.
	 *
	 * @since 3.2.0
	 * @return array
	 */
	private function get_product_terms() {
		if ( ! empty( $this->product->terms ) ) {
			return array_keys( (array) $this->product->terms );
		}

		return array();
	}
}
