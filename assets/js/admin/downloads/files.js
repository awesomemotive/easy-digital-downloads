document.addEventListener( 'DOMContentLoaded', function () {
	const productTypeSelect = document.getElementById( '_edd_product_type' );
	if ( ! productTypeSelect ) {
		return;
	}

	// Listen for the 'change' event
	productTypeSelect.addEventListener( 'change', function ( e ) {
		const productType = e.target.value;
		const productFiles = document.getElementById( 'edd_product_files' );
		const navItem = document.getElementById( 'edd_download_editor__files-nav-item' );
		const variablePricing = document.getElementById( 'edd_variable_pricing' );
		// Add the loading class
		navItem.classList.add( 'ajax--loading' );

		// Prepare the AJAX data
		const data = new FormData();
		data.append( 'action', 'edd_swap_download_type' );
		data.append( 'product_type', productType );
		data.append( 'post_id', edd_vars.post_id );
		data.append( 'has_variable_pricing', variablePricing && variablePricing.checked );

		// Perform the AJAX POST request
		fetch( ajaxurl, {
			method: 'POST',
			body: data,
		} )
			.then( response => response.json() )
			.then( response => {
				if ( response.success ) {
					productFiles.innerHTML = response.data.html;

					const label = navItem.querySelector( '.label' );
					if ( label ) {
						label.textContent = response.data.label;
					}

					document.dispatchEvent( new CustomEvent( 'edd_download_type_changed', {
						detail: {
							productType: productType,
						},
					} ) );
				}
			} )
			.catch( error => console.error( 'Error:', error ) )
			.finally( () => {
				navItem.classList.remove( 'ajax--loading' );
			} );
	} );
} );
