<?php
defined( 'ABSPATH' ) OR exit;

if ( ! edd_has_variable_prices( get_the_ID() ) ) {
	?>
	<div itemprop="price" class="">
		<?php edd_price( get_the_ID() ); ?>
	</div>
	<?php
}