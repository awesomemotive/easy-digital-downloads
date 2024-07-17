<?php
$row_actions = $email->get_row_actions();
unset( $row_actions['edit'] );

if ( empty( $row_actions ) ) {
	return;
}
?>
<div class="edd-editor__actions--test">
	<?php
	foreach ( $row_actions as $id => $action ) {
		if ( 'test' === $id ) {
			$action['url'] = add_query_arg( 'editor', 'true', $action['url'] );
		}
		printf(
			'<a href="%1$s" class="button button-secondary edd-email-action-%4$s"%3$s>%2$s</a>',
			esc_url( $action['url'] ),
			esc_html( $action['text'] ),
			isset( $action['target'] ) ? ' target="' . esc_attr( $action['target'] ) . '"' : '',
			esc_attr( $id )
		);
	}
	?>
</div>
