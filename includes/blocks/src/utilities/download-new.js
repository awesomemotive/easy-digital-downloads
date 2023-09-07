import { sprintf, __ } from '@wordpress/i18n';
import { Placeholder } from '@wordpress/components';
import { useBlockProps } from '@wordpress/block-editor';

export const newDownload = () => (
	<div className="edd-downloads--wrap" {...useBlockProps()}>
		<Placeholder
			icon="download"
			className="edd-downloads--new-placeholder"
			label={EDDBlocks.has_draft_downloads && (
				/* translators: %s: Download label plural */
				sprintf( __( 'No Published %s Found', 'easy-digital-downloads' ), EDDBlocks.download_label_plural )
				/* translators: %s: Download label plural */
			) || ( sprintf( __( 'No %s Found', 'easy-digital-downloads' ), EDDBlocks.download_label_plural ) ) }
		>
			<div className="edd-downloads--actions">
				<a
					href={EDDBlocks.new_download_link}
					className="components-button edd-downloads--primary"
					target="_blank"
				>	{EDDBlocks.has_draft_downloads && (
						/* translators: %s: Download label singular */
						sprintf( __( 'Create a New %s', 'easy-digital-downloads' ), EDDBlocks.download_label_singular )
					) || (
							/* translators: %s: Download label singular */
							sprintf( __( 'Create Your First %s', 'easy-digital-downloads' ), EDDBlocks.download_label_singular )
						)}
				</a>
				{EDDBlocks.has_draft_downloads && (
					<a
						href={EDDBlocks.view_downloads_link}
						className="components-button edd-downloads--secondary"
						target="_blank"
					>
						{
							/* translators: %s: Download label singular */
							sprintf( __( 'View All %s', 'easy-digital-downloads' ), EDDBlocks.download_label_plural )
						}
					</a>
				)}
			</div>
		</Placeholder>
	</div>
);
