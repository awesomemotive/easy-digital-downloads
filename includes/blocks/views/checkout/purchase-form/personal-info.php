<?php
if ( function_exists( 'EDD_CFM' ) ) {
	return;
}
?>
<fieldset id="edd_checkout_user_info" class="edd-blocks-form">
	<legend><?php esc_html_e( 'Personal Info', 'easy-digital-downloads' ); ?></legend>
	<?php
	$fields = array();
	if ( empty( $customer['email'] ) || empty( $customer_info_complete ) ) {
		$fields[] = '\\EDD\\Forms\\Checkout\\PersonalInfo\\Email';
	}
	if ( empty( $customer_info_complete ) ) {
		$fields[] = '\\EDD\\Forms\\Checkout\\PersonalInfo\\FirstName';
		$fields[] = '\\EDD\\Forms\\Checkout\\PersonalInfo\\LastName';
	}
	EDD\Forms\Handler::render_fields( $fields, $customer );
	/**
	 * Allow users to add additional fields to the checkout form.
	 *
	 * @param array $customer Customer information. Note that this parameter is not in the original shortcode hook.
	 */
	do_action( 'edd_purchase_form_user_info_fields', $customer );
	?>
</fieldset>
