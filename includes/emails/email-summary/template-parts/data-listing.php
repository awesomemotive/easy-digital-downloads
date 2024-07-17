<?php
/**
 * Data Listing for the email summary.
 */
?>
<table class="stats-totals-wrapper" style="border-collapse: collapse; border-spacing: 0; padding: 0; vertical-align: top; text-align: left; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; width: 100%; margin-top: 48px;" width="100%" valign="top" align="left">
	<tr style="padding: 0; vertical-align: top; text-align: left;" valign="top" align="left">
		<td class="stats-totals-item-wrapper" style="word-wrap: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; padding: 0px; vertical-align: top; text-align: center; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: normal; margin: 0; mso-line-height-rule: exactly; line-height: 140%; font-size: 14px; border-collapse: collapse;" valign="top" align="center">
			<table class="stats-total-item" width="145" style="border-spacing: 0; padding: 0; vertical-align: top; text-align: left; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; width: 145px; display: inline-table; min-width: 145px;" valign="top" align="center">
				<tr style="padding: 0; vertical-align: top; text-align: left;" valign="top" align="left">
				<td class="stats-total-item-inner" style="width: 100%; min-width: 100%; word-wrap: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; padding: 0px; vertical-align: top; text-align: center; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: normal; margin: 0; mso-line-height-rule: exactly; line-height: 140%; font-size: 14px; border-collapse: collapse;" width="100%" valign="top" align="center">
					<p class="stats-total-item-icon-wrapper" style="font-weight: 400; font-size: 14px; line-height: 18px; margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #809EB0; padding: 0; text-align: center; mso-line-height-rule: exactly; margin-bottom: 1px; height: 32px;">
						<img src="<?php echo esc_url( 'https://plugin.easydigitaldownloads.com/cdn/summaries/icon-gross.png' ); ?>" alt="#" title="#" width="28" height="28">
					</p>
					<p class="stats-total-item-title" style="margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #1F2937; padding: 0; text-align: center; mso-line-height-rule: exactly; font-weight: 600; font-size: 14px; line-height: 18px; font-style: normal; margin-bottom: 5px; white-space: nowrap;">
						<?php echo esc_html( __( 'Gross Revenue', 'easy-digital-downloads' ) ); ?>
					</p>
					<p class="stats-total-item-value dark-white-color" style="margin: 0 0 6px 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: bold; padding: 0; text-align: center; mso-line-height-rule: exactly; line-height: 32px; font-size: 32px;">
						<?php echo esc_html( edd_currency_filter( edd_format_amount( $dataset['earnings_gross']['value'] ) ) ); ?>
					</p>
					<p class="stats-total-item-percent" style="margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #777777; font-weight: normal; padding: 0; text-align: center; mso-line-height-rule: exactly; line-height: 14px; font-size: 10px; white-space: nowrap;">
						<?php echo $this->build_relative_markup( $dataset['earnings_gross']['relative_data'] ); ?>
						<span class="comparison" style="font-style: normal; font-weight: lighter; font-size: 12px; line-height: 14px; text-align: center; color: #6B7280; display: block; margin-top: 6px;">
							<?php echo esc_html( $relative_text ); ?>
						</span>
					</p>
				</td>
				</tr>
			</table>
		</td>

		<td class="stats-totals-item-wrapper" style="word-wrap: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; padding: 0px; vertical-align: top; text-align: center; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: normal; margin: 0; mso-line-height-rule: exactly; line-height: 140%; font-size: 14px; border-collapse: collapse;" valign="top" align="center">
			<table class="stats-total-item" width="145" style="border-spacing: 0; padding: 0; vertical-align: top; text-align: left; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; width: 145px; display: inline-table; min-width: 145px;" valign="top" align="center">
				<tr style="padding: 0; vertical-align: top; text-align: left;" valign="top" align="left">
				<td class="stats-total-item-inner" style="width: 100%; min-width: 100%; word-wrap: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; padding: 0px; vertical-align: top; text-align: center; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: normal; margin: 0; mso-line-height-rule: exactly; line-height: 140%; font-size: 14px; border-collapse: collapse;" width="100%" valign="top" align="center">
					<p class="stats-total-item-icon-wrapper" style="font-weight: 400; font-size: 14px; line-height: 18px; margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #809EB0; padding: 0; text-align: center; mso-line-height-rule: exactly; margin-bottom: 1px; height: 32px;">
						<img src="<?php echo esc_url( 'https://plugin.easydigitaldownloads.com/cdn/summaries/icon-net.png' ); ?>" alt="#" title="#" width="28" height="28">
					</p>
					<p class="stats-total-item-title" style="margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #1F2937; padding: 0; text-align: center; mso-line-height-rule: exactly; font-weight: 600; font-size: 14px; line-height: 18px; font-style: normal; margin-bottom: 5px; white-space: nowrap;">
						<?php echo esc_html( __( 'Net Revenue', 'easy-digital-downloads' ) ); ?>
					</p>
					<p class="stats-total-item-value dark-white-color" style="margin: 0 0 6px 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: bold; padding: 0; text-align: center; mso-line-height-rule: exactly; line-height: 32px; font-size: 32px;">
						<?php echo esc_html( edd_currency_filter( edd_format_amount( $dataset['earnings_net']['value'] ) ) ); ?>
					</p>
					<p class="stats-total-item-percent" style="margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #777777; font-weight: normal; padding: 0; text-align: center; mso-line-height-rule: exactly; line-height: 14px; font-size: 10px; white-space: nowrap;">
						<?php echo $this->build_relative_markup( $dataset['earnings_net']['relative_data'] ); ?>
						<span class="comparison" style="font-style: normal; font-weight: lighter; font-size: 12px; line-height: 14px; text-align: center; color: #6B7280; display: block; margin-top: 6px;">
							<?php echo esc_html( $relative_text ); ?>
						</span>
					</p>
				</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

<table class="stats-totals-wrapper pull-down-40" style="border-collapse: collapse; border-spacing: 0; padding: 0; vertical-align: top; text-align: left; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; width: 100%; margin-top: 48px; margin-bottom: 48px !important;" width="100%" valign="top" align="left">
	<tr style="padding: 0; vertical-align: top; text-align: left;" valign="top" align="left">
		<td class="stats-totals-item-wrapper" style="word-wrap: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; padding: 0px; vertical-align: top; text-align: center; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: normal; margin: 0; mso-line-height-rule: exactly; line-height: 140%; font-size: 14px; border-collapse: collapse;" valign="top" align="center">
			<table class="stats-total-item" width="145" style="border-spacing: 0; padding: 0; vertical-align: top; text-align: left; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; width: 145px; display: inline-table; min-width: 145px;" valign="top" align="center">
				<tr style="padding: 0; vertical-align: top; text-align: left;" valign="top" align="left">
				<td class="stats-total-item-inner" style="width: 100%; min-width: 100%; word-wrap: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; padding: 0px; vertical-align: top; text-align: center; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: normal; margin: 0; mso-line-height-rule: exactly; line-height: 140%; font-size: 14px; border-collapse: collapse;" width="100%" valign="top" align="center">
					<p class="stats-total-item-icon-wrapper" style="font-weight: 400; font-size: 14px; line-height: 18px; margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #809EB0; padding: 0; text-align: center; mso-line-height-rule: exactly; margin-bottom: 1px; height: 32px;">
						<img src="<?php echo esc_url( 'https://plugin.easydigitaldownloads.com/cdn/summaries/icon-new-customers.png' ); ?>" alt="#" title="#" width="28" height="28">
					</p>
					<p class="stats-total-item-title" style="margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #1F2937; padding: 0; text-align: center; mso-line-height-rule: exactly; font-weight: 600; font-size: 14px; line-height: 18px; font-style: normal; margin-bottom: 5px; white-space: nowrap;">
						<?php echo esc_html( __( 'New Customers', 'easy-digital-downloads' ) ); ?>
					</p>
					<p class="stats-total-item-value dark-white-color" style="margin: 0 0 6px 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: bold; padding: 0; text-align: center; mso-line-height-rule: exactly; line-height: 32px; font-size: 32px;">
						<?php echo esc_html( $dataset['new_customers']['value'] ); ?>
					</p>
					<p class="stats-total-item-percent" style="margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #777777; font-weight: normal; padding: 0; text-align: center; mso-line-height-rule: exactly; line-height: 14px; font-size: 10px; white-space: nowrap;">
						<?php echo $this->build_relative_markup( $dataset['new_customers']['relative_data'] ); ?>
						<span class="comparison" style="font-style: normal; font-weight: lighter; font-size: 12px; line-height: 14px; text-align: center; color: #6B7280; display: block; margin-top: 6px;">
							<?php echo esc_html( $relative_text ); ?>
						</span>
					</p>
				</td>
				</tr>
			</table>
		</td>

		<td class="stats-totals-item-wrapper" style="word-wrap: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; padding: 0px; vertical-align: top; text-align: center; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: normal; margin: 0; mso-line-height-rule: exactly; line-height: 140%; font-size: 14px; border-collapse: collapse;" valign="top" align="center">
			<table class="stats-total-item" width="145" style="border-spacing: 0; padding: 0; vertical-align: top; text-align: left; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; width: 145px; display: inline-table; min-width: 145px;" valign="top" align="center">
				<tr style="padding: 0; vertical-align: top; text-align: left;" valign="top" align="left">
				<td class="stats-total-item-inner" style="width: 100%; min-width: 100%; word-wrap: break-word; -webkit-hyphens: auto; -moz-hyphens: auto; hyphens: auto; padding: 0px; vertical-align: top; text-align: center; mso-table-lspace: 0pt; mso-table-rspace: 0pt; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: normal; margin: 0; mso-line-height-rule: exactly; line-height: 140%; font-size: 14px; border-collapse: collapse;" width="100%" valign="top" align="center">
					<p class="stats-total-item-icon-wrapper" style="font-weight: 400; font-size: 14px; line-height: 18px; margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #809EB0; padding: 0; text-align: center; mso-line-height-rule: exactly; margin-bottom: 1px; height: 32px;">
						<img src="<?php echo esc_url( 'https://plugin.easydigitaldownloads.com/cdn/summaries/icon-average.png' ); ?>" alt="#" title="#" width="28" height="28">
					</p>
					<p class="stats-total-item-title" style="margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #1F2937; padding: 0; text-align: center; mso-line-height-rule: exactly; font-weight: 600; font-size: 14px; line-height: 18px; font-style: normal; margin-bottom: 5px; white-space: nowrap;">
						<?php echo esc_html( __( 'Average Order', 'easy-digital-downloads' ) ); ?>
					</p>
					<p class="stats-total-item-value dark-white-color" style="margin: 0 0 6px 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #444444; font-weight: bold; padding: 0; text-align: center; mso-line-height-rule: exactly; line-height: 32px; font-size: 32px;">
						<?php echo esc_html( edd_currency_filter( edd_format_amount( $dataset['average_order_value']['value'] ) ) ); ?>
					</p>
					<p class="stats-total-item-percent" style="margin: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; color: #777777; font-weight: normal; padding: 0; text-align: center; mso-line-height-rule: exactly; line-height: 14px; font-size: 10px; white-space: nowrap;">
						<?php echo $this->build_relative_markup( $dataset['average_order_value']['relative_data'] ); ?>
						<span class="comparison" style="font-style: normal; font-weight: lighter; font-size: 12px; line-height: 14px; text-align: center; color: #6B7280; display: block; margin-top: 6px;">
							<?php echo esc_html( $relative_text ); ?>
						</span>
					</p>
				</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
