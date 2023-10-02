<?php
/**
 * @var array  $block_attributes
 * @var string $content
 */

?>
<p class="message">
	<?php esc_html_e( 'Please enter your username or email address. You will receive an email message with instructions on how to reset your password.', 'easy-digital-downloads' ); ?>
</p>
<form id="edd-blocks-form__lost-password" class="edd-blocks-form edd-blocks-form__lost-password" action="" method="post">
	<div class="edd-blocks-form__group edd-blocks-form__group-username">
		<label for="user_login">
			<?php
			esc_html_e( 'Username or Email Address', 'easy-digital-downloads' );
			echo EDD()->html->show_required();
			?>
		</label>
		<div class="edd-blocks-form__control">
			<input name="user_login" id="user_login" class="edd-required edd-input" type="text" autocomplete="username" required/>
		</div>
	</div>
	<div class="edd-blocks-form__group edd-blocks-form__group-submit">
		<input type="hidden" name="edd_redirect" value="<?php echo esc_url( remove_query_arg( 'action', edd_get_current_page_url() ) ); ?>"/>
		<input type="hidden" name="edd_lost-password_nonce" value="<?php echo esc_attr( wp_create_nonce( 'edd-lost-password-nonce' ) ); ?>"/>
		<input type="hidden" name="edd_action" value="user_lost_password"/>
		<input type="hidden" name="edd_submit" value="edd_lost_password_submit" />
		<input id="edd_lost_password_submit" type="submit" class="<?php echo esc_attr( implode( ' ', EDD\Blocks\Functions\get_button_classes() ) ); ?>" value="<?php esc_html_e( 'Get New Password', 'easy-digital-downloads' ); ?>"/>
	</div>
	<?php do_action( 'edd_lost_password_fields_after' ); ?>
</form>
