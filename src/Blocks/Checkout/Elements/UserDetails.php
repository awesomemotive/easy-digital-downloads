<?php
/**
 * Checkout Block Elements User Details
 *
 * @package     EDD\Blocks\Checkout\Elements
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.6.0
 */

namespace EDD\Blocks\Checkout\Elements;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;

/**
 * User Details class.
 *
 * @since 3.6.0
 */
class UserDetails implements SubscriberInterface {

	/**
	 * Get the subscribed events.
	 *
	 * @since 3.6.0
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return array(
			'edd_checkout_form_top' => array( 'render', 1 ),
		);
	}

	/**
	 * Render the personal info fields for the purchase form.
	 *
	 * @since 3.6.0
	 * @param array $block_attributes The block attributes.
	 */
	public static function render( $block_attributes ) {
		if ( empty( $block_attributes ) ) {
			return;
		}
		$css_classes = array(
			'edd-blocks__user-details',
		);
		if ( ! empty( $block_attributes['logged_in'] ) ) {
			$css_classes[] = 'edd-blocks__user-details--logged-in';
		}
		$address_class                   = edd_get_namespace( 'Checkout\\Address' );
		$address                         = new $address_class();
		$block_attributes['has_address'] = ! empty( $address->get_fields() );
		?>
		<div class="<?php echo esc_attr( implode( ' ', $css_classes ) ); ?>">
			<?php
			PersonalInfo::render( $block_attributes );
			$address->render();
			do_action( 'edd_blocks_checkout_address_fields', $block_attributes );
			?>
		</div>
		<?php
	}
}
