/* global wpApiSettings, ajaxurl */

/**
 * Log Settings JavaScript
 *
 * Handles the log pruning settings page interactions including:
 * - Manual pruning via REST API with batch processing
 * - AJAX saving of number inputs (batch size, days)
 *
 * @since 3.6.4
 */

/**
 * Initialize log settings functionality.
 */
function initLogSettings() {
	initPruneConfirmHandler();
	initNumberInputHandlers();
	initMainToggleHandler();
}

/**
 * Handle the confirm button in the prune modal.
 *
 * Uses event delegation to handle dynamically added modal content.
 */
function initPruneConfirmHandler() {
	document.addEventListener( 'click', function ( event ) {
		const confirmButton = event.target.closest( '#edd-confirm-prune-logs' );
		if ( ! confirmButton ) {
			return;
		}

		event.preventDefault();

		const modal = document.querySelector( '.edd-promo-notice' );
		const resultElement = modal?.querySelector( '#edd-prune-logs-result' );
		const logType = confirmButton.dataset.logType;
		const days = confirmButton.dataset.days;
		const defaultText = confirmButton.dataset.defaultText;
		const updatingText = confirmButton.dataset.updatingText;
		let totalDeleted = 0;

		/**
		 * Reset button to default state.
		 */
		function resetButton() {
			confirmButton.disabled = false;
			confirmButton.classList.remove( 'updating-message' );
			confirmButton.textContent = defaultText;
		}

		/**
		 * Show result message.
		 *
		 * @param {string} message The message to display.
		 * @param {string} color   The color for the message.
		 */
		function showResult( message, color ) {
			if ( resultElement ) {
				resultElement.innerHTML = `<span style="color: ${ color };">${ message }</span>`;
				resultElement.classList.remove( 'edd-hidden' );
			}
		}

		/**
		 * Close modal and reload page.
		 */
		function closeModalAndReload() {
			if ( modal ) {
				modal.classList.remove( 'edd-promo-notice--is-visible' );
			}
			location.reload();
		}

		// Disable button and show updating state.
		confirmButton.disabled = true;
		confirmButton.classList.add( 'updating-message' );
		confirmButton.textContent = updatingText;

		if ( resultElement ) {
			resultElement.innerHTML = '';
			resultElement.classList.add( 'edd-hidden' );
		}

		/**
		 * Process a batch of logs via REST API.
		 *
		 * Recursively calls itself until no more logs to delete.
		 */
		function processBatch() {
			fetch( wpApiSettings.root + 'edd/v3/logs/prune', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': wpApiSettings.nonce,
				},
				credentials: 'same-origin',
				body: JSON.stringify( {
					log_type: logType,
					days: days,
				} ),
			} )
				.then( ( response ) => response.json() )
				.then( ( data ) => {
					if ( data.success && data.count > 0 ) {
						totalDeleted += data.count;

						// Update progress message.
						showResult(
							`${ eddLogSettings.i18n.deleting } ${ totalDeleted } ${ eddLogSettings.i18n.deletedSoFar }`,
							'#46b450'
						);

						// Process next batch.
						processBatch();
					} else if ( data.success && data.count === 0 ) {
						// All done - hide the action buttons.
						const actionsContainer = modal?.querySelector( '.edd-promo-notice__actions' );
						if ( actionsContainer ) {
							actionsContainer.classList.add( 'edd-hidden' );
						}

						if ( totalDeleted > 0 ) {
							const message = totalDeleted === 1
								? eddLogSettings.i18n.oneDeleted
								: eddLogSettings.i18n.manyDeleted.replace( '%d', totalDeleted );
							showResult( message, '#46b450' );
						} else {
							showResult( eddLogSettings.i18n.noLogsFound, '#46b450' );
						}

						// Close modal after 2 seconds and reload page to update counts.
						setTimeout( closeModalAndReload, 2000 );
					} else {
						// Error response.
						resetButton();
						showResult(
							data.message || eddLogSettings.i18n.errorOccurred,
							'#dc3232'
						);
					}
				} )
				.catch( () => {
					resetButton();
					showResult( eddLogSettings.i18n.errorTryAgain, '#dc3232' );
				} );
		}

		// Start processing.
		processBatch();
	} );
}

/**
 * Handle blur event for number inputs with AJAX save.
 */
function initNumberInputHandlers() {
	// Store original values on page load.
	document.querySelectorAll( '.edd-log-pruning-number' ).forEach( ( input ) => {
		input.dataset.originalValue = input.value;
	} );

	// Update the days data attribute when the input changes.
	document.querySelectorAll( 'input[type="number"]' ).forEach( ( input ) => {
		input.addEventListener( 'change', function () {
			const row = this.closest( 'tr' );
			const pruneButton = row?.querySelector( '.edd-prune-now' );
			if ( pruneButton ) {
				pruneButton.dataset.value = this.value;
			}
		} );
	} );

	// Handle blur event for number inputs with AJAX save.
	document.querySelectorAll( '.edd-log-pruning-number' ).forEach( ( input ) => {
		input.addEventListener( 'blur', function () {
			const setting = this.dataset.setting;
			const nonce = this.dataset.nonce;
			const value = this.value;
			const originalValue = this.dataset.originalValue;

			// Skip if no change.
			if ( value === originalValue ) {
				return;
			}

			// Store the new value as original for next comparison.
			this.dataset.originalValue = value;

			// Add updating indicator.
			this.classList.add( 'updating' );

			const formData = new FormData();
			formData.append( 'action', 'edd_update_log_pruning_number' );
			formData.append( 'setting', setting );
			formData.append( 'value', value );
			formData.append( 'nonce', nonce );

			fetch( ajaxurl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData,
			} )
				.then( ( response ) => response.json() )
				.then( ( response ) => {
					this.classList.remove( 'updating' );

					if ( response.success ) {
						// Update the input with the sanitized value from server.
						this.value = response.data.value;
						this.dataset.originalValue = response.data.value;

						// Update the Prune Now button's data-value so the modal uses the new value.
						// Must update both DOM and jQuery's data cache since promo notice uses jQuery.data().
						const row = this.closest( 'tr' );
						const pruneButton = row?.querySelector( '.edd-prune-now' );
						if ( pruneButton ) {
							pruneButton.dataset.value = response.data.value;
							// Update jQuery's internal cache (required because promo notice uses .data()).
							if ( typeof jQuery !== 'undefined' ) {
								jQuery( pruneButton ).data( 'value', response.data.value );
							}
						}

						// Brief success flash.
						this.classList.add( 'edd-saved' );
						setTimeout( () => {
							this.classList.remove( 'edd-saved' );
						}, 1000 );
					}
				} )
				.catch( () => {
					this.classList.remove( 'updating' );
				} );
		} );
	} );
}

/**
 * Handle the main pruning toggle to enable/disable individual log type controls.
 *
 * When the main "Enable Automatic Pruning" toggle is changed, this updates
 * the disabled state of all individual log type toggles and days inputs.
 */
function initMainToggleHandler() {
	const mainToggle = document.querySelector( 'input[name="log_pruning_enabled"]' );
	if ( ! mainToggle ) {
		return;
	}

	/**
	 * Update the disabled state of all log type controls.
	 *
	 * @param {boolean} enabled Whether pruning is enabled.
	 */
	function updateLogTypeControls( enabled ) {
		// Update all log type toggles (only those that are prunable).
		document.querySelectorAll( '.edd-log-type-toggle input[type="checkbox"]' ).forEach( ( toggle ) => {
			// Only enable/disable if the type is prunable (has data-prunable attribute).
			if ( toggle.dataset.prunable === '1' ) {
				toggle.disabled = ! enabled;
			}
		} );

		// Update all log type days inputs (only those that are prunable).
		document.querySelectorAll( '.edd-log-type-days' ).forEach( ( input ) => {
			// Only enable/disable if the type is prunable (has data-prunable attribute).
			if ( input.dataset.prunable === '1' ) {
				input.disabled = ! enabled;
			}
		} );
	}

	// Listen for changes on the main toggle.
	mainToggle.addEventListener( 'change', function () {
		updateLogTypeControls( this.checked );
	} );
}

// Initialize when document is ready.
document.addEventListener( 'DOMContentLoaded', initLogSettings );
