<?php
$args = array(
	'type'    => 'download',
	'format'  => '?paged=%#%',
	'current' => max( 1, $query_args['paged'] ),
	'total'   => $downloads->max_num_pages,
);

if ( is_single() ) {
	$args['base'] = get_permalink() . '%#%';
} else {
	$big          = 999999;
	$search_for   = array( $big, '#038;' );
	$replace_with = array( '%#%', '&' );
	$args['base'] = str_replace( $search_for, $replace_with, get_pagenum_link( $big ) );
}

edd_pagination( $args );
