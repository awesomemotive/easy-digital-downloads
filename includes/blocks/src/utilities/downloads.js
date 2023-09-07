import { sprintf, __ } from "@wordpress/i18n";
import { useSelect } from '@wordpress/data';

export const DownloadOptions = ( showEmpty, downloadID ) => {
	const options = [];

	if ( showEmpty ) {
		options.push( {
			'value': '',
			/* translators: %s: Download label singular */
			'label': sprintf( __( 'Select a %s', 'easy-digital-downloads' ), EDDBlocks.download_label_singular ),
		} );
	}

	if ( downloadID || 'template' === downloadID ) {
		/* translators: %s: Download label singular */
		let label = sprintf( __( 'Current %s', 'easy-digital-downloads' ), EDDBlocks.download_label_singular );
		if ( 'template' !== downloadID ) {
			label = wp.data.select( 'core/editor' ).getCurrentPostAttribute( 'title' );
		}
		options.push( {
			'value': downloadID,
			'label': label,
		} );
	}

	// Request data
	const data = useSelect( ( select ) => {
		let query = { per_page: -1 };
		if ( downloadID ) {
			query.exclude = downloadID;
		}
		return select( 'core' ).getEntityRecords( 'postType', 'download', query );
	} );

	// Has the request resolved?
	const isLoading = useSelect( ( select ) => {
		return select( 'core/data' ).isResolving( 'core', 'getEntityRecords', [
			'postType', 'download'
		] );
	} );

	// Show the loading state if we're still waiting.
	if ( isLoading ) {
		return options;
	}

	data && data.map( ( { id, title } ) => {
		options.push( {
			'value': id,
			'label': title.raw
		} );
	} );

	return options;
};
