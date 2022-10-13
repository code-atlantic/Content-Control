import { __experimentalNumberControl as NumberControl } from '@wordpress/components';

import type { ControlledInputProps } from '../types';

const NumberField = ( {
	value = '',
	onChange,
	...fieldProps
}: ControlledInputProps< string > & NumberControl.Props ) => {
	return (
		<NumberControl
			value={ value }
			onChange={ onChange }
			__nextHasNoMarginBottom={ true }
			{ ...fieldProps }
		/>
	);
};

export default NumberField;