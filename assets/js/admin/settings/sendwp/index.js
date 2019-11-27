export const sendwpRemoteInstall = () => {
	let data = {
		'action': 'edd_sendwp_remote_install',
	};

	// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
	jQuery.post(ajaxurl, data, function( response ) {

		if( ! response.success ) {
			if( confirm( response.data.error ) ) {
				location.reload();
				return;
			}
		}

		sendwpRegisterClient(
			response.data.register_url,
			response.data.client_name,
			response.data.client_secret,
			response.data.client_redirect,
			response.data.partner_id
		);

	});
}

export const sendwpDisconnect = () => {
	let data = {
		'action': 'edd_sendwp_disconnect',
	};

	jQuery.post(ajaxurl, data, function( response ) {
		location.reload();
	});
}

const sendwpRegisterClient = ( register_url, client_name, client_secret, client_redirect, partner_id ) => {

	let form = document.createElement( 'form' );
	form.setAttribute( 'method', 'POST' );
	form.setAttribute( 'action', register_url );

	const attributes = [
		[ 'client_name', client_name ],
		[ 'client_secret', client_secret ],
		[ 'client_redirect', client_redirect ],
		[ 'partner_id', partner_id ]
	];

	for ( const[ attr_name, attr_value ] of attributes ) {
		let input = document.createElement( 'input' );
		input.setAttribute( 'type', 'hidden' );
		input.setAttribute( 'name', attr_name );
		input.setAttribute( 'value', attr_value );
		form.appendChild( input );
	}

	document.body.appendChild( form );
	form.submit();

}
