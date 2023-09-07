import { sprintf, __ } from "@wordpress/i18n";
import { useSelect } from '@wordpress/data';

export const Users = () => {
	const options = [ {
		'value': '',
		'label': __( 'All authors', 'easy-digital-downloads' ),
	} ];

	const query = {
		per_page: -1,
		has_published_posts: [ 'download' ]
	};

	// Request data
	const data = useSelect( ( select ) => {
		return select( 'core' ).getUsers( query );
	} );

	// Has the request resolved?
	const isLoading = useSelect( ( select ) => {
		return select( 'core/data' ).isResolving( 'core', 'getUsers', query );
	} );

	// Show the loading state if we're still waiting.
	if ( isLoading ) {
		return options;
	}

	data && data.map( ( { id, nickname } ) => {
		options.push( {
			'value': id,
			'label': nickname
		} );
	} );

	return options;
};
