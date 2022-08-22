import { __ } from '@wordpress/i18n';
import {
	TextControl,
	Notice,
	BaseControl,
	Button,
	CheckboxControl,
} from '@wordpress/components';
import { whoOptions } from '../options';

type Props = ContentControl.Settings.Restrictions.EditTabProps;

const { userRoles } = contentControlSettingsPageVars;

const GeneralTab = ( { values, onChange, updateValue }: Props ) => {

	console.log( values );
	const { title = '', who = 'logged_in', roles = [] } = values;

	return (
		<>
			<TextControl
				label={ __( 'Restriction label', 'content-control' ) }
				hideLabelFromVision={ true }
				placeholder={ __( 'Condition set label', 'content-control' ) }
				value={ title }
				onChange={ ( newTitle ) => updateValue( 'title', newTitle ) }
			/>

			{ title.length <= 0 && (
				<Notice status="warning" isDismissible={ false }>
					{ __( 'Enter a label for this set.', 'content-control' ) }
				</Notice>
			) }

			<h4>{ __( 'Who can see this content?', 'content-control' ) }</h4>
			<div className="who-options">
				{ Object.entries( whoOptions ).map( ( [ value, label ] ) => (
					<Button
						key={ value }
						variant={ value === who ? 'primary' : 'secondary' }
						text={ label }
						onClick={ () =>
							updateValue(
								'who',
								value as keyof typeof whoOptions
							)
						}
					/>
				) ) }
			</div>

			{ 'logged_in' === who && (
				<>
					{ Object.entries( userRoles ).map( ( [ value, label ] ) => {
						const checked = roles.indexOf( value ) !== -1;

						return (
							<CheckboxControl
								label={ label }
								checked={ checked }
								onChange={ () => {
									if ( checked ) {
										updateValue( 'roles', [
											...roles.filter(
												( role ) => role !== value
											),
										] );
									} else {
										updateValue( 'roles', [
											...roles,
											value,
										] );
									}
								} }
							/>
						);
					} ) }
				</>
			) }
		</>
	);
};

export default GeneralTab;