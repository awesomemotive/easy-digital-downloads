<?php
/**
 * Email Editor: Content
 *
 * @package     EDD
 * @subpackage  Admin/Emails/Views
 * @since       3.3.0
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'tiny_mce_plugins',
	function ( $plugins ) {
		return array_diff( $plugins, array( 'wpview' ) );
	}
);

if ( ! $email->can_edit( 'content' ) ) {
	add_filter(
		'tiny_mce_before_init',
		function ( $args ) {
			$args['readonly'] = 1;

			return $args;
		}
	);
}
?>
<div class="edd-form-group">
	<label for="edd-email-content" class="edd-form-group__label">
		<?php esc_html_e( 'Message', 'easy-digital-downloads' ); ?>
	</label>
	<div class="edd-form-group__control">
		<?php wp_editor( $email->content, 'edd-email-content', array( 'textarea_name' => 'content' ) ); ?>
	</div>
	<?php
	if ( ! $email->can_edit( 'content' ) ) :
		?>
		<p class="edd-form-group__help">
			<?php esc_html_e( 'The content for this email is not editable.', 'easy-digital-downloads' ); ?>
		</p>
		<?php
	endif;
	?>
</div>
