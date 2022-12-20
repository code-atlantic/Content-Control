import './index.scss';

import classNames from 'classnames';

import { Children, cloneElement, isValidElement } from '@wordpress/element';

import RuleGroupHeader from './header';

import type { Icon } from '@wordpress/components';
import type { BlockControlsGroup, BlockControlsGroupProps } from '../../../../types';

type Props = React.PropsWithChildren< {
	label: string;
	groupId: string;
	icon: Icon.IconType< any >;
	rules: any;
	setRules: ( rules: any ) => void;
	defaults: {
		[ key: string ]: any;
	};
} >;

type OtherProps = BlockControlsGroupProps< BlockControlsGroup > &
	Props & {
		setGroupRules: ( rules?: BlockControlsGroup | null ) => void;
		updateGroupRules: ( rules?: BlockControlsGroup | null ) => void;
	};

const RuleGroupComponent = ( {
	label,
	groupId,
	icon,
	rules,
	setRules,
	defaults,
	children,
	// isOpened = false,
	...extraChildProps
}: Props ) => {
	const { [ groupId ]: groupRules = null } = rules;

	const isOpened = null !== groupRules;

	/**
	 * Set single rule group's settings by ID.
	 *
	 * This will replace the entire group object with the newRules.
	 *
	 * @param {BlockControlsGroup} newRules New rules to save for group.
	 */
	const setGroupRules = ( newRules?: BlockControlsGroup | null ) =>
		setRules( {
			...rules,
			[ groupId ]: newRules,
		} );

	/**
	 * Append/update rules for the group.
	 *
	 * @param {BlockControlsGroup} newRules Rules to append to the group settings.
	 */
	const updateGroupRules = ( newRules?: BlockControlsGroup | null ) =>
		setGroupRules( {
			...groupRules,
			...newRules,
		} );

	/**
	 * Render children with additional props.
	 */
	const ChildrenWithProps = () => (
		<>
			{ Children.map( children, ( child ) => {
				const item = child as React.ReactElement<
					React.PropsWithChildren< Props >
				>;

				// Checking isValidElement is the safe way and avoids a typescript
				// error too.
				if ( isValidElement< OtherProps >( item ) ) {
					// TODO LEFT OFF HERE.
					return cloneElement( item, {
						...extraChildProps,
						groupRules,
						setGroupRules,
						updateGroupRules,
					} );
				}

				return item;
			} ) }
		</>
	);

	return (
		<div
			className={ classNames( [
				'cc__rules-group',
				`cc__rules-group--type-${ groupId }`,
				isOpened ? 'is-opened' : null,
			] ) }
		>
			<RuleGroupHeader
				label={ label }
				isOpened={ isOpened }
				icon={ icon }
				setGroupRules={ setGroupRules }
				groupRules={ groupRules }
				groupDefaults={ defaults[ groupId ] }
			/>
			{ isOpened && (
				<div className="cc__rules-group__body">
					<ChildrenWithProps />
				</div>
			) }
		</div>
	);
};

export default RuleGroupComponent;
