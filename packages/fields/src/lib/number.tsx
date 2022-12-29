import {
	// @ts-ignore
	__experimentalNumberControl as NumberControl,
} from '@wordpress/components';

import type { NumberFieldProps, WithOnChange } from '../types';

const NumberField = ( {
	value,
	onChange,
	...fieldProps
}: WithOnChange< NumberFieldProps > ) => {
	return (
		<NumberControl
			{ ...fieldProps }
			value={ value }
			onChange={ onChange }
			__nextHasNoMarginBottom={ true }
		/>
	);
};

export default NumberField;