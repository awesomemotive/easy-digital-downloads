export const jiltRemoteInstall = () => {
	var data = {
		'action': 'edd_jilt_remote_install',
	};

	jQuery.post( ajaxurl, data, function( response ) {

		if( ! response.success ) {

			if( confirm( response.data.error ) ) {
				location.reload();
				return;
			}
		}

		jiltConnect();
	});
}

const jiltConnect = () => {
	var data = {
		'action': 'edd_jilt_connect',
	};

	jQuery.post( ajaxurl, data, function( response ) {

		if( ! response.success ) {

			if( confirm( response.data.error ) ) {
				location.reload();
				return;
			}
		}

		if ( response.data.connect_url !== '' ) {

			location.assign( response.data.connect_url );
			return;
		}
	});
}

export const jiltDisconnect = () => {
	var data = {
		'action': 'edd_jilt_disconnect',
	};

	jQuery.post( ajaxurl, data, function( response ) {

		if ( ! response.success ) {
			confirm( response.data.error );
		}

		location.reload();
	});
}
