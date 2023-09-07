/* global jQuery, edd_scripts, edd_stripe_vars */

/**
 * Internal dependencies
 */
import { forEach, domReady, apiRequest } from 'utils';
import { Modal } from '../../../shared';
import { paymentMethods } from '../../payment-methods';
import { paymentForm } from '../../payment-forms/checkout'

/**
 * Adds a Download to the Cart.
 *
 * @param {number} downloadId Download ID.
 * @param {number} priceId Download Price ID.
 * @param {number} quantity Download quantity.
 * @param {string} nonce Nonce token.
 * @param {HTMLElement} addToCartForm Add to cart form.
 *
 * @return {Promise}
 */
function addToCart( downloadId, priceId, quantity, nonce, addToCartForm ) {
	const data = {
		download_id: downloadId,
		price_id: priceId,
		quantity: quantity,
		nonce,
		post_data: jQuery( addToCartForm ).serialize(),
	};

	return apiRequest( 'edds_add_to_cart', data );
}

/**
 * Empties the Cart.
 *
 * @return {Promise}
 */
function emptyCart() {
	return apiRequest( 'edds_empty_cart' );
}

/**
 * Displays the Buy Now modal.
 *
 * @param {Object} args
 * @param {number} args.downloadId Download ID.
 * @param {number} args.priceId Download Price ID.
 * @param {number} args.quantity Download quantity.
 * @param {string} args.nonce Nonce token.
 * @param {HTMLElement} args.addToCartForm Add to cart form.
 */
function buyNowModal( args ) {
	const modalContent = document.querySelector( '#edds-buy-now-modal-content' );
	const modalLoading = '<span class="edd-loading-ajax edd-loading"></span>';

	// Show modal.
	Modal.open( 'edds-buy-now', {
		/**
		 * Adds the item to the Cart when opening.
		 */
		onShow() {
			modalContent.innerHTML = modalLoading;

			const {
				downloadId,
				priceId,
				quantity,
				nonce,
				addToCartForm,
			} = args;

			addToCart(
				downloadId,
				priceId,
				quantity,
				nonce,
				addToCartForm
			)
				.then( ( { checkout } ) => {
					// Show Checkout HTML.
					modalContent.innerHTML = checkout;

					// Reinitialize core JS.
					window.EDD_Checkout.init();

					const totalEl = document.querySelector( '#edds-buy-now-modal-content .edd_cart_amount' );
					const total = parseFloat( totalEl.dataset.total );

					// Reinitialize Stripe JS if a payment is required.
					if ( total > 0 ) {
						paymentForm();
						paymentMethods();
					}
				} )
				.fail( ( { message } ) => {
					// Show error message.
					document.querySelector( '#edds-buy-now-modal-content' ).innerHTML = message;
				} );
		},
		/**
		 * Empties Cart on close.
		 */
		onClose() {
			emptyCart();
		}
	} );
}

// DOM ready.
export function setup() {

	// Find all "Buy Now" links on the page.
	forEach( document.querySelectorAll( '.edds-buy-now' ), ( el ) => {

		// Don't use modal if "Free Downloads" is active and available for this download.
		// https://easydigitaldownloads.com/downloads/free-downloads/
		if ( el.classList.contains( 'edd-free-download' ) ) {
			return;
		}

		/**
		 * Launches "Buy Now" modal when clicking "Buy Now" link.
		 *
		 * @param {Object} e Click event.
		 */
		el.addEventListener( 'click', ( e ) => {
			const { downloadId, nonce } = e.currentTarget.dataset;

			// Stop other actions if a Download ID is found.
			if ( ! downloadId ) {
				return;
			}

			e.preventDefault();
			e.stopImmediatePropagation();

			// Gather Download information.
			let priceId = null;
			let quantity = 1;

			const addToCartForm = e.currentTarget.closest(
				'.edd_download_purchase_form'
			);

			// Price ID.
			const priceIdEl = addToCartForm.querySelector(
				`.edd_price_option_${downloadId}:checked`
			);

			if ( priceIdEl ) {
				priceId = priceIdEl.value;
			}

			// Quantity.
			const quantityEl = addToCartForm.querySelector(
				'input[name="edd_download_quantity"]'
			);

			if ( quantityEl ) {
				quantity = quantityEl.value;
			}

			buyNowModal( {
				downloadId,
				priceId,
				quantity,
				nonce,
				addToCartForm
			} );
		} );

	} );

	/**
	 * Replaces submit button text after validation errors.
	 *
	 * If there are no other items in the cart the core javascript will replace
	 * the button text with the value for a $0 cart (usually "Free Download")
	 * because the script variables were constructed when nothing was in the cart.
	 */
	jQuery( document.body ).on( 'edd_checkout_error', () => {
		const submitButtonEl = document.querySelector(
			'#edds-buy-now #edd-purchase-button'
		);

		if ( ! submitButtonEl ) {
			return;
		}

		const { i18n: { completePurchase } } = edd_stripe_vars;

		const amountEl = document.querySelector( '.edd_cart_amount' );
		const { total, totalCurrency } = amountEl.dataset;

		if ( '0' === total ) {
			return;
		}

		// For some reason a delay is needed to override the value set by
		// https://github.com/easydigitaldownloads/easy-digital-downloads/blob/master/assets/js/edd-ajax.js#L414
		setTimeout( () => {
			submitButtonEl.value = `${ totalCurrency } - ${ completePurchase }`;
		}, 10 );
	} );
}
