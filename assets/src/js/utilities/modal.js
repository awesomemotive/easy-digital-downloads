/**
 * Base utility for admin modals using native <dialog class="edd-modal">.
 *
 * Shared by email tags inserter, email style formats, and Cart Recovery (and other
 * admin dialogs that use the edd-modal CSS pattern). Handles open, close, close
 * button, and backdrop click. Esc is handled natively by the dialog.
 *
 * @package EDD\Utilities
 */

/**
 * Binds a trigger element to an edd-modal dialog and sets up close behavior.
 *
 * When options.trigger is a selector string, uses event delegation so buttons added later
 * (e.g. cloned into the DOM after AJAX) work. Pass options.dialogId when the dialog may not
 * exist at init (e.g. onboarding step loaded via AJAX) so close/getDialog and delegation still work.
 *
 * @param {Object} options
 * @param {HTMLElement|string} [options.trigger] - Button or link that opens the modal (element or selector). Omit when dialog is opened programmatically (e.g. Cart Recovery).
 * @param {HTMLDialogElement} [options.dialog] - Dialog element. Use when opening programmatically so only close/backdrop are set up.
 * @param {string} [options.dialogId] - Dialog element id. If omitted, read from trigger's data-dialog-id. Required for delegation when dialog is not in DOM at init.
 * @param {Function} [options.onOpen] - Called when dialog is opened (e.g. focus search, reset state). Receives (dialog).
 * @return {{ open: function, close: function, getDialog: function }|null} Modal API or null if invalid (and not in delegation-only mode).
 */
export function setupEddModal( options ) {

	// Dialog-only mode: no trigger, dialog opened programmatically (e.g. Cart Recovery).
	if ( options.dialog ) {
		const dialog = options.dialog;
		if ( ! dialog || typeof dialog.showModal !== 'function' ) {
			return null;
		}
		const closeFn = function () {
			if ( dialog && typeof dialog.close === 'function' ) {
				dialog.close();
			}
		};
		ensureCloseListeners( dialog, closeFn );
		return {
			open: function () {
				dialog.showModal();
				if ( typeof options.onOpen === 'function' ) {
					options.onOpen( dialog );
				}
			},
			close: closeFn,
			getDialog: function () {
				return dialog;
			},
		};
	}

	const isSelector = typeof options.trigger === 'string';
	const trigger =
		isSelector ? document.querySelector( options.trigger ) : options.trigger;
	const dialogId =
		options.dialogId ||
		( trigger?.getAttribute( 'data-dialog-id' ) ) ||
		'';
	let dialog = dialogId ? document.getElementById( dialogId ) : null;

	function getDialog() {
		if ( ! dialog && dialogId ) {
			dialog = document.getElementById( dialogId );
		}
		return dialog;
	}

	function open( targetDialog ) {
		const d = targetDialog || getDialog();
		if ( ! d || typeof d.showModal !== 'function' ) {
			return;
		}
		d.showModal();
		if ( typeof options.onOpen === 'function' ) {
			options.onOpen( d );
		}
	}

	function close() {
		const d = getDialog();
		if ( d && typeof d.close === 'function' ) {
			d.close();
		}
	}

	// Delegation: trigger may not exist yet (e.g. onboarding step loaded via AJAX).
	if ( isSelector && options.trigger ) {
		document.body.addEventListener( 'click', function ( e ) {
			const triggerEl = e.target.closest( options.trigger );
			if ( ! triggerEl ) {
				return;
			}
			e.preventDefault();
			const id = triggerEl.getAttribute( 'data-dialog-id' ) || dialogId;
			const targetDialog = id ? document.getElementById( id ) : null;
			if ( targetDialog ) {
				// Bind close to this dialog instance so the close button works when dialog was injected via AJAX.
				ensureCloseListeners( targetDialog, function () {
					if ( targetDialog && typeof targetDialog.close === 'function' ) {
						targetDialog.close();
					}
				} );
				open( targetDialog );
			}
		} );
		return { open, close, getDialog };
	}

	// Single-element mode: dialog must exist at init.
	if ( ! dialog || typeof dialog.showModal !== 'function' ) {
		return null;
	}

	ensureCloseListeners( dialog, close );
	if ( trigger ) {
		trigger.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			open();
		} );
	}
	return { open, close, getDialog };
}

function ensureCloseListeners ( d, closeFn ) {
	if ( !d || d.dataset.eddModalBound ) {
		return;
	}
	d.dataset.eddModalBound = 'true';
	const closeButton = d.querySelector( '.edd-modal__close' );
	if ( closeButton ) {
		closeButton.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			e.stopPropagation();
			closeFn();
		} );
	}
	d.addEventListener( 'click', function ( e ) {
		if ( e.target === d ) {
			closeFn();
		}
	} );
}
