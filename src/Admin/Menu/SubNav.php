<?php
/**
 * SubNav HTML Element
 *
 * @package     EDD\Admin\Menu
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.4
 */

namespace EDD\Admin\Menu;

defined( 'ABSPATH' ) || exit;

/**
 * Class SubNav
 *
 * Renders a secondary navigation menu using the edd-sub-nav pattern.
 *
 * @since 3.6.4
 */
class SubNav {

	/**
	 * The tabs.
	 *
	 * @since 3.6.4
	 * @var array
	 */
	private $tabs;

	/**
	 * The current tab.
	 *
	 * @since 3.6.4
	 * @var string
	 */
	private $current;

	/**
	 * Configuration arguments.
	 *
	 * @since 3.6.4
	 * @var array
	 */
	private $args;

	/**
	 * SubNav constructor.
	 *
	 * @since 3.6.4
	 * @param array $args Arguments for the sub-navigation.
	 */
	public function __construct( array $args = array() ) {
		$this->args    = wp_parse_args( $args, $this->get_defaults() );
		$this->tabs    = $this->args['tabs'];
		$this->current = $this->args['current'];
	}

	/**
	 * Gets the default arguments for the sub-navigation.
	 *
	 * @since 3.6.4
	 * @return array
	 */
	private function get_defaults(): array {
		return array(
			'tabs'          => array(),
			'current'       => '',
			'base_url'      => '',
			'url_args'      => array(),
			'url_key'       => 'view',
			'remove_args'   => array(),
			'url_callback'  => null,
			'minimum_tabs'  => 1,
			'wrapper_style' => '',
		);
	}

	/**
	 * Renders the sub-navigation HTML.
	 *
	 * @since 3.6.4
	 * @return void
	 */
	public function render(): void {
		if ( empty( $this->tabs ) ) {
			return;
		}

		// If minimum tabs requirement not met, return early.
		if ( count( $this->tabs ) < $this->args['minimum_tabs'] ) {
			return;
		}

		?>
		<div class="edd-sub-nav__wrapper"<?php echo $this->get_wrapper_style(); ?>>
			<ul class="edd-sub-nav">
				<?php
				foreach ( $this->tabs as $tab_id => $label ) {
					$url   = $this->get_tab_url( $tab_id );
					$class = ( $this->current === $tab_id ) ? 'current' : '';

					printf(
						'<li class="%1$s"><a href="%2$s">%3$s</a></li>',
						esc_attr( $class ),
						esc_url( $url ),
						esc_html( $label )
					);
				}
				?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Gets the URL for a specific tab.
	 *
	 * @since 3.6.4
	 * @param string $tab_id The tab identifier.
	 * @return string The URL for the tab.
	 */
	private function get_tab_url( string $tab_id ): string {
		// If a custom URL callback is provided, use it.
		if ( is_callable( $this->args['url_callback'] ) ) {
			return call_user_func( $this->args['url_callback'], $tab_id );
		}

		// Build URL from base URL and args.
		$base_url = ! empty( $this->args['base_url'] ) ? $this->args['base_url'] : edd_get_admin_base_url();

		// Build query args.
		$query_args                           = $this->args['url_args'];
		$query_args[ $this->args['url_key'] ] = $tab_id;

		$url = add_query_arg( $query_args, $base_url );

		// Remove any args that should be stripped.
		if ( ! empty( $this->args['remove_args'] ) ) {
			$url = remove_query_arg( $this->args['remove_args'], $url );
		}

		return $url;
	}

	/**
	 * Gets the wrapper style attribute if set.
	 *
	 * @since 3.6.4
	 * @return string The style attribute or empty string.
	 */
	private function get_wrapper_style(): string {
		if ( empty( $this->args['wrapper_style'] ) ) {
			return '';
		}

		return sprintf( ' style="%s"', esc_attr( $this->args['wrapper_style'] ) );
	}
}
