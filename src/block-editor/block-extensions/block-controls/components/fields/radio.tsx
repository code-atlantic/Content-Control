import { RadioControl } from '@wordpress/components';

const RadioField = ( {
	value,
	onChange,
	...fieldProps
}: ControlledInputProps< string > & RadioControl.Props< string > ) => {
	const options = fieldProps.options;

	return (
		<RadioControl
			selected={ value }
			options={ options }
			onChange={ onChange }
			__nextHasNoMarginBottom={ true }
			{ ...fieldProps }
		/>
	);
};

export default RadioField;
