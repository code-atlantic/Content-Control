/** WordPress Imports */
import {
	Button,
	Flex,
	FlexItem,
	Icon,
	Modal,
	Notice,
	SelectControl,
	TextControl,
	__experimentalConfirmDialog as ConfirmDialog,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { _x, __ } from '@wordpress/i18n';
import { blockMeta, trash } from '@wordpress/icons';

/** Internal Imports */
import { newSet } from '../../rule-engine/templates';
import RuleEngine from '../../rule-engine';

const { userRoles, registeredRules } = window.contentControlBlockEditorVars;

const verbs = {
	are: __( 'Are', 'content-control' ),
	arenot: __( 'Are Not', 'content-control' ),
	is: __( 'Is', 'content-control' ),
	isnot: __( 'Is Not', 'content-control' ),
	has: __( 'Has', 'content-control' ),
	hasnot: __( 'Has Not', 'content-control' ),
	doesnothave: __( 'Does Not Have', 'content-control' ),
	does: __( 'Does', 'content-control' ),
	doesnot: __( 'Does Not', 'content-control' ),
	was: [ __( 'Was', 'content-control' ), __( 'Was Not', 'content-control' ) ],
	were: [
		__( 'Were', 'content-control' ),
		__( 'Were Not', 'content-control' ),
	],
};

const builderRules = [
	...Object.values( registeredRules ),
	{
		name: 'user_is_logged_in',
		label: __( 'Logged In', 'content-control' ),
		category: __( 'User', 'content-control' ),
		format: '{category} {verb} {label}',
		verbs: [ verbs.is, verbs.isnot ],
	},
	{
		name: 'user_has_role',
		label: __( 'Role(s)', 'content-control' ),
		category: __( 'User', 'content-control' ),
		format: '{category} {verb} {label}',
		verbs: [ verbs.has, verbs.doesnothave ],
		fields: [
			{
				type: 'multicheck',
				id: 'roles',
				label: __( 'Role(s)', 'content-control' ),
				default: [ 'administrator' ],
				multiple: true,
				options: userRoles,
			},
		],
	},
	{
		name: 'user_has_commented',
		label: __( 'Commented', 'content-control' ),
		category: __( 'User', 'content-control' ),
		format: '{category} {verb} {label}',
		verbs: [ verbs.has, verbs.hasnot ],
		fields: [
			{
				id: 'comparison',
				type: 'select',
				options: [ '>=', '<=', '>', '<', '=' ],
				label: __( 'Comparison', 'content-control' ),
				default: '>=',
			},
			{
				type: 'number',
				id: 'number',
				label: __( 'More than', 'content-control' ),
			},
		],
	},
];

const anyAllOptions = [
	{
		value: 'all',
		label: __( 'All condition are met', 'content-control' ),
	},
	{
		value: 'any',
		label: __( 'Any conditions are met', 'content-control' ),
	},
	{
		value: 'none',
		label: __( 'No conditions are met', 'content-control' ),
	},
];

type ConditionalGroupRules = {
	anyAll: 'all' | 'any' | 'none';
	conditionSets: QuerySet[];
};

type ConditionalRulesProps = {
	groupRules: ConditionalGroupRules;
	setGroupRules: ( groupRules: ConditionalGroupRules ) => void;
};

const ConditionalRules = ( props: ConditionalRulesProps ) => {
	const { groupRules, setGroupRules } = props;
	const { anyAll = 'all', conditionSets = [] } = groupRules;

	const [ currentSet, updateCurrentSet ] = useState< QuerySet | null >(
		null
	);

	const [ setToDelete, confirmDeleteSet ] = useState< QuerySet | null >(
		null
	);

	/** Add new set. */
	const addSet = () => {
		updateCurrentSet( newSet() );
	};

	/**
	 * Update set.
	 *
	 * @param {QuerySet} updatedSet
	 */
	const updateSet = ( updatedSet: QuerySet ) => {
		let updated = false;
		const newSets = conditionSets.map( ( set ) => {
			if ( set.id === updatedSet.id ) {
				updated = true;
				return updatedSet;
			}

			return set;
		} );

		if ( ! updated ) {
			newSets.push( updatedSet );
		}

		setGroupRules( {
			...groupRules,
			conditionSets: newSets,
		} );
	};

	/**
	 * Remove set.
	 *
	 * @param {string} id
	 */
	const removeSet = ( id: string ) =>
		setGroupRules( {
			...groupRules,
			conditionSets: conditionSets.filter( ( set ) => set.id !== id ),
		} );

	/** Confirmation dialogue component. */
	const confirmAndDelete = setToDelete && (
		<ConfirmDialog
			onCancel={ () => confirmDeleteSet( null ) }
			onConfirm={ () => {
				removeSet( setToDelete.id );
				confirmDeleteSet( null );
			} }
		>
			<p>
				{ __(
					'Are you sure you want to delete this set?',
					'content-control'
				) }
			</p>
			<p>{ setToDelete.label }</p>
		</ConfirmDialog>
	);

	const isSetValid = () => {
		return (
			currentSet &&
			[ currentSet.label.length > 0 ].indexOf( false ) === -1
		);
	};

	return (
		<>
			{ confirmAndDelete }

			<SelectControl
				label={ __(
					'Conditionally render this block if…',
					'content-control'
				) }
				options={ anyAllOptions }
				value={ anyAll }
				onChange={ ( value ) => {
					setGroupRules( {
						...groupRules,
						anyAll: value,
					} );
				} }
				help={
					<Button
						variant="link"
						text={ __( 'Add conditions', 'content-control' ) }
						onClick={ addSet }
					/>
				}
			/>

			{ conditionSets.map( ( set ) => (
				<Flex key={ set.id }>
					<FlexItem>
						<Button
							variant="link"
							onClick={ () => updateCurrentSet( set ) }
							text={ set.label || 'Unlabeled Set' }
						/>
					</FlexItem>
					<FlexItem>
						<Button
							isSmall={ true }
							isDestructive={ true }
							variant="tertiary"
							onClick={ () => confirmDeleteSet( set ) }
							icon={ trash }
						/>
					</FlexItem>
				</Flex>
			) ) }

			{ currentSet && (
				<Modal
					title={ __(
						'Content Control -- Conditional Logic',
						'content-control'
					) }
					onRequestClose={ () => updateCurrentSet( null ) }
					shouldCloseOnClickOutside={ false }
					style={ { width: '760px' } }
				>
					<Flex
						style={ {
							marginBottom: 20,
						} }
					>
						<FlexItem
							style={ {
								flexGrow: 1,
								maxWidth: 60,
							} }
						>
							<div
								style={ {
									backgroundColor: '#e6f2f9',
									borderRadius: 100,
									width: 50,
									height: 50,
									padding: 10,
									paddingLeft: 7,
									paddingTop: 11,
									verticalAlign: 'middle',
									textAlign: 'center',
								} }
							>
								<Icon icon={ blockMeta } size={ 30 } />
							</div>
						</FlexItem>

						<FlexItem
							style={ {
								flexBasis: 'auto',
								flexGrow: 3,
							} }
						>
							<h3
								style={ {
									margin: 0,
									marginBottom: 5,
								} }
							>
								{ __(
									'Conditional Logic',
									'content-control '
								) }
							</h3>
							<p
								style={ {
									margin: 0,
								} }
							>
								{ __(
									'Use the power of conditional logic to control when a block is visible.',
									'content-control'
								) }
							</p>
						</FlexItem>
					</Flex>

					<TextControl
						label={ __( 'Condition set label', 'content-control' ) }
						hideLabelFromVision={ true }
						placeholder={ __(
							'Condition set label',
							'content-control'
						) }
						value={ currentSet.label }
						onChange={ ( label ) =>
							updateCurrentSet( {
								...currentSet,
								label,
							} )
						}
					/>

					{ currentSet.label.length <= 0 && (
						<Notice status="warning" isDismissible={ false }>
							{ __(
								'Enter a label for this set.',
								'content-control'
							) }
						</Notice>
					) }

					<RuleEngine
						value={ currentSet.query }
						onChange={ ( query ) => {
							updateCurrentSet( {
								...currentSet,
								query,
							} );
						} }
						options={ {
							features: {
								notOperand: true,
								groups: true,
								nesting: false,
							},
							rules: builderRules,
						} }
					/>

					<Flex justify="right">
						<FlexItem>
							<Button onClick={ () => updateCurrentSet( null ) }>
								{ __( 'Cancel', 'content-control' ) }
							</Button>
						</FlexItem>
						<FlexItem>
							<Button
								disabled={ ! isSetValid() }
								variant="primary"
								onClick={ () => {
									if ( ! isSetValid() ) {
										return;
									}
									updateSet( currentSet );
									updateCurrentSet( null );
								} }
							>
								{ __( 'Save', 'content-control' ) }
							</Button>
						</FlexItem>
					</Flex>
				</Modal>
			) }
		</>
	);
};

export default ConditionalRules;
