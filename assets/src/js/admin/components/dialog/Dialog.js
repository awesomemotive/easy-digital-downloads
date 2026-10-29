/**
 * Native dialog modal component.
 *
 * @package     EDD\Admin\Components
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useRef, useEffect, createPortal } from '@wordpress/element';

/**
 * Reusable native <dialog> modal component.
 *
 * Wraps the native <dialog> element with showModal/close lifecycle, Esc handling
 * via the cancel event, backdrop-click-to-close, and focus return on close.
 *
 * @param {Object}   props
 * @param {boolean}  props.open          Whether the dialog is open.
 * @param {Function} props.onClose       Called when the dialog should close.
 * @param {string}   [props.title]       Heading text rendered inside the dialog header.
 * @param {string}   [props.titleId]     ID applied to the title element for aria-labelledby.
 * @param {string}   [props.width]       CSS width (default: '350px').
 * @param {string}   [props.className]   Additional CSS class(es) for the dialog element.
 * @param {*}        props.children      Dialog body content.
 * @return {Element} The Dialog component.
 */
const Dialog = ( { open, onClose, title, titleId, width = '350px', className, children } ) => {
	const dialogRef = useRef( null );
	const openRef   = useRef( open );

	openRef.current = open;

	useEffect( () => {
		const dialog = dialogRef.current;
		if ( ! dialog || ! open ) {
			return;
		}

		// Capture the element that opened the dialog so focus can return to it.
		const trigger = document.activeElement;
		if ( ! dialog.open ) {
			dialog.showModal();
		}

		// Cleanup runs on both an in-place open→false change and on unmount,
		// so closing the modal (which unmounts it) restores focus to the trigger.
		return () => {
			if ( dialog.open ) {
				dialog.close();
			}
			if ( trigger instanceof HTMLElement ) {
				trigger.focus();
			}
		};
	}, [ open ] );

	useEffect( () => {
		const dialog = dialogRef.current;
		if ( ! dialog ) {
			return;
		}

		const handleCancel = ( e ) => {
			e.preventDefault();
			onClose();
		};

		// A drag that starts inside the dialog and ends on the backdrop still fires click on the dialog.
		let isPressOnBackdrop = false;

		const handleMouseDown = ( e ) => {
			isPressOnBackdrop = e.target === dialog;
		};

		const handleClick = ( e ) => {
			if ( isPressOnBackdrop && e.target === dialog ) {
				onClose();
			}
			isPressOnBackdrop = false;
		};

		// Browsers can close the dialog despite a prevented cancel (Chrome does on a repeated Esc), so reopen it and let onClose decide.
		const handleNativeClose = () => {
			if ( openRef.current && ! dialog.open ) {
				dialog.showModal();
				onClose();
			}
		};

		dialog.addEventListener( 'cancel', handleCancel );
		dialog.addEventListener( 'mousedown', handleMouseDown );
		dialog.addEventListener( 'click', handleClick );
		dialog.addEventListener( 'close', handleNativeClose );

		return () => {
			dialog.removeEventListener( 'cancel', handleCancel );
			dialog.removeEventListener( 'mousedown', handleMouseDown );
			dialog.removeEventListener( 'click', handleClick );
			dialog.removeEventListener( 'close', handleNativeClose );
		};
	}, [ onClose ] );

	const closeLabel = globalThis.edd_vars?.closeText ?? 'Close';

	return createPortal(
		<dialog
			className={ [ 'edd-modal', className ].filter( Boolean ).join( ' ' ) }
			ref={ dialogRef }
			style={ { width } }
			aria-modal="true"
			aria-labelledby={ titleId || undefined }
		>
			<div className="edd-modal__header">
				{ title && (
					<h2
						className="edd-modal__title"
						id={ titleId || undefined }
					>
						{ title }
					</h2>
				) }
				<button
					type="button"
					className="edd-modal__close"
					onClick={ onClose }
					aria-label={ closeLabel }
				>
					<span className="dashicons dashicons-no-alt" aria-hidden="true"></span>
					<span className="screen-reader-text">{ closeLabel }</span>
				</button>
			</div>
			<div className="edd-modal__content">
				{ children }
			</div>
		</dialog>,
		document.body
	);
};

export default Dialog;
