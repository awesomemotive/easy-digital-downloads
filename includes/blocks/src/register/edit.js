import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, Disabled, ToggleControl } from '@wordpress/components';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/developers/block-api/block-edit-save/#edit
 *
 * @return {WPElement} Element to render.
 */
export default function Edit ( { attributes, setAttributes } ) {

	const toggleAttribute = ( attributeName ) => ( newValue ) =>
		setAttributes( { [ attributeName ]: newValue } );

	return (
		<div {...useBlockProps()}>
			<InspectorControls>
				<PanelBody title={__( 'Settings', 'easy-digital-downloads' )}>
					<p className="description">{__( 'Once registered, where should the user be directed? You can choose the current page, or a custom URL.', 'easy-digital-downloads' )}</p>
					<ToggleControl
						label={__( 'Redirect to Current Page', 'easy-digital-downloads' )}
						checked={!!attributes.current}
						onChange={toggleAttribute( 'current' )}
					/>
					{!attributes.current && (
						<TextControl
							label={__( 'Custom Redirect URL', 'easy-digital-downloads' )}
							value={attributes.redirect}
							onChange={toggleAttribute( 'redirect' )}
						/>
					)}
				</PanelBody>
			</InspectorControls>
			<p className="description">{__( 'This form is a sample view of your registration form. Logged in users will not see it.', 'easy-digital-downloads' )}</p>
			<form id="edd-blocks-form__register" className="edd-blocks-form edd-blocks-form__register">
				<div className="edd-blocks-form__group edd-blocks-form__group-username">
					<label htmlFor="edd_user_register">
						{__( 'Username or Email', 'easy-digital-downloads' )}
						<span className="edd-required-indicator">*</span><span className="screen-reader-text">{__( 'Required', 'easy-digital-downloads' )}</span>
					</label>
					<div className="edd-blocks-form__control">
						<input name="edd_user_register" id="edd_user_register" className="edd-required edd-input" type="text" readOnly />
					</div>
				</div>
				<div className="edd-blocks-form__group edd-blocks-form__group-email">
					<label htmlFor="edd-user-email">
						{__( 'Email', 'easy-digital-downloads' )}
						<span className="edd-required-indicator">*</span><span className="screen-reader-text">{__( 'Required', 'easy-digital-downloads' )}</span>
					</label>
					<div className="edd-blocks-form__control">
						<input name="edd-user-email" id="edd_user_login" className="edd-password edd-required edd-input" type="email" readOnly />
					</div>
				</div>
				<div className="edd-blocks-form__group edd-blocks-form__group-password">
					<label htmlFor="edd-user-pass">
						{__( 'Password', 'easy-digital-downloads' )}
						<span className="edd-required-indicator">*</span><span className="screen-reader-text">{__( 'Required', 'easy-digital-downloads' )}</span>
					</label>
					<div className="edd-blocks-form__control">
						<input id="edd-user-pass" className="password required edd-input" type="password" name="edd_user_pass" readOnly />
					</div>
				</div>
				<div className="edd-blocks-form__group edd-blocks-form__group-password-confirm">
					<label htmlFor="edd-user-pass2">
						{__( 'Confirm Password', 'easy-digital-downloads' )}
						<span className="edd-required-indicator">*</span><span className="screen-reader-text">{__( 'Required', 'easy-digital-downloads' )}</span>
					</label>
					<div className="edd-blocks-form__control">
						<input id="edd-user-pass2" className="password required edd-input" type="password" name="edd_user_pass2" readOnly />
					</div>
				</div>
				<div className="edd-blocks-form__group edd-blocks-form__group-submit">
					<Disabled>
						<input
							name="submit"
							type="submit"
							className="edd-submit button"
							label={__( 'Log In', 'easy-digital-downloads' )}
							value={__( 'Log In', 'easy-digital-downloads' )}
						/>
					</Disabled>
				</div>
			</form >
		</div >
	);
}
