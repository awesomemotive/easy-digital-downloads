<?php

/**
 * Purpose of this file is to include functions that are in trunk unit testing suite, but not yet in all versions of WordPress core.
 *
 * We'll need to periodly make sure these shims are in parity with the core functions.
 */

 /**
 * Function required to test the qTranslate compatibility function.
 *
 * @since 2.3
 */
if ( ! function_exists( 'qtrans_useCurrentLanguageIfNotFoundShowAvailable' ) ) {
	function qtrans_useCurrentLanguageIfNotFoundShowAvailable( $content ) {
		return $content;
	}
}
