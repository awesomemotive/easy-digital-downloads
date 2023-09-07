<?php

if ( $count <= $number ) {
	return;
}
?>
<div class="edd-blocks-orders__pagination">
	<?php
	echo edd_pagination(
		array(
			'type'  => 'purchase_history',
			'total' => ceil( $count / $number ),
		)
	);
	?>
</div>
