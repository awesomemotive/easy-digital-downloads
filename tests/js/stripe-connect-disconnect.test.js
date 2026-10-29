// Run from the plugin root: npm run test:unit

jest.mock( 'utils', () => ( { apiRequest: jest.fn() } ), { virtual: true } );
jest.mock(
	'@easy-digital-downloads/modal',
	() => jest.requireActual( '../../assets/src/js/utilities/modal.js' ),
	{ virtual: true }
);

const DIALOG_ID = 'edds-stripe-disconnect-dialog';
const disconnectLink = `<button type="button" class="button-link" data-dialog-id="${ DIALOG_ID }">Disconnect this account</button>`;

const load = ( respond ) => {
	document.body.innerHTML = `
		<div id="edds-stripe-connect-account"></div>
		<div id="edds-stripe-disconnect-reconnect"></div>
		<input id="edd_settings[stripe_statement_descriptor]">
		<input id="edd_settings[stripe_statement_descriptor_prefix]">
		<dialog id="${ DIALOG_ID }"></dialog>
	`;

	const showModal = jest.fn();
	document.getElementById( DIALOG_ID ).showModal = showModal;

	globalThis.wp = { domReady: ( callback ) => callback() };
	require( 'utils' ).apiRequest.mockReturnValue( respond() );

	jest.isolateModules( () => {
		require( '../../assets/src/js/admin/stripe/settings/stripe-connect' );
	} );

	return showModal;
};

// The request object the handler chains on; only the branch under test runs.
const answers = ( branch, payload ) => () => {
	const request = {
		done: ( callback ) => {
			if ( 'done' === branch ) {
				callback( payload );
			}
			return request;
		},
		fail: ( callback ) => {
			if ( 'fail' === branch ) {
				callback( payload );
			}
			return request;
		},
	};

	return request;
};

afterEach( () => {
	// A fresh body drops the click listener the modal helper bound to the old one.
	document.body = document.createElement( 'body' );
	delete globalThis.wp;
} );

test( 'a Disconnect button in the actions container opens the dialog', () => {
	const showModal = load( answers( 'done', { message: 'Connected.', status: 'success', actions: disconnectLink } ) );

	document.querySelector( '#edds-stripe-disconnect-reconnect [data-dialog-id]' ).click();

	expect( showModal ).toHaveBeenCalledTimes( 1 );
} );

test( 'a Disconnect button in the account-info container opens the dialog', () => {
	const showModal = load( answers( 'fail', { message: disconnectLink } ) );

	document.querySelector( '#edds-stripe-connect-account [data-dialog-id]' ).click();

	expect( showModal ).toHaveBeenCalledTimes( 1 );
} );
