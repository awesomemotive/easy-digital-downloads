import { sprintf, __ } from '@wordpress/i18n';

export const DownloadTaxonomies = [
	{
		'value': 'download_category',
		/* translators: %s: Download label singular */
		'label': sprintf( __( '%s Categories', 'easy-digital-downloads' ), EDDBlocks.download_label_singular )
	},
	{
		'value': 'download_tag',
		/* translators: %s: Download label singular */
		'label': sprintf( __( '%s Tags', 'easy-digital-downloads' ), EDDBlocks.download_label_singular )
	}
];
