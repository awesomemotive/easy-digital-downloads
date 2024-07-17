<?php
/**
 * Email Summary Site Info
 */
?>
<h1 style="margin: 0px; color: #1F2937;font-weight: 700;font-size: 24px;line-height: 24px;"><?php echo esc_html( __( 'Your eCommerce Summary', 'easy-digital-downloads' ) ); ?></h1>

<div class="period-date pull-down-8" style="margin-top: 8px; font-weight: 400; font-size: 14px; line-height: 18px; color: #4B5563;">
	<?php echo esc_html( $date_range['start_date']->format( $wp_date_format ) ); ?> - <?php echo esc_html( $date_range['end_date']->format( $wp_date_format ) ); ?>
</div>

<a href="<?php echo esc_url( $site_url ); ?>" class="link-style pull-down-8" style="margin-top: 8px; font-weight: 400; font-size: 14px; text-decoration-line: underline; display: inline-block; color: inherit; text-decoration: none;">
	<?php echo esc_url( $site_url ); ?>
</a>


<div class="pull-down-20" style="margin-top: 20px;">
	<h2 style="margin: 0px; font-weight: 700; font-size: 18px; line-height: 23px; letter-spacing: -0.02em; color: #1F2937;"><?php echo esc_html( __( 'Hey there!', 'easy-digital-downloads' ) ); ?></h2>
</div>


<p class="pull-down-5" style="margin: 0px; font-weight: 400; font-size: 14px; line-height: 18px; color: #4B5563; margin-top: 5px;">
	<?php
	/**
	 * To assist in proper translations, each 'period_name' should have it's own translation.
	 *
	 * This prevents a confusing translation string when the language has different genders for the periods.
	 */
	switch ( $period_name ) {
		case 'month':
			echo esc_html( __( 'Below is a look at how your store performed in the last month.', 'easy-digital-downloads' ) );
			break;
		case 'week':
			echo esc_html( __( 'Below is a look at how your store performed in the last week.', 'easy-digital-downloads' ) );
			break;
		default:
			// While we don't support other periods, we will add a generic message just in case the case is missed.
			echo esc_html( __( 'Below is a look at how your store has been performing.', 'easy-digital-downloads' ) );
			break;
	}
	?>
</p>
