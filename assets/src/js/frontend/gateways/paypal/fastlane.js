/* global eddPayPalVars, paypal, jQuery */

import { showPayPalError, clearPayPalErrors } from './errors.js';
import { getCheckoutForm, collectCheckoutFields } from './base/checkout-fields.js';

// EDD PayPal Fastlane integration — accelerated checkout (card component, identity lookup, OTP).

/**
 * EDD PayPal Fastlane integration.
 *
 * Handles the Fastlane accelerated checkout experience: card component
 * rendering, email-based identity lookup with OTP, watermark, and
 * REST-based payment submission.
 */
const EDDFastlane = {
	fastlane: null,
	paymentComponent: null,
	isProcessing: false,

	/**
	 * Initializes the Fastlane checkout experience.
	 */
	init: async function() {
		if ( ! eddPayPalVars.clientToken ) {
			return;
		}

		const container = document.getElementById( 'edd-fastlane-container' );
		if ( ! container ) {
			return;
		}

		try {
			// The client token is set on the SDK script tag via PHP
			// (data-sdk-client-token attribute). The SDK reads it at load time.
			this.fastlane = await paypal.Fastlane();
			globalThis.eddPayPalFastlane = this.fastlane;

			await this.renderPaymentComponent();
			await this.renderWatermark();
			this.bindEmailLookup();
			this.bindSubmitHandler();
			this.showFastlaneUI();
		} catch ( e ) {
			// Fastlane unavailable — hide Fastlane UI, PayPal buttons still work.
			console.warn( 'PayPal Fastlane unavailable:', e );
			this.hideFastlaneUI();
		}
	},

	/**
	 * Renders the Fastlane card payment component.
	 */
	renderPaymentComponent: async function() {
		const cardContainer = document.getElementById( 'edd-fastlane-card-container' );
		if ( ! cardContainer ) {
			return;
		}

		this.paymentComponent = await this.fastlane.FastlanePaymentComponent();
		this.paymentComponent.render( '#edd-fastlane-card-container' );
	},

	/**
	 * Renders the required Fastlane watermark/branding badge.
	 */
	renderWatermark: async function() {
		const watermarkContainer = document.getElementById( 'edd-fastlane-watermark' );
		if ( ! watermarkContainer ) {
			return;
		}

		const watermark = await this.fastlane.FastlaneWatermarkComponent( {
			includeAdditionalInfo: true,
		} );
		watermark.render( '#edd-fastlane-watermark' );
	},

	/**
	 * Binds the email lookup + OTP authentication flow to the email field.
	 */
	bindEmailLookup: function() {
		const emailField = document.getElementById( 'edd-email' );
		if ( ! emailField ) {
			return;
		}

		let lastLookupEmail = '';

		emailField.addEventListener( 'blur', async () => {
			const email = emailField.value.trim();
			if ( ! email || email === lastLookupEmail ) {
				return;
			}

			lastLookupEmail = email;

			try {
				const { customerContextId } = await this.fastlane.identity.lookupCustomerByEmail( email );

				if ( customerContextId ) {
					const authResult = await this.fastlane.identity.triggerAuthenticationFlow( customerContextId );

					if ( authResult.authenticationState === 'succeeded' && authResult.profileData ) {
						this.applyProfileData( authResult.profileData );
					}
				}
			} catch ( e ) {
				// Lookup failed — not critical, user can still enter card manually.
				console.warn( 'Fastlane identity lookup failed:', e );
			}
		} );

		// Run lookup immediately if email is already populated.
		if ( emailField.value.trim() ) {
			emailField.dispatchEvent( new Event( 'blur' ) );
		}
	},

	/**
	 * Applies profile data from a successful Fastlane authentication.
	 *
	 * @param {Object} profileData The authenticated user's profile data.
	 */
	applyProfileData: function( profileData ) {
		if ( profileData.name ) {
			const form = getCheckoutForm();
			const firstNameField =
				form.querySelector( '[name="edd_first"]' ) ||
				document.getElementById( 'edd-first' );
			const lastNameField =
				form.querySelector( '[name="edd_last"]' ) ||
				document.getElementById( 'edd-last' );

			if ( firstNameField && profileData.name.firstName ) {
				this.setFieldValue( firstNameField, profileData.name.firstName );
			}
			if ( lastNameField && profileData.name.lastName ) {
				this.setFieldValue( lastNameField, profileData.name.lastName );
			}
		}
	},

	/**
	 * Sets a form field's value and dispatches input/change events.
	 *
	 * Direct `.value =` assignment does not fire form events, which means
	 * other handlers (notably the PayPal Smart Button `onInit` listeners
	 * that gate the button on `form.checkValidity()`) never see the new
	 * value and leave the button stuck disabled. Dispatch `input` and
	 * `change` so dependent handlers re-evaluate.
	 *
	 * @param {HTMLInputElement} field The input element.
	 * @param {string} value The value to set.
	 */
	setFieldValue: function( field, value ) {
		field.value = value;
		field.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		field.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	},

	/**
	 * Binds the submit handler to the Fastlane pay button.
	 */
	bindSubmitHandler: function() {
		const submitBtn = document.getElementById( 'edd-fastlane-submit' );
		if ( ! submitBtn ) {
			return;
		}

		submitBtn.addEventListener( 'click', async ( e ) => {
			e.preventDefault();

			if ( this.isProcessing ) {
				return;
			}

			// Validate the form before attempting payment.
			const form = document.getElementById( 'edd_purchase_form' );
			if ( form && ! form.reportValidity() ) {
				return;
			}

			this.isProcessing = true;
			this.clearErrors();
			this.showSpinner();

			try {
				const tokenResult = await this.paymentComponent.getPaymentToken();
				const paymentToken = tokenResult.id;
				const clientMetadataId = tokenResult.paymentSource?.card?.authenticationInsight?.clientMetadataId ?? '';

				const formData = this.collectFormData();

				const response = await fetch( eddPayPalVars.restBase + 'process-payment', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-EDD-Cart-Token': eddPayPalVars.cartToken,
						'X-EDD-Cart-Timestamp': eddPayPalVars.cartTimestamp,
					},
					body: JSON.stringify( {
						payment_token: paymentToken,
						metadata_id: clientMetadataId,
						form_data: formData,
					} ),
				} );

				const result = await response.json();

				if ( response.ok && result.redirect_url ) {
					globalThis.location = result.redirect_url;
					return;
				}

				// A removed discount changes the total, so reload to show it with the message.
				if ( result.data?.refresh ) {
					globalThis.location.reload();
					return;
				}

				// Handle error response.
				const errorMessage = result.message ?? result.data?.message ?? eddPayPalVars.defaultError;
				this.showError( errorMessage );
			} catch ( e ) {
				// Fastlane signals validation errors ("user entered bad data...") via
				// FastlaneError. Treat those as a buyer-correctable issue and surface
				// a friendly message instead of the generic fallback. Other errors
				// (network, SDK, etc.) still fall through to the default error.
				const isValidationError =
					e?.name === 'FastlaneError' ||
					/user entered bad data/i.test( e?.message ?? '' );

				if ( isValidationError ) {
					this.showError(
						eddPayPalVars.fastlaneValidationError ??
							eddPayPalVars.defaultError
					);
				} else {
					console.error( 'Fastlane payment error:', e );
					this.showError( eddPayPalVars.defaultError );
				}
			} finally {
				this.isProcessing = false;
				this.hideSpinner();
			}
		} );
	},

	/**
	 * Collects form data from the checkout form for the REST request.
	 *
	 * @returns {Object} Form field values.
	 */
	collectFormData: function() {
		return collectCheckoutFields();
	},

	/**
	 * Shows the Fastlane UI elements and divider.
	 */
	showFastlaneUI: function() {
		const submitBtn = document.getElementById( 'edd-fastlane-submit' );
		const divider = document.getElementById( 'edd-fastlane-divider' );

		if ( submitBtn ) {
			submitBtn.style.display = '';
		}
		if ( divider ) {
			divider.style.display = '';
		}
	},

	/**
	 * Hides the Fastlane UI elements when initialization fails.
	 */
	hideFastlaneUI: function() {
		const container = document.getElementById( 'edd-fastlane-container' );
		const divider = document.getElementById( 'edd-fastlane-divider' );

		if ( container ) {
			container.style.display = 'none';
		}
		if ( divider ) {
			divider.style.display = 'none';
		}
	},

	/**
	 * Shows the loading spinner.
	 */
	showSpinner: function() {
		const spinner = document.getElementById( 'edd-fastlane-spinner' );
		const submitBtn = document.getElementById( 'edd-fastlane-submit' );

		if ( spinner ) {
			spinner.style.display = '';
		}
		if ( submitBtn ) {
			submitBtn.disabled = true;
		}
	},

	/**
	 * Hides the loading spinner.
	 */
	hideSpinner: function() {
		const spinner = document.getElementById( 'edd-fastlane-spinner' );
		const submitBtn = document.getElementById( 'edd-fastlane-submit' );

		if ( spinner ) {
			spinner.style.display = 'none';
		}
		if ( submitBtn ) {
			submitBtn.disabled = false;
		}
	},

	/**
	 * Displays an error message in the PayPal errors wrapper.
	 *
	 * @param {string} message The error message or HTML.
	 */
	showError: function( message ) {
		showPayPalError( message );
	},

	/**
	 * Clears any existing error messages.
	 */
	clearErrors: function() {
		clearPayPalErrors();
	},
};

export default EDDFastlane;
