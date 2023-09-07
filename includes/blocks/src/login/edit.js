
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
					<p className="description">{__( 'Once logged in, where should the user be directed? You can choose the current page, or a custom URL.', 'easy-digital-downloads' )}</p>
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
			<p className="description">{__( 'This form is a sample view of your login form. Logged in users will not see it.', 'easy-digital-downloads' )}</p>
			<form className="edd-blocks-form edd-blocks-form__login">
				<div className="edd-blocks-form__group edd-blocks-form__group-username">
					<label htmlFor="edd_user_login">{__( 'Username or Email', 'easy-digital-downloads' )}</label>
					<div className="edd-blocks-form__control">
						<input name="edd_user_login" id="edd_user_login" className="edd-required edd-input" type="text" value="user_name" readOnly />
					</div>
				</div>
				<div className="edd-blocks-form__group edd-blocks-form__group-password">
					<label htmlFor="edd_user_pass">{__( 'Password', 'easy-digital-downloads' )}</label>
					<div className="edd-blocks-form__control">
						<input name="edd_user_pass" id="edd_user_pass" className="edd-password edd-required edd-input" type="password" value="234324" readOnly />
					</div>
				</div>
				<div className="edd-blocks-form__group edd-blocks-form__group-remember">
					<div className="edd-blocks-form__control">
						<Disabled>
							<input name="rememberme" type="checkbox" id="rememberme" value="forever" readOnly />
							<label htmlFor="rememberme">{__( 'Remember Me', 'easy-digital-downloads' )}</label>
						</Disabled>
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
				<p className="edd-blocks-form__group edd-blocks-form__group-lost-password">
					<a href="">
						{__( 'Lost Password?', 'easy-digital-downloads' )}
					</a>
				</p>
			</form>
		</div>
	);
}
