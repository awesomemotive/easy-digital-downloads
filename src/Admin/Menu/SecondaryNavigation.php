<?php

namespace EDD\Admin\Menu;

defined( 'ABSPATH' ) || exit;

/**
 * Class SecondaryNavigation
 *
 * @since 3.3.0
 * @package EDD\Admin\Menu
 */
class SecondaryNavigation {

	/**
	 * The tabs.
	 *
	 * @since 3.3.0
	 * @var array
	 */
	public $tabs;

	/**
	 * The page.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	private $page;

	/**
	 * Custom parameters.
	 *
	 * @since 3.3.0
	 * @var array
	 */
	private $args;

	/**
	 * SecondaryNavigation constructor.
	 *
	 * @since 3.3.0
	 * @param array  $tabs The tabs.
	 * @param string $page The page.
	 */
	public function __construct( array $tabs, string $page, array $args = array() ) {
		$this->tabs = self::get_tabs( $tabs, $page );
		$this->page = $page;
		$this->args = $args;
	}

	/**
	 * Render the tabs.
	 *
	 * @since 3.3.0
	 */
	public function render() {
		?>
		<div class="edd-nav__wrapper">
			<nav
				class="<?php echo esc_attr( $this->css_classes_to_string( $this->get_nav_classes() ) ); ?>"
				aria-label="<?php esc_attr_e( 'Secondary menu', 'easy-digital-downloads' ); ?>"
			>
				<ul class="edd-nav__tabs">
					<?php
					foreach ( $this->tabs as $slug => $tab_name_or_data ) {
						printf(
							'<li class="%4$s"><a href="%1$s" class="%2$s">%3$s</a></li>',
							esc_url( $this->get_tab_url( $slug, $tab_name_or_data ) ),
							esc_attr( $this->css_classes_to_string( $this->get_tab_classes( $slug ) ) ),
							esc_html( $this->get_tab_name( $tab_name_or_data ) ),
							esc_attr( $this->css_classes_to_string( $this->get_li_classes( $slug ) ) )
						);
					}
					?>
				</ul>
			</nav>
			<?php
			if ( ! empty( $this->args['show_search'] ) ) {
				if ( isset( $_REQUEST['s'] ) && strlen( $_REQUEST['s'] ) ) {
					echo '<span class="subtitle edd-search-query">';
					printf(
					/* translators: %s: Search query. */
						__( 'Search results for: %s', 'easy-digital-downloads' ),
						'<strong>' . esc_html( $_REQUEST['s'] ) . '</strong>'
					);
					echo '</span>';
				}
			}
			?>
		</div>
		<?php
	}

	/**
	 * Get the tabs.
	 *
	 * @since 3.3.0
	 * @param array  $tabs The tabs.
	 * @param string $page The page.
	 * @return array
	 */
	private function get_tabs( array $tabs, string $page ): array {
		/**
		 * Filter the tabs.
		 *
		 * @since 3.3.0
		 * @param array  $tabs The tabs.
		 * @param string $page The page.
		 */
		return apply_filters( 'edd_secondary_navigation_tabs', $tabs, $page );
	}

	/**
	 * Get the current tab.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private function get_current_tab(): string {
		if ( ! empty( $this->args['active_tab'] ) ) {
			return $this->args['active_tab'];
		}

		$tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( $tab && array_key_exists( $tab, $this->tabs ) ) {
			return $tab;
		}

		return array_key_first( $this->tabs );
	}

	/**
	 * Get the nav classes.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	private function get_nav_classes(): array {
		$nav_classes = array(
			'edd-nav',
			"{$this->page}-nav",
		);
		if ( ! empty( $this->args['legacy'] ) ) {
			$nav_classes = array_merge( $nav_classes, array( 'nav-tab-wrapper', 'edd-nav-tab-wrapper' ) );
		}

		return $nav_classes;
	}

	/**
	 * Get the li classes.
	 *
	 * @since 3.3.0
	 * @param string $slug The tab slug.
	 * @return array
	 */
	private function get_li_classes( $slug ): array {
		$classes = array( 'edd-nav__tabs--item' );
		if ( $this->get_current_tab() === $slug ) {
			$classes[] = 'active';
		}

		return $classes;
	}

	/**
	 * Get the tab classes.
	 *
	 * @since 3.3.0
	 * @param string $slug The tab slug.
	 * @return array
	 */
	private function get_tab_classes( string $slug ): array {
		$classes = array( 'tab' );
		if ( ! empty( $this->args['legacy'] ) ) {
			$classes[] = 'nav-tab';
			if ( $this->get_current_tab() === $slug ) {
				$classes[] = 'nav-tab-active';
			}
		}

		return $classes;
	}

	/**
	 * Convert an array of CSS classes to a string.
	 *
	 * @since 3.3.0
	 * @param array $classes The CSS classes.
	 * @return string
	 */
	private function css_classes_to_string( array $classes ): string {
		return implode( ' ', array_map( 'sanitize_html_class', array_filter( $classes ) ) );
	}

	/**
	 * Get the tab URL.
	 *
	 * @since 3.3.0
	 * @param string $slug The tab slug.
	 * @return string
	 */
	private function get_tab_url( string $slug, $data = null ): string {
		if ( is_array( $data ) && ! empty( $data['url'] ) ) {
			return $data['url'];
		}

		$args = array(
			'page' => sanitize_key( $this->page ),
		);
		if ( array_key_first( $this->tabs ) !== $slug ) {
			$args['tab'] = sanitize_key( $slug );
		}

		return edd_get_admin_url( $args );
	}

	/**
	 * Get the tab name.
	 *
	 * @since 3.3.0
	 * @param string|array $data The tab data.
	 * @return string
	 */
	private function get_tab_name( $data ) {
		return is_array( $data ) ? $data['name'] : $data;
	}
}
