<?php
namespace EDD\Onboarding\Steps\Products;

use EDD\Onboarding\Helpers;

function initialize() {
	// add_action( 'wp_ajax_edd_onboarding_started', array( $this, 'ajax_onboarding_started' ) );
}

function save_handler() {
	exit;
}

function step_html() {
	ob_start();
	?>
	<pre>
[This is a minimal form, with just the basic settings needed for a download]
Product Name [text input]
Product Pricing:
[Single Price] [Variable Priced] (based on this option, the following will change)

[Single Price]
Price [text input]

[Variable Priced]
Variation Name [text input] Variation Price [text input]

Add your first file:
[File Upload field]

Product Image:
[Image upload field]
	</pre>
	<?php

	return ob_get_clean();
}
