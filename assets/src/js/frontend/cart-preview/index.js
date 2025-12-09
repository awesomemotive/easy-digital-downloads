/**
 * EDD Cart Preview - Vanilla JS + Shadow DOM Implementation
 *
 * A self-contained, accessible cart slideout with Shadow DOM encapsulation.
 *
 * @package EDD\CartPreview
 * @since   3.6.2
 */

// Import styles as string for Shadow DOM injection
// Using inline loaders for reliable string import with minification
import shadowStyles from '!!to-string-loader!css-loader!postcss-loader!sass-loader!./cart-preview.scss';

// Import reusable Hooks utility
import { Hooks } from '@easy-digital-downloads/hooks';

// Import icon utility
import { getIcon, injectIcons } from '@easy-digital-downloads/icons';

/**
 * Cart Preview Class
 *
 * Manages the cart slideout functionality with Shadow DOM encapsulation,
 * REST API integration, and full keyboard accessibility.
 */
class EDDCartPreview {
	/**
	 * Constructor
	 *
	 * @param {Object} config Configuration object from WordPress
	 */
	constructor ( config ) {
		this.config = config;
		this.hooks = new Hooks();
		this.state = {
			isOpen: false,
			isLoading: false,
			items: [],
			subtotal: '0.00',
			hasItems: false,
			itemCount: 0,
			error: null,
			token: config.token || ''
		};

		// Track in-flight requests to prevent race conditions
		this.fetchController = null;
		this.operationController = null;

		// Bind methods
		this.open = this.open.bind( this );
		this.close = this.close.bind( this );
		this.toggle = this.toggle.bind( this );
		this.handleBackdropClick = this.handleBackdropClick.bind( this );

		// Initialize
		this.init();
	}

	/**
	 * Initialize the cart preview
	 */
	init () {
		this.createShadowDOM();
		this.attachEventListeners();
		this.setupGlobalAPI();
		this.setupEDDIntegration();

		// Fetch initial cart contents
		this.fetchCartContents();
	}

	/**
	 * Create Shadow DOM structure
	 */
	createShadowDOM () {
		// Create host element
		this.hostElement = document.createElement( 'edd-cart-preview' );
		document.body.appendChild( this.hostElement );

		// Attach shadow root
		this.shadow = this.hostElement.attachShadow( { mode: 'open' } );

		// Add styles
		this.injectStyles();

		// Import and render PHP template
		this.render();

		// Create floating button
		this.createFloatingButton();
	}

	/**
	 * Inject styles into Shadow DOM
	 */
	injectStyles () {
		const style = document.createElement( 'style' );
		// Combine CSS variables with main stylesheet
		style.textContent = this.generateCSSVariables() + shadowStyles;
		this.shadow.appendChild( style );
	}

	/**
	 * Generate CSS custom properties from button colors
	 *
	 * @return {string} CSS variables stylesheet
	 */
	generateCSSVariables () {
		const colors = this.config.buttonColors || {};
		return `
			:host {
				--edd-cart-preview-color-primary: ${ colors.buttonColor || '#2271b1' };
				--edd-cart-preview-color-primary-hover: ${ colors.buttonHoverColor || '#135e96' };
				--edd-cart-preview-color-primary-text: ${ colors.buttonTextColor || '#ffffff' };
			}
		`;
	}

	/**
	 * Render the cart preview structure from PHP template
	 */
	render () {
		// Get the template from the page (rendered by PHP)
		const dialogTemplate = document.getElementById( 'edd-cart-preview-dialog-template' );

		if ( !dialogTemplate ) {
			console.error( 'EDD Cart Preview: Dialog template not found. Ensure dialog.php is loaded.' );
			return;
		}

		// Clone the template content into Shadow DOM
		// Use importNode for proper Shadow DOM support
		const templateClone = document.importNode( dialogTemplate.content, true );
		this.shadow.appendChild( templateClone );

		// Cache the item template for later use
		const itemTemplate = document.getElementById( 'edd-cart-preview-item-template' );
		if ( itemTemplate ) {
			// Store reference to item template (it's outside Shadow DOM)
			this.itemTemplate = itemTemplate;
		} else {
			this.log( 'EDD Cart Preview: Item template not found.' );
		}

		// Cache DOM references
		this.cacheElements();

		// Inject icons into template
		injectIcons( this.shadow );
	}

	/**
	 * Create the floating cart button
	 */
	createFloatingButton () {
		if ( 'none' === this.config.buttonSize ) {
			return;
		}

		const floatingButton = document.createElement( 'button' );
		floatingButton.className = `cart-preview__button--trigger cart-preview__button--trigger--${ this.config.buttonSize }`;
		if ( this.config.buttonPosition ) {
			floatingButton.className += ` cart-preview__button--trigger--${ this.config.buttonPosition }`;
		}
		floatingButton.setAttribute( 'aria-label', 'Open cart' );
		floatingButton.setAttribute( 'part', 'cart-preview-button-trigger' );
		floatingButton.innerHTML = getIcon( 'cart' );
		floatingButton.addEventListener( 'click', this.open );
		this.shadow.appendChild( floatingButton );
		this.elements.floatingButton = floatingButton;
	}

	/**
	 * Cache frequently accessed DOM elements
	 */
	cacheElements () {
		this.elements = {
			dialog: this.shadow.querySelector( '.cart-preview__dialog' ),
			closeBtn: this.shadow.querySelector( '.cart-preview__close' ),
			continueBtn: this.shadow.querySelector( '.cart-preview__continue' ),
			content: this.shadow.querySelector( '.cart-preview__content' ),
			scrollIndicator: this.shadow.querySelector( '.cart-preview__scroll-indicator' ),
			loading: this.shadow.querySelector( '.cart-preview__loading' ),
			error: this.shadow.querySelector( '.cart-preview__error' ),
			errorMessage: this.shadow.querySelector( '.cart-preview__error--message' ),
			errorDismiss: this.shadow.querySelector( '.cart-preview__error--dismiss' ),
			empty: this.shadow.querySelector( '.cart-preview__empty' ),
			items: this.shadow.querySelector( '.cart-preview__items' ),
			itemsList: this.shadow.querySelector( '.cart-preview__items-list' ),
			summaryValue: this.shadow.querySelector( '.cart-preview__summary-value' ),
			footer: this.shadow.querySelector( '.cart-preview__footer' ),
			status: this.shadow.querySelector( '[data-edd-cart-status]' )
		};
	}

	/**
	 * Attach event listeners
	 */
	attachEventListeners () {
		// Close button
		this.elements.closeBtn.addEventListener( 'click', this.close );

		// Continue shopping button
		this.elements.continueBtn.addEventListener( 'click', this.close );

		// Dialog native close event (triggered by ESC key or .close())
		this.elements.dialog.addEventListener( 'close', () => {
			this.state.isOpen = false;
			document.body.style.overflow = '';
			document.dispatchEvent( new CustomEvent( 'edd:cart-preview:closed' ) );
		} );

		// Backdrop click (click outside dialog)
		this.elements.dialog.addEventListener( 'click', this.handleBackdropClick );

		// Error dismiss
		this.elements.errorDismiss.addEventListener( 'click', () => this.clearError() );

		// Scroll detection for scroll indicator
		this.elements.content.addEventListener( 'scroll', () => this.updateScrollIndicator() );
	}

	/**
	 * Setup global API for external control
	 */
	setupGlobalAPI () {
		globalThis.eddSlideoutCart = {
			open: this.open,
			close: this.close,
			toggle: this.toggle,
			refresh: () => this.fetchCartContents(),
			getState: () => ( { ...this.state } ),
			hooks: this.hooks
		};

		// Dispatch ready event
		document.dispatchEvent( new CustomEvent( 'edd:cart-preview:ready', {
			detail: { api: globalThis.eddSlideoutCart }
		} ) );
	}

	/**
	 * Setup integration with existing EDD cart system
	 */
	setupEDDIntegration () {
		// Wait for jQuery to be available
		const setupListeners = () => {
			if ( typeof jQuery === 'undefined' ) {
				return;
			}

			const $ = jQuery;

			// Listen for item added to cart
			$( document.body ).on( 'edd_cart_item_added', async ( event, response ) => {
				this.log( 'EDD Cart: Item added', response );

				// Refresh cart contents
				await this.fetchCartContents();

				// Announce for screen readers
				if ( this.elements.status && response?.addedToCart ) {
					this.elements.status.textContent = response.addedToCart;
				}

				// Auto-open if configured
				if ( this.config.autoOpenOnAdd ) {
					this.open();
				}
			} );

			// Listen for item removed from cart
			$( document.body ).on( 'edd_cart_item_removed', async ( event, response ) => {
				this.log( 'EDD Cart: Item removed', response );

				// Refresh cart contents
				await this.fetchCartContents();
			} );

			// Listen for quantity updated
			$( document.body ).on( 'edd_quantity_updated', async ( event, response ) => {
				this.log( 'EDD Cart: Quantity updated', response );

				// Refresh cart contents
				await this.fetchCartContents();
			} );
		};

		// Try to setup immediately, or wait for jQuery
		if ( typeof jQuery !== 'undefined' ) {
			setupListeners();
		} else {
			// Poll for jQuery (it should load quickly)
			const checkJQuery = setInterval( () => {
				if ( typeof jQuery !== 'undefined' ) {
					clearInterval( checkJQuery );
					setupListeners();
				}
			}, 100 );

			// Give up after 5 seconds
			setTimeout( () => clearInterval( checkJQuery ), 5000 );
		}
	}

	/**
	 * Open the cart
	 */
	open () {
		this.state.isOpen = true;

		// Use native dialog.showModal() for built-in modal behavior
		// This handles backdrop, focus trapping, and ESC key automatically
		this.elements.dialog.showModal();

		document.body.style.overflow = 'hidden';

		// Dispatch event
		document.dispatchEvent( new CustomEvent( 'edd:cart-preview:opened' ) );
	}

	/**
	 * Close the cart
	 */
	close () {
		// Add closing class to trigger exit animation
		this.elements.dialog.classList.add( 'closing' );

		// Wait for animation to complete before actually closing
		setTimeout( () => {
			// Use native dialog.close()
			// This triggers the 'close' event which handles cleanup
			this.elements.dialog.close();
			// Remove closing class for next open
			this.elements.dialog.classList.remove( 'closing' );
		}, 400 ); // Match $transition-speed from SCSS
	}

	/**
	 * Toggle the cart
	 */
	toggle () {
		if ( this.state.isOpen ) {
			this.close();
		} else {
			this.open();
		}
	}

	/**
	 * Handle backdrop click (clicking outside the dialog)
	 *
	 * @param {MouseEvent} event
	 */
	handleBackdropClick ( event ) {
		// Check if click was on the dialog backdrop (outside the dialog content)
		if ( event.target === this.elements.dialog ) {
			const rect = this.elements.dialog.getBoundingClientRect();
			const clickedInDialog = (
				event.clientX >= rect.left &&
				event.clientX <= rect.right &&
				event.clientY >= rect.top &&
				event.clientY <= rect.bottom
			);

			// Only close if clicked outside the dialog content
			if ( !clickedInDialog ) {
				this.close();
			}
		}
	}

	/**
	 * Update the UI based on current state
	 */
	updateUI () {
		this.log( 'EDD Cart Preview: Updating UI', {
			hasItems: this.state.hasItems,
			itemCount: this.state.items.length,
			isLoading: this.state.isLoading
		} );

		// Toggle loading state
		this.elements.loading.classList.toggle( 'hidden', !this.state.isLoading );

		// Toggle empty state
		this.elements.empty.classList.toggle( 'hidden', this.state.hasItems );

		// Toggle items and footer
		this.elements.items.classList.toggle( 'hidden', !this.state.hasItems );
		this.elements.footer.classList.toggle( 'hidden', !this.state.hasItems );

		// Toggle floating button visibility
		if ( this.elements.floatingButton ) {
			this.elements.floatingButton.classList.toggle( 'hidden', !this.state.hasItems );
		}

		if ( this.state.hasItems ) {
			this.log( 'EDD Cart Preview: Rendering items', this.state.items );
			this.renderItems();
			this.elements.summaryValue.innerHTML = this.state.subtotal;

			// Check if scroll indicator should be shown
			// Use setTimeout to ensure DOM has fully rendered
			setTimeout( () => this.updateScrollIndicator(), 0 );
		}
	}

	/**
	 * Render cart items using PHP template
	 */
	renderItems () {
		// Clear existing items
		this.elements.itemsList.innerHTML = '';

		// Check if we have the item template
		if ( !this.itemTemplate ) {
			console.error( 'EDD Cart Preview: Item template not available.' );
			return;
		}

		/**
		 * Fires before rendering cart items
		 *
		 * @since 3.6.2
		 */
		this.hooks.doAction( 'beforeRenderItems' );

		// Render each item using the template
		for ( const item of this.state.items ) {
			// Apply filters to item data before rendering
			/**
			 * Filters cart item data before it's rendered
			 *
			 * Extensions can use this to modify item data or add custom properties
			 *
			 * @since 3.6.2
			 * @param {Object} item Cart item data
			 * @return {Object} Modified item data
			 */
			const processedItem = this.hooks.applyFilters( 'cartItemData', item );

			const itemElement = this.createItemElement( processedItem );
			this.elements.itemsList.appendChild( itemElement );

			// After appending to the DOM, find the actual rendered element and fire the action on it
			// The item element was a DocumentFragment, so we need to get the actual element from the DOM
			const renderedItemElement = this.elements.itemsList.querySelector( `[data-cart-key="${ item.key }"]` );

			if ( ! renderedItemElement ) {
				return;
			}

			// Collect messages from extensions
			/**
			 * Filters cart item messages
			 *
			 * Extensions can add informational messages to display below cart items.
			 * Common use cases: subscription info, renewal notices, license details.
			 *
			 * @since 3.6.2
			 * @param {Array} messages Array of message objects
			 * @param {Object} itemData The item data being rendered
			 * @return {Array} Modified messages array
			 *
			 * @example
			 * api.hooks.addFilter('cartItemMessages', function(messages, itemData) {
			 *     if (itemData.subscription_info?.display) {
			 *         messages.push({
			 *             text: itemData.subscription_info.display,
			 *             type: 'recurring'
			 *         });
			 *     }
			 *     return messages;
			 * });
			 */
			const messages = this.hooks.applyFilters( 'cartItemMessages', [], processedItem );

			// Render messages if any exist
			if ( messages.length > 0 ) {
				const messagesContainer = this.createMessagesContainer( messages );
				renderedItemElement.appendChild( messagesContainer );
			}

			/**
			 * Fires after an item is rendered
			 *
			 * Extensions can use this to add DOM modifications or additional elements
			 * For simple text messages, prefer using the 'cartItemMessages' filter instead.
			 *
			 * @since 3.6.2
			 * @param {HTMLElement} itemElement The rendered item element (now in the DOM)
			 * @param {Object} itemData The item data that was rendered
			 */
			this.hooks.doAction( 'itemRendered', renderedItemElement, processedItem );
		}

		/**
		 * Fires after rendering all cart items
		 *
		 * @since 3.6.2
		 * @param {Object} cartData Full cart data from API response
		 */
		this.hooks.doAction( 'afterRenderItems', this.state.cartData || {} );
	}

	/**
	 * Create a cart item element from template
	 *
	 * @param {Object} item Cart item data
	 * @return {HTMLElement} Populated item element
	 */
	createItemElement ( item ) {
		// Clone the item template
		const itemClone = document.importNode( this.itemTemplate.content, true );

		// Get the container element
		const itemContainer = itemClone.querySelector( '.cart-preview__item' );
		const image = itemClone.querySelector( '.cart-preview__item-image' );
		const name = itemClone.querySelector( '.cart-preview__item-name' );
		const price = itemClone.querySelector( '.cart-preview__item-price' );
		const removeBtn = itemClone.querySelector( '.cart-preview__item-remove' );
		const quantityWrapper = itemClone.querySelector( '.cart-preview__item-quantity' );
		const quantityControl = itemClone.querySelector( '.cart-preview__quantity-control' );
		const quantityDisplay = itemClone.querySelector( '.cart-preview__quantity-display' );
		const minusBtn = itemClone.querySelector( '.cart-preview__quantity-button--minus' );
		const plusBtn = itemClone.querySelector( '.cart-preview__quantity-button--plus' );
		const feesList = itemClone.querySelector( '.cart-preview__fees-list' );

		// Populate with item data
		itemContainer.dataset.cartKey = item.key;

		// Handle thumbnail
		if ( item.thumbnail ) {
			image.src = item.thumbnail;
			image.alt = item.name;
			image.style.display = '';
		}

		// Set text content (automatically escapes HTML)
		name.textContent = item.name;
		price.innerHTML = item.price; // Price is already formatted/escaped by server

		// Setup quantity control if enabled for this item
		if ( item.quantities_enabled ) {
			quantityWrapper.style.display = '';
			quantityControl.dataset.cartKey = item.key;
			quantityDisplay.textContent = item.quantity;

			// Minus button - decrease quantity
			minusBtn.addEventListener( 'click', ( e ) => {
				const cartKey = Number.parseInt( quantityControl.dataset.cartKey );
				const currentQuantity = Number.parseInt( quantityDisplay.textContent );
				if ( currentQuantity > 1 ) {
					this.updateQuantity( cartKey, currentQuantity - 1 );
				}
			} );

			// Plus button - increase quantity
			plusBtn.addEventListener( 'click', ( e ) => {
				const cartKey = Number.parseInt( quantityControl.dataset.cartKey );
				const currentQuantity = Number.parseInt( quantityDisplay.textContent );
				this.updateQuantity( cartKey, currentQuantity + 1 );
			} );

			// Disable minus button if quantity is 1
			if ( item.quantity <= 1 ) {
				minusBtn.disabled = true;
			}
		}

		// Setup fees list if any exist
		if ( item.fees && item.fees.length > 0 ) {
			feesList.style.display = '';
			for ( const fee of item.fees ) {
				const feeElement = document.createElement( 'div' );
				feeElement.className = 'cart-preview__fee';
				feeElement.innerHTML = `<strong>${ fee.label }:</strong> ${ fee.amount }`;
				feesList.appendChild( feeElement );
			}
			itemClone.querySelector( '.cart-preview__item-fees' ).style.display = '';
		}

		// Setup remove button
		removeBtn.dataset.cartKey = item.key;
		// Use the translation template from PHP and replace the placeholder with the item name
		const labelTemplate = removeBtn.dataset.labelTemplate || 'Remove %s from cart';
		const ariaLabel = labelTemplate.replace( '%s', item.name );
		removeBtn.setAttribute( 'aria-label', ariaLabel );
		removeBtn.addEventListener( 'click', ( e ) => {
			const cartKey = Number.parseInt( e.currentTarget.dataset.cartKey );
			this.removeItem( cartKey );
		} );

		// Inject icons into this cloned item
		injectIcons( itemClone );

		return itemClone;
	}

	/**
	 * Create a messages container with all messages
	 *
	 * @param {Array} messages Array of message objects with text and type properties
	 * @return {HTMLElement} Container element with all messages
	 */
	createMessagesContainer ( messages ) {
		const container = document.createElement( 'div' );
		container.className = 'cart-preview__item__messages';

		for ( const message of messages ) {
			const messageElement = document.createElement( 'div' );
			messageElement.className = 'cart-preview__item__message';

			// Add type-specific class if provided (for custom styling)
			if ( message.type ) {
				messageElement.classList.add( `cart-preview__item__message--${ message.type }` );
			}

			messageElement.textContent = message.text;
			container.appendChild( messageElement );
		}

		return container;
	}

	/**
	 * Show error message
	 *
	 * @param {string} message
	 */
	showError ( message ) {
		this.state.error = message;
		this.elements.errorMessage.textContent = message;
		this.elements.error.classList.remove( 'hidden' );
	}

	/**
	 * Clear error message
	 */
	clearError () {
		this.state.error = null;
		this.elements.error.classList.add( 'hidden' );
	}

	/**
	 * Update scroll indicator visibility based on scroll position
	 *
	 * Shows indicator when there's more content below to scroll to.
	 * Hides when scrolled to bottom, no overflow, or scrollbar is visible.
	 */
	updateScrollIndicator () {
		if ( ! this.elements.scrollIndicator || ! this.elements.content ) {
			return;
		}

		const content = this.elements.content;
		const hasOverflow = content.scrollHeight > content.clientHeight;
		const scrolledToBottom = Math.abs( content.scrollHeight - content.clientHeight - content.scrollTop ) < 5; // 5px threshold

		// Check if scrollbar is visible (offsetWidth includes scrollbar, clientWidth doesn't)
		const scrollbarVisible = content.offsetWidth > content.clientWidth;

		// Show indicator only when:
		// - There's overflow AND
		// - Not scrolled to bottom AND
		// - Scrollbar is NOT visible (redundant otherwise)
		const shouldShow = hasOverflow && ! scrolledToBottom && ! scrollbarVisible;

		this.elements.scrollIndicator.classList.toggle( 'hidden', ! shouldShow );
	}

	/**
	 * Fetch cart contents from REST API
	 */
	async fetchCartContents () {
		// Cancel any in-flight fetch to prevent race conditions
		if ( this.fetchController ) {
			this.fetchController.abort();
		}

		// Create new controller for this request
		this.fetchController = new AbortController();

		this.state.isLoading = true;
		this.updateUI();

		try {
			const response = await fetch( `${ this.config.apiBase }/contents`, {
				method: 'GET',
				headers: {
					'Content-Type': 'application/json',
					'X-EDD-Cart-Token': this.state.token,
					'X-EDD-Cart-Timestamp': this.config.timestamp,
					'X-WP-Nonce': this.config.nonce
				},
				credentials: 'same-origin',
				signal: this.fetchController.signal
			} );

			if ( !response.ok ) {
				throw new Error( 'Failed to fetch cart contents' );
			}

			const data = await response.json();
			this.log( 'EDD Cart Preview: Fetched cart data', data );
			this.updateStateFromResponse( data );
		} catch ( error ) {
			// Ignore abort errors - they're expected when a newer request is made
			if ( error.name === 'AbortError' ) {
				this.log( 'EDD Cart Preview: Fetch aborted (newer request started)' );
				return;
			}
			console.error( 'EDD Cart Preview: Fetch failed', error );
			this.showError( this.config.i18n.failedCartContents );
		} finally {
			this.state.isLoading = false;
			this.fetchController = null;
			this.updateUI();
		}
	}

	/**
	 * Remove item from cart
	 *
	 * @param {number} cartKey
	 */
	async removeItem ( cartKey ) {
		// Cancel any in-flight operation to prevent race conditions
		if ( this.operationController ) {
			this.operationController.abort();
		}

		// Create new controller for this operation
		this.operationController = new AbortController();

		this.state.isLoading = true;
		this.clearError();
		this.updateUI();

		try {
			const headers = {
				'Content-Type': 'application/json',
				'X-EDD-Cart-Token': this.state.token,
				'X-EDD-Cart-Timestamp': this.config.timestamp
			};

			// Add WordPress nonce for authenticated requests
			if ( this.config.nonce ) {
				headers['X-WP-Nonce'] = this.config.nonce;
			}

			const response = await fetch( `${ this.config.apiBase }/remove`, {
				method: 'POST',
				headers: headers,
				credentials: 'same-origin',
				body: JSON.stringify( { cart_key: cartKey } ),
				signal: this.operationController.signal
			} );

			if ( !response.ok ) {
				throw new Error( 'Failed to remove item' );
			}

			const data = await response.json();
			this.updateStateFromResponse( data );

			// Announce removal for screen readers
			if ( data.removedFromCart && this.elements.status ) {
				this.elements.status.textContent = data.removedFromCart;
			}

			// Update the purchase form for this download (if it exists on the page)
			if ( data.download_id ) {
				this.updatePurchaseForm( data.download_id );
			}

			// Dispatch event
			document.dispatchEvent( new CustomEvent( 'edd:cart-item-removed', {
				detail: { cartKey, downloadId: data.download_id, data }
			} ) );
		} catch ( error ) {
			// Ignore abort errors - they're expected when a newer operation is made
			if ( error.name === 'AbortError' ) {
				this.log( 'EDD Cart Preview: Remove operation aborted (newer operation started)' );
				return;
			}
			console.error( 'EDD Cart Preview: Remove failed', error );
			this.showError( this.config.i18n.failedRemoveItem );
		} finally {
			this.state.isLoading = false;
			this.operationController = null;
			this.updateUI();
		}
	}

	/**
	 * Update purchase form to show Add to Cart button when item is removed
	 *
	 * @param {number} downloadId
	 */
	updatePurchaseForm ( downloadId ) {
		// Find the purchase form for this download
		const form = document.querySelector( `#edd_purchase_${ downloadId }` );

		if ( !form ) {
			return;
		}

		const addToCartBtn = form.querySelector( 'button.edd-add-to-cart.edd-has-js' );
		const checkoutLink = form.querySelector( 'a.edd_go_to_checkout' );
		const priceOptions = form.querySelector( '.edd_price_options' );

		// Show the Add to Cart button
		if ( addToCartBtn ) {
			addToCartBtn.style.display = '';
			addToCartBtn.removeAttribute( 'data-edd-loading' );
		}

		// Hide the Checkout link
		if ( checkoutLink ) {
			checkoutLink.style.display = 'none';
		}

		if ( priceOptions ) {
			priceOptions.style.display = '';
		}

		this.log( `EDD Cart Preview: Updated purchase form for download ${ downloadId }` );
	}

	/**
	 * Update item quantity in cart
	 *
	 * @param {number} cartKey
	 * @param {number} quantity
	 */
	async updateQuantity ( cartKey, quantity ) {
		// Cancel any in-flight operation to prevent race conditions
		if ( this.operationController ) {
			this.operationController.abort();
		}

		// Create new controller for this operation
		this.operationController = new AbortController();

		this.state.isLoading = true;
		this.clearError();
		this.updateUI();

		try {
			const headers = {
				'Content-Type': 'application/json',
				'X-EDD-Cart-Token': this.state.token,
				'X-EDD-Cart-Timestamp': this.config.timestamp
			};

			// Add WordPress nonce for authenticated requests
			if ( this.config.nonce ) {
				headers['X-WP-Nonce'] = this.config.nonce;
			}

			const response = await fetch( `${ this.config.apiBase }/update-quantity`, {
				method: 'POST',
				headers: headers,
				credentials: 'same-origin',
				body: JSON.stringify( { cart_key: cartKey, quantity: quantity } ),
				signal: this.operationController.signal
			} );

			if ( !response.ok ) {
				throw new Error( 'Failed to update quantity' );
			}

			const data = await response.json();
			this.updateStateFromResponse( data );

			// Dispatch event
			document.dispatchEvent( new CustomEvent( 'edd:cart-quantity-updated', {
				detail: { cartKey, quantity, data }
			} ) );
		} catch ( error ) {
			// Ignore abort errors - they're expected when a newer operation is made
			if ( error.name === 'AbortError' ) {
				this.log( 'EDD Cart Preview: Update quantity aborted (newer operation started)' );
				return;
			}
			console.error( 'EDD Cart Preview: Update quantity failed', error );
			this.showError( this.config.i18n.failedUpdateQuantity );
		} finally {
			this.state.isLoading = false;
			this.operationController = null;
			this.updateUI();
		}
	}

	/**
	 * Update state from API response
	 *
	 * @param {Object} data
	 */
	updateStateFromResponse ( data ) {
		this.log( 'EDD Cart Preview: Updating state from response', data );

		// Handle both response formats: nested cart object or flat
		const cartData = data.cart || data;

		this.state.items = cartData.items || [];
		this.state.subtotal = cartData.subtotal || '0.00';
		this.state.hasItems = cartData.has_items || false;
		this.state.itemCount = cartData.item_count || 0;

		// Store full cart data for extensions to access
		this.state.cartData = cartData;

		// Update token AND timestamp together to keep them in sync
		if ( data.token ) {
			this.state.token = data.token;
			// The token is tied to the timestamp, so we need to update both
			if ( data.timestamp ) {
				this.config.timestamp = data.timestamp;
			}
		}

		this.log( 'EDD Cart Preview: New state', this.state );
	}

	/**
	 * Escape HTML to prevent XSS
	 *
	 * @param {string} text
	 * @return {string}
	 */
	escapeHtml ( text ) {
		const div = document.createElement( 'div' );
		div.textContent = text;
		return div.innerHTML;
	}

	/**
	 * Log a message if script debug is enabled.
	 *
	 * @param {string} message
	 */
	log ( message ) {
		if ( this.config.debug ) {
			console.log( message );
		}
	}
}

// Initialize when DOM is ready
if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', () => {
		if ( globalThis.eddCartPreviewConfig ) {
			new EDDCartPreview( globalThis.eddCartPreviewConfig );
		}
	} );
} else if ( globalThis.eddCartPreviewConfig ) {
	new EDDCartPreview( globalThis.eddCartPreviewConfig );
}
