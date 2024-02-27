/**
 * Internal dependencies
 */
import { domReady, apiRequest } from 'utils';

// Wait for DOM.
domReady( () => {
	const containerEl = document.getElementById( 'edds-stripe-connect-account' );
	const actionsEl = document.getElementById( 'edds-stripe-disconnect-reconnect' );


	if ( ! containerEl ) {
		return;
	}

	/*
	 * Do not make a request, if we are inside Onboarding Wizard.
	 * Onboarding Wizard will make it's own call.
	*/
	if ( containerEl.hasAttribute('data-onboarding-wizard') ) {
		return;
	}

	return apiRequest( 'edds_stripe_connect_account_info', {
		...containerEl.dataset,
	} )
		.done( ( response ) => {
			containerEl.classList.remove( 'loading' );
			containerEl.innerHTML = response.message;
			containerEl.classList.add( `notice-${ response.status }` );

			actionsEl.classList.remove( 'loading' );
			if ( response.actions ) {
				actionsEl.innerHTML = response.actions;
			}

			const statement_descriptor_target = document.getElementById( 'edd_settings[stripe_statement_descriptor]' ),
			statement_descriptor_prefix_target = document.getElementById( 'edd_settings[stripe_statement_descriptor_prefix]' );

			statement_descriptor_target.classList.remove( 'edd-text-loading' );
			statement_descriptor_prefix_target.classList.remove( 'edd-text-loading' );
			if ( response.account ) {
				const statement_descriptor = response.account.settings.payments.statement_descriptor || '',
				statement_descriptor_prefix = response.account.settings.card_payments.statement_descriptor_prefix || '';

				statement_descriptor_target.value = statement_descriptor || '';
				statement_descriptor_prefix_target.value = statement_descriptor_prefix || '';
			}
		} )
		.fail( ( error ) => {
			containerEl.innerHTML = error.message;
			containerEl.classList.add( 'notice-error' );
		} );
} );
