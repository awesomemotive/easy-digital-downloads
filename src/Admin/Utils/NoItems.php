<?php
/**
 * NoItems class.
 *
 * @package     EDD\Admin\Utils
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.5
 */

namespace EDD\Admin\Utils;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class NoItems
 *
 * @since 3.6.5
 */
class NoItems {

	/**
	 * The heading for the NoItems class.
	 *
	 * @var string
	 */
	private $heading;

	/**
	 * The text property for the NoItems class.
	 *
	 * @var string
	 */
	private $text;

	/**
	 * The actions array for the NoItems class.
	 *
	 * @var array
	 */
	private $actions;

	/**
	 * NoItems constructor.
	 *
	 * @param string $heading The heading for the NoItems class.
	 * @param string $text The text property for the NoItems class.
	 * @param array  $actions The actions array for the NoItems class.
	 */
	public function __construct( string $heading, string $text = '', array $actions = array() ) {
		$this->heading = $heading;
		$this->text    = $text;
		$this->actions = $actions;
	}

	/**
	 * Displays a message when there are no items to show.
	 *
	 * @since 3.6.5
	 * @return void
	 */
	public function render() {
		?>
		<div class="edd-no-items">
			<img src="<?php echo esc_url( EDD_PLUGIN_URL . 'assets/images/edd-no-items.svg' ); ?>" alt="">
			<h2><?php echo esc_html( $this->heading ); ?></h2>
			<?php
			if ( ! empty( $this->text ) ) {
				echo wp_kses_post( wpautop( $this->text ) );
			}
			?>
			<?php $this->render_actions(); ?>
		</div>
		<?php
	}

	/**
	 * Renders the actions for the NoItems class.
	 *
	 * @access private
	 * @return void
	 */
	private function render_actions() {
		if ( empty( $this->actions ) ) {
			return;
		}
		?>
		<div class="edd-no-items__actions">
			<?php
			foreach ( $this->actions as $action ) {
				printf(
					'<a class="button button-%s" href="%s">%s</a>',
					esc_attr( $action['type'] ?? 'secondary' ),
					esc_url( $action['url'] ),
					esc_html( $action['label'] )
				);
			}
			?>
		</div>
		<?php
	}
}
