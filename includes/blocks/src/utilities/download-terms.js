import { useSelect } from '@wordpress/data';

export const DownloadCategoryTerms = ( term, all ) => {
	const options = [ {
		'value': '',
		'label': all
	} ];
	// Request data
	const data = useSelect( ( select ) => {
		return select( 'core' ).getEntityRecords( 'taxonomy', term, { per_page: -1 } );
	} );

	// Has the request resolved?
	const isLoading = useSelect( ( select ) => {
		return select( 'core/data' ).isResolving( 'core', 'getEntityRecords', [
			'taxonomy', term
		] );
	} );

	// Show the loading state if we're still waiting.
	if ( isLoading ) {
		return options;
	}

	data && data.map( ( { id, name } ) => {
		options.push( {
			'value': id,
			'label': name
		} );
	} );

	return options;
};
