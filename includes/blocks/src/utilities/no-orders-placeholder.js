import { __ } from '@wordpress/i18n';
import { Placeholder } from '@wordpress/components';

export const emptyPlaceholder = () => (
	<Placeholder>
		<p>{__( 'Create at least one order to see an example of a receipt.', 'easy-digital-downloads' )}</p>
	</Placeholder>
);
