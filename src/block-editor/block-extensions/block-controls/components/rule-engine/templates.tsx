/** External Imports */
import { customAlphabet } from 'nanoid';

export const newUUID = customAlphabet(
	'abcdefghijklmnopqrstuvwxyz0123456789',
	8
);

export const newRule = ( name: string = '' ): RuleItem => ( {
	id: newUUID(),
	type: 'rule',
	name,
	options: {},
	notOperand: false,
} );

export const newGroup = ( ruleName: string = '' ): GroupItem => ( {
	id: newUUID(),
	type: 'group',
	label: '',
	query: {
		logicalOperator: 'and',
		items: [ { ...newRule( ruleName ) } ],
	},
} );

export const newSet = ( ruleName: string = '' ): QuerySet => ( {
	id: newUUID(),
	label: '',
	query: {
		logicalOperator: 'and',
		items: [ newGroup( ruleName ) ],
	},
} );