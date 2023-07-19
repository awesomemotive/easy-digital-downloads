export const recaptureRemoteInstall = () => {
	var data = {
		'action': 'edd_recapture_remote_install',
	};

	jQuery.post( ajaxurl, data, function( response ) {
		if ( !response.success ) {

			if ( response.data && confirm( response.data.error ) ) {
				location.reload();
				return;
			}
		}

		window.location.href = 'https://recapture.io/register';
	});
}
