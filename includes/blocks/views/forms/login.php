<?php
/**
 * @var string $redirect_url
 */
?>
<form id="edd-blocks-form__login" class="edd-blocks-form edd-blocks-form__login" action="" method="post">
	<?php do_action( 'edd_login_fields_before' ); ?>
	<div class="edd-blocks-form__group edd-blocks-form__group-username">
		<label for="edd_user_login">
			<?php
			esc_html_e( 'Username or Email', 'easy-digital-downloads' );
			echo EDD()->html->show_required();
			?>
		</label>
		<div class="edd-blocks-form__control">
			<input name="edd_user_login" id="edd_user_login" class="edd-required edd-input" type="text" required/>
		</div>
	</div>
	<div class="edd-blocks-form__group edd-blocks-form__group-password">
		<label for="edd_user_pass">
			<?php
			esc_html_e( 'Password', 'easy-digital-downloads' );
			echo EDD()->html->show_required();
			?>
		</label>
		<div class="edd-blocks-form__control">
			<input name="edd_user_pass" id="edd_user_pass" class="edd-password edd-required edd-input" type="password" required/>
		</div>
	</div>
	<div class="edd-blocks-form__group edd-blocks-form__group-remember">
		<div class="edd-blocks-form__control">
			<input name="rememberme" type="checkbox" id="rememberme" value="forever" />
			<label for="rememberme"><?php esc_html_e( 'Remember Me', 'easy-digital-downloads' ); ?></label>
		</div>
	</div>
	<div class="edd-blocks-form__group edd-blocks-form__group-submit">
		<input type="hidden" name="edd_redirect" value="<?php echo esc_url( $redirect_url ); ?>"/>
		<input type="hidden" name="edd_login_nonce" value="<?php echo esc_attr( wp_create_nonce( 'edd-login-nonce' ) ); ?>"/>
		<input type="hidden" name="edd_action" value="user_login"/>
		<input id="edd_login_submit" type="submit" class="<?php echo esc_attr( implode( ' ', EDD\Blocks\Functions\get_button_classes() ) ); ?>" value="<?php esc_html_e( 'Log In', 'easy-digital-downloads' ); ?>"/>
	</div>
	<p class="edd-blocks-form__group edd-blocks-form__group-lost-password">
		<a href="<?php echo esc_url( add_query_arg( 'action', 'lostpassword', edd_get_current_page_url() ) ); ?>">
			<?php esc_html_e( 'Lost Password?', 'easy-digital-downloads' ); ?>
		</a>
	</p>
	<?php do_action( 'edd_login_fields_after' ); ?>
</form>
