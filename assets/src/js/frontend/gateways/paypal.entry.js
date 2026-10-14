/* global eddPayPalVars, edd_global_vars, paypal, edd_scripts */

// PayPal Commerce frontend: standard PayPal Buttons + Fastlane bootstrap.

import EDDFastlane from './paypal/fastlane.js';
import EDDPayPalApplePay from './paypal/applepay.js';
import EDDPayPalGooglePay from './paypal/googlepay.js';
import EDDPayPalUnbrandedCard from './paypal/unbrandedcard.js';
import { showPayPalError } from './paypal/errors.js';
import { beginLoading, endLoading } from '@easy-digital-downloads/cart-loading';

// Token for the shared checkout loading overlay while an order is being finalized.
let loadingToken = null;

// Registry of the PayPal payment methods that load via their own SDK component
// and need a JS bootstrap. Keyed by EDD method slug; each entry pairs the
// PayPal SDK global the component exposes with the module that drives it.
// Driving entry.js off this map keeps the per-cart method gate and the
// bootstrap loop reading one list instead of two hand-maintained ones.
const PAYMENT_METHODS = {
	fastlane:       { sdkGlobal: 'Fastlane',   module: EDDFastlane },
	apple_pay:      { sdkGlobal: 'Applepay',   module: EDDPayPalApplePay },
	google_pay:     { sdkGlobal: 'Googlepay',  module: EDDPayPalGooglePay },
	unbranded_card: { sdkGlobal: 'CardFields', module: EDDPayPalUnbrandedCard },
};

var EDD_PayPal = {
	isMounted: false,

	/**
	 * Locks the payment-button stack while a server round-trip is in flight.
	 *
	 * Added by the same class the wallet modules (Apple Pay / Google Pay)
	 * use, so all three paths share one visual lockdown — buttons dim and
	 * become unclickable, spinner stays animated and visible.
	 *
	 * @returns {void}
	 */
	startProcessing: function() {
		const wrap = document.getElementById( 'edd_purchase_submit' )
			|| document.getElementById( 'edd-paypal-container' )?.parentElement;
		if ( wrap ) {
			wrap.classList.add( 'edd-paypal-processing' );
		}
		const spinner = document.getElementById( 'edd-paypal-spinner' );
		if ( spinner ) {
			spinner.style.display = 'block';
		}

		// Drive the shared checkout overlay alongside PayPal's own button lockdown.
		if ( ! loadingToken ) {
			loadingToken = beginLoading( 'paypal' );
		}
	},

	/**
	 * Releases the payment-button stack so buttons are interactive again.
	 *
	 * @returns {void}
	 */
	stopProcessing: function() {
		const wrap = document.getElementById( 'edd_purchase_submit' )
			|| document.getElementById( 'edd-paypal-container' )?.parentElement;
		if ( wrap ) {
			wrap.classList.remove( 'edd-paypal-processing' );
		}
		const spinner = document.getElementById( 'edd-paypal-spinner' );
		if ( spinner ) {
			spinner.style.display = 'none';
		}

		endLoading( loadingToken );
		loadingToken = null;
	},

	/**
	 * Returns a style object adjusted for a specific PayPal funding source.
	 *
	 * The PayPal SDK rejects style.label values like `paypal`, `checkout`, and
	 * `buynow` on non-PayPal funding sources, and rejects color `gold` on
	 * Venmo and the standalone Card button. When isEligible() sees an
	 * incompatible style it returns false and the button is silently skipped.
	 *
	 * @param {Object} baseStyle The base button style (from PHP get_button_styles).
	 * @param {string} fundingSource A paypal.FUNDING.* value.
	 * @returns {Object} Cloned style object, safe for the given funding source.
	 */
	styleForFundingSource: function ( baseStyle, fundingSource ) {
		var style = Object.assign( {}, baseStyle || {} );

		// `label` only applies to the PayPal funding source.
		if ( fundingSource !== paypal.FUNDING.PAYPAL ) {
			delete style.label;
		}

		// Color compatibility differs per funding source. PayPal's SDK throws
		// during construction when given an incompatible color, so each source
		// gets remapped from the base `gold` to something it actually accepts:
		//   - PayPal / Pay Later: gold (default), blue, white, silver, black
		//   - Venmo:               blue (default), silver, black, white       (NO gold)
		//   - Card:                black (default), white                      (NO gold, NO blue)
		//   - Credit (legacy):     darkblue (default), black, white            (NO gold)
		if ( 'gold' === style.color ) {
			if ( fundingSource === paypal.FUNDING.CARD ) {
				style.color = 'black';
			} else if ( fundingSource === paypal.FUNDING.VENMO ) {
				style.color = 'blue';
			} else if ( fundingSource === paypal.FUNDING.CREDIT ) {
				style.color = 'darkblue';
			}
		}

		return style;
	},

	/**
	 * Maps PayPal SDK funding-source constants to EDD method slugs.
	 *
	 * Prefers the server-derived map (built from the payment-method registry
	 * descriptors, keyed by the SDK FUNDING string values) so the funding
	 * source → slug mapping has a single source of truth. Falls back to the
	 * SDK constants when the map isn't localized. PAYLATER and CREDIT both
	 * represent the "Pay Later" method, so they collapse to `pay_later`.
	 *
	 * @returns {Object} Funding-source constant => method slug.
	 */
	fundingSourceSlugs: function () {
		if ( eddPayPalVars && eddPayPalVars.fundingSlugMap ) {
			return eddPayPalVars.fundingSlugMap;
		}

		var map = {};
		map[ paypal.FUNDING.PAYPAL ]   = 'paypal';
		map[ paypal.FUNDING.PAYLATER ] = 'pay_later';
		map[ paypal.FUNDING.CREDIT ]   = 'pay_later';
		map[ paypal.FUNDING.VENMO ]    = 'venmo';
		map[ paypal.FUNDING.CARD ]     = 'card';
		return map;
	},

	/**
	 * Resolves the payment methods allowed to render, evaluated at the latest
	 * possible point — the button render loop and wallet bootstrap, not SDK
	 * enqueue.
	 *
	 * Seeded from the merchant's Payment Methods toggles (the funding sources
	 * the server localized as enabled) plus the wallet and Fastlane methods
	 * whose SDK components were loaded. Extensions refine the set per cart via
	 * the `edd.paypal.enabledMethods` filter — Recurring removes every method
	 * except `paypal` when the cart holds a free trial, since the card / ACDC,
	 * Venmo, Pay Later, Apple Pay, Google Pay, and Fastlane flows all run an
	 * immediate zero-amount capture PayPal rejects for a $0 trial.
	 *
	 * @param {string} context Either `checkout` or `buy_now`.
	 * @returns {string[]} Allowed EDD method slugs.
	 */
	getEnabledMethods: function ( context ) {
		var allowedFunding = ( eddPayPalVars && eddPayPalVars.enabledFundingSources ) || null;
		var slugMap = EDD_PayPal.fundingSourceSlugs();
		var methods = [];

		// Seed funding-based methods from the server list (or all when unset,
		// which is the backend default before the setting is first saved).
		Object.keys( slugMap ).forEach( function ( fundingSource ) {
			if ( allowedFunding && allowedFunding.indexOf( fundingSource ) === -1 ) {
				return;
			}
			var slug = slugMap[ fundingSource ];
			if ( methods.indexOf( slug ) === -1 ) {
				methods.push( slug );
			}
		} );

		// Wallets, Fastlane, and the unbranded card fields are attempted
		// whenever their SDK component loaded; finer device/eligibility checks
		// happen later in each module (canMakePayments() / isEligible()).
		Object.keys( PAYMENT_METHODS ).forEach( function ( slug ) {
			if ( methods.indexOf( slug ) === -1 ) {
				methods.push( slug );
			}
		} );

		/**
		 * Filters the PayPal payment methods allowed to render on this cart.
		 *
		 * Evaluated at render time so extensions can gate methods on live cart
		 * state (not just the merchant's static Payment Methods toggles).
		 *
		 * @param {string[]} methods Allowed method slugs.
		 * @param {Object}   data    { context, intent }.
		 */
		return wp.hooks.applyFilters( 'edd.paypal.enabledMethods', methods, {
			context: context,
			intent: eddPayPalVars.intent,
		} );
	},

	/**
	 * Whether a single payment method is allowed to render on this cart.
	 *
	 * @param {string} method  Method slug.
	 * @param {string} context Either `checkout` or `buy_now`.
	 * @returns {boolean}
	 */
	isMethodEnabled: function ( method, context ) {
		return EDD_PayPal.getEnabledMethods( context ).indexOf( method ) !== -1;
	},

	/**
	 * Initializes PayPal buttons and sets up some events.
	 */
	init: function() {
		if ( document.getElementById( 'edd-paypal-container' ) ) {
			this.initButtons( '#edd-paypal-container', 'checkout' );
		}

		// Refresh the page when the cart total requires it (e.g. a 100% discount).
		// The native edd:cart-updated event consolidates every cart mutation, so
		// we listen for it alone rather than the individual legacy jQuery events.
		document.addEventListener( 'edd:cart-updated', function() {
			const totalStr = document.querySelector( '.edd_cart_total .edd_cart_amount' )?.dataset.total;
			if ( undefined === totalStr ) {
				return;
			}
			const total = Number.parseFloat( totalStr );
			EDD_PayPal.maybeRefreshPageForTotal( total );
		} );
	},

	/**
	 * Determines whether or not the selected gateway is PayPal.
	 * @returns {boolean}
	 */
	isPayPal: function() {
		return document.getElementById( 'edd-paypal-container' ) ? true : false;
	},

	/**
	 * Refreshes the page when the cart total requires it (e.g. a 100% discount).
	 *
	 * @param {number} total The current cart total.
	 */
	maybeRefreshPageForTotal: function( total ) {
		if ( ! EDD_PayPal.isPayPal() ) {
			return;
		}
		if ( ! EDD_PayPal.isMounted && total > 0 ) {
			window.location.reload();
		}
		if ( 0 === total ) {
			window.location.reload();
		}
	},

	/**
	 * Sets the error HTML, depending on the context.
	 *
	 * @param {string|HTMLElement} container
	 * @param {string} context
	 * @param {string} errorHtml
	 */
	setErrorHtml: function( container, context, errorHtml ) {
		// Checkout uses the global #edd-paypal-errors-wrap; Buy Now scopes the
		// error to the form-level `.edd-paypal-checkout-buy-now-error-wrapper`.
		// The shared helper handles the markup, scope resolution, and the
		// `edd_checkout_error` jQuery event so all four PayPal entry points
		// render errors identically.
		if ( 'checkout' === context && 'undefined' !== typeof edd_global_vars && edd_global_vars.checkout_error_anchor ) {
			showPayPalError( errorHtml, 'checkout' );
		} else if ( 'buy_now' === context ) {
			showPayPalError( errorHtml, 'buy_now', container );
		}
	},

	/**
	 * Initializes PayPal buttons
	 *
	 * @param {string|HTMLElement} container Element to render the buttons in.
	 * @param {string} context   Context for the button. Either `checkout` or `buy_now`.
	 */
	initButtons: function( container, context ) {
		EDD_PayPal.isMounted = true;

		var buttonArgs = EDD_PayPal.getButtonArgs( container, context );

		// Allow extensions (e.g. Recurring's vault-setup flow for free trials) to
		// override or augment the button args before rendering. The filter runs
		// once per initButtons call; the result is then specialized per funding
		// source below.
		var filteredArgs = wp.hooks.applyFilters( 'edd.paypal.buttonArgs', buttonArgs, {
			context: context,
			container: container,
			form: ( 'checkout' === context ) ? document.getElementById( 'edd_purchase_form' ) : container.closest( '.edd_download_purchase_form' ),
			intent: eddPayPalVars.intent,
		} );

		// Render each eligible funding source as its own button. A single
		// `paypal.Buttons(...)` call only reliably renders the primary PayPal
		// button — Venmo, Pay Later, and the standalone Card button require
		// explicit per-source rendering with isEligible() checks.
		//
		// Style notes:
		//  - `label: 'paypal'` / `'checkout'` / `'buynow'` are only valid for
		//    the PayPal funding source. Passing them on Venmo/Card/PayLater
		//    causes isEligible() to return false, so we strip `label` for
		//    non-PayPal sources and let the SDK use each brand's default.
		//  - `color: 'gold'` is only supported for PayPal and Pay Later.
		//    Venmo and Card require a non-gold color (default to blue).
		// PAYLATER and CREDIT are two related but distinct funding sources in the
		// SDK — newer Pay-in-4 vs legacy PayPal Credit (revolving line). The
		// auto-stack rendering picks whichever the merchant is approved for, but
		// per-source iteration needs both listed so the eligible one renders.
		var allFundingSources = [
			paypal.FUNDING.PAYPAL,
			paypal.FUNDING.PAYLATER,
			paypal.FUNDING.CREDIT,
			paypal.FUNDING.VENMO,
			paypal.FUNDING.CARD,
		];

		// Filter to only the methods allowed to render on this cart. This is
		// the latest-possible gate: it folds the merchant's Payment Methods
		// toggles together with any per-cart overrides from the
		// `edd.paypal.enabledMethods` filter (e.g. Recurring restricting a
		// free-trial cart to the PayPal button only).
		var enabledMethods = EDD_PayPal.getEnabledMethods( context );
		var slugMap = EDD_PayPal.fundingSourceSlugs();
		var fundingSources = allFundingSources.filter( function ( src ) {
			var slug = slugMap[ src ];
			return slug && enabledMethods.indexOf( slug ) !== -1;
		} );

		// Isolate each funding source: a throw here (e.g. PayPal validating an
		// incompatible style for one button) must not halt other sources or
		// downstream callers like the Fastlane bootstrap on edd_gateway_loaded.
		fundingSources.forEach( function ( fundingSource ) {
			try {
				var sourceArgs = Object.assign( {}, filteredArgs, {
					fundingSource: fundingSource,
					style: EDD_PayPal.styleForFundingSource( filteredArgs.style, fundingSource ),
				} );

				var button = paypal.Buttons( sourceArgs );

				if ( button.isEligible() ) {
					button.render( container );
				}
			} catch ( e ) {
				console.warn( 'EDD PayPal: failed to render funding source', fundingSource, e );
			}
		} );

		// Render Pay Later messaging if available and enabled (and not gated
		// off for this cart, e.g. a free trial).
		if ( paypal.Messages && eddPayPalVars.payLaterEnabled && enabledMethods.indexOf( 'pay_later' ) !== -1 ) {
			const messagesContainer = document.getElementById( 'edd-paypal-messages' );
			if ( messagesContainer ) {
				paypal.Messages( {
					amount: eddPayPalVars.cartTotal || 0,
					placement: 'payment',
				} ).render( messagesContainer );
			}
		}

		document.dispatchEvent( new CustomEvent( 'edd_paypal_buttons_mounted' ) );
	},

	/**
	 * Retrieves the arguments used to build the PayPal button.
	 *
	 * @param {string|HTMLElement} container Element to render the buttons in.
	 * @param {string} context   Context for the button. Either `checkout` or `buy_now`.
	 */
	getButtonArgs: function ( container, context ) {
		var form = ( 'checkout' === context ) ? document.getElementById( 'edd_purchase_form' ) : container.closest( '.edd_download_purchase_form' );
		var errorWrapper = ( 'checkout' === context ) ? form.querySelector( '#edd-paypal-errors-wrap' ) : form.querySelector( '.edd-paypal-checkout-buy-now-error-wrapper' );
		var spinner = ( 'checkout' === context ) ? document.getElementById( 'edd-paypal-spinner' ) : form.querySelector( '.edd-paypal-spinner' );
		var nonceEl = form.querySelector( 'input[name="edd_process_paypal_nonce"]' );
		var tokenEl = form.querySelector( 'input[name="edd-process-paypal-token"]' );
		var createFunc = ( 'subscription' === eddPayPalVars.intent ) ? 'createSubscription' : 'createOrder';
		var requiredInputs = form.querySelectorAll( '[required]' );

		var buttonArgs = {
			onInit: function ( data, actions ) {
				actions.disable();
				if ( form.checkValidity() ) {
					actions.enable();
				}
				requiredInputs.forEach( function ( element ) {
					element.addEventListener( 'change', function ( e ) {
						if ( form.checkValidity() ) {
							actions.enable();
						} else {
							actions.disable();
						}
					} );
				} );
			},
			onClick: function ( data, actions ) {
				if ( ! form.reportValidity() ) {
					return false;
				}

				// Clear errors at the start of each attempt.
				if ( errorWrapper ) {
					errorWrapper.innerHTML = '';
				}
			},
			onApprove: function( data, actions ) {
				// Buyer approved in PayPal's popup — lock the rest of the
				// payment-button stack while we capture server-side.
				EDD_PayPal.startProcessing();

				var formData = new FormData();
				formData.append( 'action', eddPayPalVars.approvalAction );
				formData.append( 'edd_process_paypal_nonce', nonceEl.value );
				formData.append( 'token', tokenEl.getAttribute('data-token') );
				formData.append( 'timestamp', tokenEl.getAttribute('data-timestamp' ) );

				if ( data.orderID ) {
					formData.append( 'paypal_order_id', data.orderID );
				}
				if ( data.subscriptionID ) {
					formData.append( 'paypal_subscription_id', data.subscriptionID );
				}

				return fetch( edd_scripts.ajaxurl, {
					method: 'POST',
					body: formData
				} ).then( function( response ) {
					return response.json();
				} ).then( function( responseData ) {
					if ( responseData.success && responseData.data.redirect_url ) {
						window.location = responseData.data.redirect_url;
					} else {
						// A removed discount changes the total, so reload to show it with the message.
						if ( responseData.data.refresh ) {
							window.location.reload();
							return;
						}

						EDD_PayPal.stopProcessing();

						var errorHtml = responseData.data.message ? responseData.data.message : eddPayPalVars.defaultError;

						EDD_PayPal.setErrorHtml( container, context, errorHtml );

						// @link https://developer.paypal.com/docs/checkout/integration-features/funding-failure/
						if ( responseData.data.retry ) {
							return actions.restart();
						}
					}
				} );
			},
			onError: function( error ) {
				EDD_PayPal.stopProcessing();

				EDD_PayPal.setErrorHtml( container, context, error );
			},
			onCancel: function( data ) {
				EDD_PayPal.stopProcessing();

				const formData = new FormData();
				formData.append( 'action', 'edd_cancel_paypal_order' );
				return fetch( edd_scripts.ajaxurl, {
					method: 'POST',
					body: formData
				} ).then( function ( response ) {
					return response.json();
				} ).then( function ( responseData ) {
					if ( responseData.success ) {
						const nonces = responseData.data.nonces;
						Object.keys( nonces ).forEach( function ( key ) {
							var gatewaySelector = document.getElementById( 'edd-gateway-' + key );
							if ( gatewaySelector ) {
								gatewaySelector.setAttribute( 'data-' + key + '-nonce', nonces[ key ] );
							}
						} );
					}
				} );
			}
		};

		/*
		 * Add style if we have any
		 *
		 * @link https://developer.paypal.com/docs/checkout/integration-features/customize-button/
		 */
		if ( eddPayPalVars.style ) {
			buttonArgs.style = eddPayPalVars.style;
		}

		/*
		 * Add the `create` logic. This gets added to `createOrder` for one-time purchases
		 * or `createSubscription` for recurring.
		 */
		buttonArgs[ createFunc ] = function ( data, actions ) {
			// Show spinner — full processing lockdown happens on onApprove
			// once the buyer authorizes inside PayPal's popup.
			spinner.style.display = 'block';

			// Clear errors at the start of each attempt.
			if ( errorWrapper ) {
				errorWrapper.innerHTML = '';
			}

			// Submit the form via AJAX.
			return fetch( edd_scripts.ajaxurl, {
				method: 'POST',
				body: new FormData( form )
			} ).then( function( response ) {
				return response.json();
			} ).then( function( orderData ) {
				if ( orderData.data && orderData.data.paypal_order_id ) {

					// Add the nonce to the form so we can validate it later.
					if ( orderData.data.nonce ) {
						nonceEl.value = orderData.data.nonce;
					}

					// Add the token to the form so we can validate it later.
					if ( orderData.data.token ) {
						jQuery(tokenEl).attr( 'data-token', orderData.data.token );
						jQuery(tokenEl).attr( 'data-timestamp', orderData.data.timestamp );
					}

					return orderData.data.paypal_order_id;
				} else {
					// Error message.
					var errorHtml = eddPayPalVars.defaultError;
					if ( orderData.data && 'string' === typeof orderData.data ) {
						errorHtml = orderData.data;
					} else if ( 'string' === typeof orderData ) {
						errorHtml = orderData;
					}

					return new Promise( function( resolve, reject ) {
						reject( errorHtml );
					} );
				}
			} );
		};

		return buttonArgs;
	}
};

// Expose Fastlane on the global scope for backwards compatibility with any
// external code that referenced the EDD_Fastlane global.
globalThis.EDD_Fastlane = EDDFastlane;

/**
 * Initialize on checkout.
 */
jQuery( document.body ).on( 'edd_gateway_loaded', function( e, gateway ) {
	if ( 'paypal_commerce' !== gateway ) {
		return;
	}

	EDD_PayPal.init();

	// Bootstrap each component-based method (Fastlane, the wallets, and the
	// unbranded card fields) that's enabled for this cart and whose SDK
	// component actually loaded. Each module self-gates further on
	// device/eligibility (canMakePayments() / isEligible()); this only avoids
	// calling init() when the component is absent (the server omitted it) or
	// the method is gated off for this cart (e.g. a free trial, which can
	// only use the PayPal button).
	if ( typeof paypal !== 'undefined' ) {
		Object.keys( PAYMENT_METHODS ).forEach( function ( slug ) {
			var method = PAYMENT_METHODS[ slug ];
			if ( 'function' === typeof paypal[ method.sdkGlobal ] && EDD_PayPal.isMethodEnabled( slug, 'checkout' ) ) {
				method.module.init();
			}
		} );
	}
} );

/**
 * Initialize Buy Now buttons.
 */
jQuery( document ).ready( function( $ ) {
	EDDPayPalBuyNowbuttons();
} );

export function EDDPayPalBuyNowbuttons() {
	var buyButtons = document.querySelectorAll( '.edd-paypal-checkout-buy-now' );
	for ( var i = 0; i < buyButtons.length; i++ ) {
		var element = buyButtons[ i ];
		// Skip if "Free Downloads" is enabled for this download.
		if ( element.classList.contains( 'edd-free-download' ) ) {
			continue;
		}

		var wrapper = element.closest( '.edd_purchase_submit_wrapper' );
		if ( ! wrapper ) {
			continue;
		}

		// Find the closest input with a class of edd_action_input and get it's value.
		var edd_input_action = element.closest( 'form' ).querySelector( '.edd_action_input' ).value;
		if ( 'add_to_cart' === edd_input_action ) {
			continue;
		}

		// Clear contents of the wrapper.
		wrapper.innerHTML = '';

		// Add error container after the wrapper.
		var errorNode = document.createElement( 'div' );
		errorNode.classList.add( 'edd-paypal-checkout-buy-now-error-wrapper' );
		wrapper.before( errorNode );

		// Add spinner container.
		var spinnerWrap = document.createElement( 'span' );
		spinnerWrap.classList.add( 'edd-paypal-spinner', 'edd-loading-ajax', 'edd-loading' );
		spinnerWrap.style.display = 'none';
		wrapper.after( spinnerWrap );

		// Initialize button.
		EDD_PayPal.initButtons( wrapper, 'buy_now' );
	}
}
