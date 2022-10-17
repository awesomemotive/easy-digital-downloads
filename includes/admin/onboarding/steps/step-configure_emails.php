<?php
namespace EDD\Onboarding\Steps\ConfigureEmails;

use EDD\Onboarding\Helpers;

function initialize() {
	return;
}

function save_handler() {
	exit;
}

function step_html() {
	$sections = array(
		'edd_settings_emails_main' => array(
			'email_logo',
			'from_name',
			'from_email',
		),
	);
	ob_start();
	?>
	<form method="post" action="options.php" class="edd-settings-form">
		<?php settings_fields( 'edd_settings' ); ?>
		<table class="form-table" role="presentation">
			<tbody>
				<?php echo Helpers\settings_html( Helpers\extract_settings_fields( $sections ) ); ?>
			</tbody>
		</table>
	</form>
	<?php

	return ob_get_clean();
}
