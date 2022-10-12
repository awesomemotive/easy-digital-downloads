<?php

$plugins = array(
	array(
		'name'        => __( 'Essential eCommerce Features', 'easy-digital-downloads' ),
		'description' => __( 'Get all the essential eCommerce features to sell digital products with WordPress.', 'easy-digital-downloads' ),
	),
	array(
		'name'        => __( 'Optimize Checkout', 'easy-digital-downloads' ),
		'description' => __( 'Improve the checkout experience by auto-creating user accounts for new customers.', 'easy-digital-downloads' ),
	),
	array(
		'name'        => __( 'Reliable Email Delivery', 'easy-digital-downloads' ),
		'description' => __( 'Email deliverability is one of the most important services for an eCommerce store. Don’t leave your customers in the dark.', 'easy-digital-downloads' ),
	),
	array(
		'name'        => __( 'SEO & Analytics', 'easy-digital-downloads' ),
		'description' => __( 'Get the tools used by millions of smart business owners to analyze and optimize their store’s traffic with SEO.', 'easy-digital-downloads' ),
	),
	array(
		'name'        => __( 'Conversion Tools', 'easy-digital-downloads' ),
		'description' => __( 'Get the #1 conversion optimization plugin to convert your website traffic into subscribers, leads, and sales.', 'easy-digital-downloads' ),
	),
);
?>

<div class="edd-onboarding__plugins-list">
	<?php foreach( $plugins as $plugin ) :
		?>
		<div class="edd-onboarding__plugins-plugin">
			<div class="edd-onboarding__plugins-details">
				<h3><?php echo esc_html( $plugin['name'] ); ?></h3>
				<p><?php echo esc_html( $plugin['description'] ); ?></p>
			</div>
			<div class="edd-onboarding__plugins-control">
				<input type="checkbox">
			</div>
		</div>
		<?php
	endforeach;
	?>


</div>

<hr />

<h3>Get helpful suggestions from Easy Digital Downloads on how to supercharge your EDD powered store, so you can improve conversions and earn more money.</h3>
Your Email Address
<input type="text">

Help make EDD better for everyone (?)
<input type="checkbox"> - TOGGLE?

<hr>

<p>The following plugins will be installed: MonsterInsights Free, OptinMonster</p>
