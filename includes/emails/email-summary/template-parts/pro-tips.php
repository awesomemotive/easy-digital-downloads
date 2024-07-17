<?php
/**
 * Email Summary Pro Tips
 */
if ( empty( $blurb ) ) {
	return;
}
?>
<div class="email-container pro-tip-blurb" style="max-width: 450px; margin: 0 auto; font-family: -apple-system, BlinkMacSystemFont, avenir next, avenir, segoe ui, helvetica neue, helvetica, Cantarell, Ubuntu, roboto, noto, arial, sans-serif; color: #1F2937;">
	<div class="content-box pro-tip-section" style="background: #FFF;">

		<div class="content-holder pro-tip-section" style="padding: 0px 31px 27px 31px;">

			<div class="pro-tip-holder" style="background: #F3F8FE; border-radius: 10px; padding: 32px 40px;">

				<div class="pro-tip-section-title" style="font-weight: 600; font-size: 18px; line-height: 23px; color: #2794DA;">
					<img src="<?php echo esc_url( 'https://plugin.easydigitaldownloads.com/cdn/summaries/icon-megaphone.png' ); ?>" alt="#" title="#" width="24" height="24" style="vertical-align: bottom; display: inline-block; margin-right: 4px;">
					<?php echo esc_html( __( 'Pro-tip from our expert', 'easy-digital-downloads' ) ); ?>
				</div>

				<div class="pro-tip-title pull-down-12" style="margin-top: 12px; font-weight: 600; font-size: 20px; line-height: 26px; color: #1F2937;">
					<?php echo esc_html( $blurb['headline'] ); ?>
				</div>

				<p class="bigger pull-down-8" style="margin: 0px; font-weight: 400; color: #4B5563; margin-top: 8px; font-size: 16px; line-height: 22px;">
					<?php echo esc_html( $blurb['content'] ); ?>
				</p>

				<div class="pull-down-15" style="margin-top: 15px;">
					<a href="<?php echo esc_attr( $blurb['button_link'] ); ?>" class="cta-btn" style="padding: 6px 15px; font-weight: 600; font-size: 14px; line-height: 20px; background: #2794DA; display: inline-block; text-decoration: none; color: white;">
						<?php echo esc_html( $blurb['button_text'] ); ?>
					</a>
				</div>

			</div>

		</div>

	</div>

</div>
