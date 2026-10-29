/* global ajaxurl eddAdminOrderOverview */

/**
 * Dialog for copying an order item's file download link.
 *
 * @package     EDD\Admin\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useState, useEffect, useRef } from '@wordpress/element';
import Dialog from '../../../components/dialog/Dialog.js';

const TITLE_ID = 'edd-copy-download-link-title';

const CopyDownloadLinkDialog = ( { orderId, productId, priceId, onClose } ) => {
	const { i18n, nonces } = eddAdminOrderOverview;
	const [ link, setLink ] = useState( null ); // null = loading
	const textareaRef = useRef( null );

	useEffect( () => {
		const body = new FormData();
		body.append( 'action', 'edd_get_file_download_link' );
		body.append( 'nonce', nonces.edd_get_file_download_link );
		body.append( 'payment_id', orderId );
		body.append( 'download_id', productId );
		body.append( 'price_id', priceId );

		const fetchLink = async () => {
			try {
				const response = await fetch( ajaxurl, { method: 'POST', body } );
				const trimmed  = ( await response.text() ).trim();
				setLink( [ '-1', '-2', '-3', '-4', '' ].includes( trimmed ) ? '' : trimmed );
			} catch {
				setLink( '' );
			}
		};

		void fetchLink();
	}, [] ); // eslint-disable-line react-hooks/exhaustive-deps

	// Select text once the link loads.
	useEffect( () => {
		if ( link && textareaRef.current ) {
			textareaRef.current.select();
		}
	}, [ link ] );

	const title = i18n.copyDownloadLinks;

	return (
		<Dialog
			open={ true }
			onClose={ onClose }
			title={ title }
			titleId={ TITLE_ID }
		>
			<div className="edd-order-overview-modal">
				<form className="edd-order-copy-download-link">
					<div className="edd-form-group">
						<label htmlFor="edd-copy-link" className="screen-reader-text">
							{ title }
						</label>
						<div className="edd-form-group__control">
							{ '' === link && (
								<>{ i18n.noFileLinks }</>
							) }
							{ link && (
								<textarea rows={ 6 } id="edd-copy-link" ref={ textareaRef } readOnly value={ link } onChange={ () => {} } />
							) }
						</div>
					</div>
				</form>
			</div>
		</Dialog>
	);
};

export default CopyDownloadLinkDialog;
