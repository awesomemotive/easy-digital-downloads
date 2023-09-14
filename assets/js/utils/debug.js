/* global Stripe, edd_scripts, edd_stripe_vars */

export function consoleOutput( message, data ) {
	if ( 'true' !== edd_stripe_vars.debuggingEnabled ) {
		return;
	}

	console.log(
		'EDD Stripe - Debugging',
		'\n',
		'*'.repeat(message.length + 5),
		'\n',
		message,
		'\n',
		'*'.repeat(message.length + 5),
		'\n',
		JSON.stringify( data, null, 4 )
	);
}