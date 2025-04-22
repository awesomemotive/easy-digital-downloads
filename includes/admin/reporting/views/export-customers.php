<?php
global $wpdb;
$taxonomies = edd_get_download_taxonomies();
$taxonomies = array_map( 'sanitize_text_field', $taxonomies );

$placeholders = implode( ', ', array_fill( 0, count( $taxonomies ), '%s' ) );

$taxonomy__in = $wpdb->prepare( "tt.taxonomy IN ({$placeholders})", $taxonomies );

$sql = "SELECT t.*, tt.*, tr.object_id
		FROM {$wpdb->terms} AS t
		INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id
		INNER JOIN {$wpdb->term_relationships} AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
		WHERE {$taxonomy__in}";

$results = $wpdb->get_results( $sql );

$taxonomies = array();

if ( $results ) {
	foreach ( $results as $r ) {
		$t = get_taxonomy( $r->taxonomy );
		$taxonomies[ absint( $r->term_id ) ] = $t->labels->singular_name . ': ' . esc_html( $r->name );
	}
}
?>
<label for="edd_export_taxonomy" class="screen-reader-text"><?php esc_html_e( 'Select Taxonomy', 'easy-digital-downloads' ); ?></label>
<?php
echo EDD()->html->select(
	array(
		'name'             => 'taxonomy',
		'id'               => 'edd_export_taxonomy',
		'options'          => $taxonomies,
		'selected'         => false,
		'show_option_none' => false,
		'show_option_all'  => __( 'All Taxonomies', 'easy-digital-downloads' ),
	)
);
?>
<label for="edd_customer_export_download" class="screen-reader-text"><?php esc_html_e( 'Select Download', 'easy-digital-downloads' ); ?></label>
<?php
echo EDD()->html->product_dropdown(
	array(
		'name'        => 'download',
		'id'          => 'edd_customer_export_download',
		'chosen'      => true,
		/* translators: %s: Download plural label */
		'placeholder' => sprintf( __( 'All %s', 'easy-digital-downloads' ), edd_get_label_plural() ),
	)
);
