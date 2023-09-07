<?php
/**
 * General admin functions for blocks.
 *
 * @package   edd-blocks
 * @copyright 2022 Easy Digital Downloads
 * @license   GPL2+
 * @since 2.0
 */

namespace EDD\Blocks\Admin\Notices;

defined( 'ABSPATH' ) || exit;

add_action( 'admin_notices', __NAMESPACE__ . '\existing_blocks_plugin' );
/**
 * Shows a notice if the original EDD Blocks plugin is active.
 *
 * @since 2.0
 * @return void
 */
function existing_blocks_plugin() {
	if ( ! class_exists( 'EDD_Blocks' ) ) {
		return;
	}
	?>
	<div id="edd-blocks-core-notice" class="notice notice-warning">
		<p><strong><?php esc_html_e( 'EDD Blocks are now a part of Easy Digital Downloads', 'easy-digital-downloads' ); ?></strong></p>
		<p><?php esc_html_e( 'If you are using the original Downloads block, you will need to update it to use either the new EDD Products or EDD Downloads Terms block. All other blocks functionality is automatically updated. Once you\'ve replaced your blocks, you can deactivate the EDD Blocks plugin.', 'easy-digital-downloads' ); ?></p>
	</div>
	<?php
}
