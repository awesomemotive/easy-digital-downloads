<?php

function edd_downloads_contextual_help() {
	$screen = get_current_screen();

	if ( $screen->id != 'download' )
		return;

	$screen->set_help_sidebar(
		'<p><strong>' . sprintf( __( 'For more information:', 'edd' ) . '</strong></p>' .
		'<p>' . sprintf( __( 'Visit the <a href="%s">documentation</a> Easy Digital Downloads website.', 'edd' ), esc_url( 'https://easydigitaldownloads.com/documentation/' ) ) ) . '</p>' .
		'<p>' . sprintf( 
					__( '<a href="%s">Post an issue</a> on <a href="%s">Github</a>. View <a href="%s">extensions</a> or <a href="%s">themes</a>.', 'edd' ),
					esc_url( 'https://github.com/pippinsplugins/Easy-Digital-Downloads/issues' ), 
					esc_url( 'https://github.com/pippinsplugins/Easy-Digital-Downloads' ),
					esc_url( 'https://easydigitaldownloads.com/extensions/' ),
					esc_url( 'https://easydigitaldownloads.com/themes/' )
				) . '</p>'
	);

	$screen->add_help_tab( array(
		'id'	    => 'edd-download-configuration',
		'title'	    => __( 'Download Configuration' ),
		'content'	=> 
			'<p>' . __( '<strong>Pricing Options</strong> - Either define a single fixed price, or enable variable pricing. By enabling variable pricing, multiple download options and prices can be configured.', 'edd' ) . '</p>' . 

			'<p>' . __( '<strong>File Downloads</strong> - Define download file names and their respsective file URL. Multiple files can be assigned to a single price, or variable prices.' ) . '</p>' . 

			'<p>' . __( '<strong>Button Options</strong> - Disable the automatic output the purchase button. If disabled, no button will be added to the download page unless the <code>[purchase_link]</code> shortcode is used.' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'edd-product-notes',
		'title'	    => __( 'Product Notes' ),
		'content'	=> 
			'<p>' . __( 'Special notes or instructions for the product. These notes will be added to the purchase receipt, and additionaly may be used by some extensions or themes on the frontend.', 'edd' ) . '</p>'
	) );

	$colors = array(
		'gray', 'pink', 'blue', 'green', 'teal', 'black', 'dark gray', 'orange', 'purple', 'slate'
	);

	$screen->add_help_tab( array(
		'id'	    => 'edd-purchase-shortcode',
		'title'	    => __( 'Purchase Shortcode' ),
		'content'	=> 
			'<p>' . __( '<strong>Purchase Shortcode</strong> - If the automatic output of the purchase button has been disabled via the Download Configuration box, a shortcode can be used to output the button or link.', 'edd' ) . '</p>' .

			'<p><code>[purchase_link]</code></p>' . 

			'<ul>
				<li><strong>id</strong> - The ID of a specific download to purchase.</li>
				<li><strong>text</strong> - The text to be displayed on the button or link.</li>
				<li><strong>style</strong> - <em>button</em> | <em>link</em> - The style of the purchase link.</li>
				<li><strong>color</strong> - <em>' . implode( '</em> | <em>', $colors ) . '</em></li>
				<li><strong>class</strong> - One or more custom CSS classes you want applied to the button.</li>
			</ul>' .

			'<p>' . sprintf( __( 'For more information, see <a href="%s">using Shortcodes</a> on the WordPress.org Codex or <a href="%s">Easy Digital Downloads Documentation</a>', 'edd' ), 'http://codex.wordpress.org/Shortcode', 'https://easydigitaldownloads.com/docs/display-purchase-buttons-purchase_link/' ) . '</p>'
	) );

	do_action( 'edd_downloads_contextual_help', $screen );
}
add_action( 'load-post.php', 'edd_downloads_contextual_help' );
add_action( 'load-post-new.php', 'edd_downloads_contextual_help' );