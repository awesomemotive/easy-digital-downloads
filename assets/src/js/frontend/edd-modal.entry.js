/**
 * EDD Modal Web Component
 *
 * Provides a reusable modal system using Web Components with Shadow DOM for style isolation.
 *
 * @package EDD/Assets/JS
 * @since   3.5.3
 */

/**
 * EDD Modal Custom Element.
 *
 * @since 3.5.3
 */
class EDDModalElement extends HTMLElement {
	constructor () {
		super();

		// Attach shadow DOM for complete style isolation
		this.attachShadow( { mode: 'open' } );

		// Get localized strings and colors (with fallbacks)
		const config = globalThis.eddModal || {
			close: 'Close',
			buttonColor: '#333',
			buttonHoverColor: '#222',
			buttonTextColor: '#ffffff'
		};

		// Apply theme colors as CSS custom properties to the host element
		// These will be inherited by the Shadow DOM
		this.style.setProperty( '--edd-modal-button-bg', config.buttonColor );
		this.style.setProperty( '--edd-modal-button-hover-bg', config.buttonHoverColor );
		this.style.setProperty( '--edd-modal-button-text', config.buttonTextColor );

		// Build the shadow DOM structure with inline styles
		this.shadowRoot.innerHTML = `
			<style>${ this.getStyles() }</style>
			<dialog class="edd-modal" part="dialog">
				<button type="button" class="edd-modal__close edd-modal__close-button" aria-label="${ config.close }" part="close">&times;</button>
				<div class="edd-modal__content" part="content"></div>
				<div class="edd-loading" style="display: none;" part="loading"></div>
			</dialog>
		`;

		// Cache DOM references from shadow root
		this.dialog = this.shadowRoot.querySelector( 'dialog' );
		this.loading = this.shadowRoot.querySelector( '.edd-loading' );
		this.contentContainer = this.shadowRoot.querySelector( '.edd-modal__content' );
	}

	/**
	 * Called when element is added to the DOM.
	 *
	 * @since 3.5.3
	 * @return {void}
	 */
	connectedCallback () {
		this.setupEventListeners();
	}

	/**
	 * Called when element is removed from the DOM.
	 *
	 * @since 3.5.3
	 * @return {void}
	 */
	disconnectedCallback () {
		// Cleanup is handled automatically by the browser
	}

	/**
	 * Get component styles.
	 *
	 * Returns all CSS styles as a string to be injected into Shadow DOM.
	 * This provides complete style isolation from theme/plugin CSS.
	 *
	 * @since 3.5.3
	 * @return {string} CSS styles.
	 */
	getStyles () {
		return `
			:host {
				display: contents;
			}

			/** Native Dialog Element **/
			dialog.edd-modal {
				background: #fff;
				border: none;
				border-radius: 5px;
				max-width: calc(100vw - 32px);
				width: calc(100% - 32px);
				padding: 24px;
				position: fixed;
				margin: auto;
				box-sizing: border-box;
			}

			@media (min-width: 426px) {
				dialog.edd-modal {
					max-width: 350px;
					width: calc(100% - 48px);
				}
			}

			/* Dark backdrop behind the dialog */
			dialog.edd-modal::backdrop {
				background: rgba(0, 0, 0, 0.6);
			}

			.edd-loading {
				display: block;
				margin: 0 auto;
				animation: 1.5s linear infinite edd-spinning;
				animation-play-state: inherit;
				border: 2px solid #fff;
				border-bottom-color: #555;
				border-radius: 100%;
				content: "";
				width: 24px;
				height: 24px;
				will-change: transform;
			}

			@keyframes edd-spinning {
				from {
					transform: rotate(0deg);
				}
				to {
					transform: rotate(360deg);
				}
			}

			input[type='text'],
			input[type='email'],
			input[type='password'] {
				box-sizing: border-box;
				width: 100%;
				padding: 8px;
			}

			button,
			a.button {
				background: var(--edd-modal-button-bg, #0073aa);
				color: var(--edd-modal-button-text, #ffffff);
				border: none;
				border-radius: 5px;
				padding: 12px;
				width: 100%;
				cursor: pointer;
				transition: background 0.2s ease;
			}

			button:hover,
			a.button:hover {
				background: var(--edd-modal-button-hover-bg, #005a8a);
			}

			label {
				font-weight: bold;
				display: block;
				line-height: 1;
				margin: 0 0 5px;
			}

			input + label {
				display: inline-block;
				padding-left: 5px;
			}

			fieldset {
				border: none;
				padding: 0;
				margin: 0;
			}

			.edd-hidden {
				display: none;
			}

			/** Modal Close Button **/
			button.edd-modal__close {
				position: absolute;
				top: 4px;
				right: 4px;
				background: none;
				border: none;
				color: #999;
				font-size: 20px;
				line-height: 1;
				text-align: center;
				width: 24px !important;
				height: 24px;
				padding: 0;
				cursor: pointer;
				transition: color 0.2s ease;
				display: flex;
				align-items: center;
				justify-content: center;
			}

			button.edd-modal__close:hover,
			button.edd-modal__close:focus {
				color: #555;
			}

			/** Modal Content **/
			.edd-modal__content {
				text-align: center;
			}

			/** Modal Messages **/
			.edd-modal__message {
				border-radius: 5px;
				margin: 0 0 21px;
				padding: 10px;
			}

			.edd-modal__message p {
				margin: 0;
				padding: 0;
			}

			.edd-modal__message--error {
				border-radius: 5px;
				margin: 0 0 21px;
				padding: 10px;
				border: 1px solid #e6db55;
				background: #ffffe0;
				color: #333;
			}

			.edd-modal__message--error p {
				margin: 0;
				padding: 0;
			}

			.edd-modal__message--success {
				border-radius: 5px;
				margin: 0 0 21px;
				padding: 10px;
				border: 1px solid #5b9a68;
				background: #c8e7d2;
				color: #2c3e50;
			}

			.edd-modal__message--success p {
				margin: 0;
				padding: 0;
			}

			/** Modal Countdown **/
			.edd-modal__countdown {
				font-weight: bold;
				display: block;
				margin: 10px 0;
				color: #666;
			}
		`;
	}

	/**
	 * Setup event listeners.
	 *
	 * @since 3.5.3
	 * @return {void}
	 */
	setupEventListeners () {
		// Close button clicks (using event delegation for both X button and content close buttons)
		this.dialog.addEventListener( 'click', ( e ) => {
			// Check if click was on a close button
			if ( e.target.closest( '.edd-modal__close-button' ) ) {
				this.close();
				return;
			}

			// Close when clicking the dialog backdrop (outside the dialog content)
			const rect = this.dialog.getBoundingClientRect();
			if (
				e.clientX < rect.left ||
				e.clientX > rect.right ||
				e.clientY < rect.top ||
				e.clientY > rect.bottom
			) {
				this.close();
			}
		} );

		// Native <dialog> handles Escape key automatically
		// Listen to the 'cancel' event for custom handling if needed
		this.dialog.addEventListener( 'cancel', ( e ) => {
			e.preventDefault();
			this.close();
		} );
	}

	/**
	 * Open the modal.
	 *
	 * @since 3.5.3
	 * @param {string} content Optional HTML content to display.
	 * @return {void}
	 */
	open ( content ) {
		if ( content ) {
			this.setContent( content );
		}

		// Use native showModal() - handles backdrop, focus trap, and accessibility
		this.dialog.showModal();

		// Dispatch custom event for integration
		this.dispatchEvent( new CustomEvent( 'edd-modal-opened', {
			bubbles: true,
			composed: true
		} ) );
	}

	/**
	 * Close the modal.
	 *
	 * @since 3.5.3
	 * @return {void}
	 */
	close () {
		this.hideLoading();
		// Use native close() method
		this.dialog.close();

		// Dispatch custom event for integration
		this.dispatchEvent( new CustomEvent( 'edd-modal-closed', {
			bubbles: true,
			composed: true
		} ) );
	}

	/**
	 * Set modal content.
	 *
	 * @since 3.5.3
	 * @param {string} content HTML content to display.
	 * @return {void}
	 */
	setContent ( content ) {
		if ( this.contentContainer ) {
			this.contentContainer.innerHTML = content;
		}
	}

	/**
	 * Show loading spinner.
	 *
	 * @since 3.5.3
	 * @return {void}
	 */
	showLoading () {
		if ( this.loading ) {
			this.loading.style.display = 'block';
			this.loading.style.opacity = '1';
		}
	}

	/**
	 * Hide loading spinner.
	 *
	 * @since 3.5.3
	 * @return {void}
	 */
	hideLoading () {
		if ( this.loading ) {
			this.loading.style.display = 'none';
			this.loading.style.opacity = '0';
		}
	}

	/**
	 * Check if modal is currently open.
	 *
	 * @since 3.5.3
	 * @return {boolean} True if modal is open.
	 */
	isOpen () {
		return this.dialog.open || false;
	}

	/**
	 * Get the dialog element.
	 *
	 * @since 3.5.3
	 * @return {HTMLDialogElement|null} The dialog element.
	 */
	getDialog () {
		return this.dialog;
	}

	/**
	 * Get the modal content container element.
	 *
	 * @since 3.5.3
	 * @return {HTMLElement|null} The content container element.
	 */
	getContentContainer () {
		return this.contentContainer;
	}
}

// Register the custom element
customElements.define( 'edd-modal', EDDModalElement );

/**
 * Backward compatibility wrapper class.
 *
 * Provides the same API as the original EDDModal class but works with the new Web Component.
 *
 * @since 3.5.3
 */
class EDDModal {
	/**
	 * Constructor.
	 *
	 * @since 3.5.3
	 * @param {string} dialogId The ID of the dialog element.
	 */
	constructor ( dialogId ) {
		this.dialogId = dialogId;
		this.element = document.getElementById( dialogId );

		if ( !this.element ) {
			console.error( 'EDDModal: Element not found with ID:', dialogId );
			return;
		}

		if ( this.element.tagName.toLowerCase() !== 'edd-modal' ) {
			console.error( 'EDDModal: Element must be an <edd-modal> custom element' );
		}
	}

	/**
	 * Open the modal.
	 *
	 * @since 3.5.3
	 * @param {string} content Optional HTML content to display.
	 * @return {void}
	 */
	open ( content ) {
		this.element?.open( content );
	}

	/**
	 * Close the modal.
	 *
	 * @since 3.5.3
	 * @return {void}
	 */
	close () {
		this.element?.close();
	}

	/**
	 * Set modal content.
	 *
	 * @since 3.5.3
	 * @param {string} content HTML content to display.
	 * @return {void}
	 */
	setContent ( content ) {
		this.element?.setContent( content );
	}

	/**
	 * Show loading spinner.
	 *
	 * @since 3.5.3
	 * @return {void}
	 */
	showLoading () {
		this.element?.showLoading();
	}

	/**
	 * Hide loading spinner.
	 *
	 * @since 3.5.3
	 * @return {void}
	 */
	hideLoading () {
		this.element?.hideLoading();
	}

	/**
	 * Check if modal is currently open.
	 *
	 * @since 3.5.3
	 * @return {boolean} True if modal is open.
	 */
	isOpen () {
		return this.element?.isOpen() || false;
	}

	/**
	 * Get the dialog element.
	 *
	 * @since 3.5.3
	 * @return {HTMLDialogElement|null} The dialog element.
	 */
	getDialog () {
		return this.element?.getDialog();
	}

	/**
	 * Get the modal content container element.
	 *
	 * @since 3.5.3
	 * @return {HTMLElement|null} The content container element.
	 */
	getContentContainer () {
		return this.element?.getContentContainer();
	}

	/**
	 * Query for an element within the modal's content.
	 *
	 * @since 3.5.3
	 * @param {string} selector CSS selector to query.
	 * @return {HTMLElement|null} The found element or null.
	 */
	querySelector ( selector ) {
		return this.element?.getContentContainer()?.querySelector( selector );
	}
}

// Export for use in other scripts
globalThis.EDDModal = EDDModal;
