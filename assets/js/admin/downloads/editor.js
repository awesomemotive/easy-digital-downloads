/**
 * Prevents Downloads post type protected meta from being saved.
 *
 * @url https://github.com/awesomemotive/easy-digital-downloads-pro/issues/725
 * @since 3.2.3
 */
const preventDownloadsProtectedMetaSave = () => {

	// Add a middleware to WP API Fetch. So it will handle before any api request.
	wp.apiFetch.use(
		( options, next ) => {

			// retrieve post type config to get api base.
			const downloadConfig = wp.data.select( 'core' ).getEntityConfig( 'postType', 'download' );
			// current request path.
			const path = options?.path;

			// if current request path and download api base path is same process the request and remove protected meta.
			if ( downloadConfig && path && path.indexOf( downloadConfig.baseURL ) === 0 ) {

				const metas = options?.data?.meta || {};

				if ( Object.keys( metas ).length ) {
					const newMetas = {};
					for ( const [ key, value ] of Object.entries( metas ) ) {
						// remove any meta which starts with _edd.
						if ( key.indexOf( '_edd' ) !== 0 ) {
							newMetas[ key ] = value;
						}
					}
					options.data.meta = newMetas;
				}
			}

			const result = next( options );
			return result;
		}
	)

}

wp.domReady( preventDownloadsProtectedMetaSave );
