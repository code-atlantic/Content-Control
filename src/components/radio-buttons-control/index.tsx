import classnames, { Argument as classNamesArg } from 'classnames';

import { useInstanceId } from '@wordpress/compose';
import { BaseControl, Button } from '@wordpress/components';

import './editor.scss';

type Props< T extends string | number = string | number > = {
	value: T;
	onChange: ( value: T ) => void;
	label?: string | JSX.Element;
	className?: classNamesArg;
	options: ( Partial< Button.Props > & {
		value: T;
		label: string | JSX.Element;
	} )[];
	orientation?: 'horizontal' | 'vertical';
	spacing?: string | number;
};

const RadioButtonControl = < T extends string | number = string | number >( {
	label,
	value,
	onChange,
	className,
	options = [],
	orientation = 'horizontal',
	spacing = 10,
}: Props< T > ) => {
	const instanceId = useInstanceId( RadioButtonControl );

	return (
		<BaseControl
			id={ `radio-button-control-${ instanceId }` }
			label={ label }
			className={ classnames(
				'components-radio-button-control',
				orientation,
				className
			) }
		>
			<div
				className="options"
				style={ spacing ? { gap: `${ spacing }px` } : undefined }
			>
				{ options.map(
					( {
						label: optLabel,
						value: optValue,
						...buttonProps
					} ) => (
						<Button
							key={ optValue }
							variant={
								optValue === value ? 'primary' : 'secondary'
							}
							onClick={ () => onChange( optValue ) }
							{ ...buttonProps }
						>
							{ optLabel }
						</Button>
					)
				) }
			</div>
		</BaseControl>
	);
};

export default RadioButtonControl;