/**
 * WordPress dependencies
 */
import { createReduxStore } from '@wordpress/data';
import { controls as wpControls } from '@wordpress/data-controls';

import * as actions from './actions';
import * as selectors from './selectors';
import * as resolvers from './resolvers';
import reducer from './reducer';
import localControls from '../controls';

import { initialState, restrictionDefaults, STORE_NAME } from './constants';

const storeConfig = () => ( {
	initialState,
	selectors,
	actions,
	reducer,
	resolvers,
	controls: { ...wpControls, ...localControls },
} );

const store = createReduxStore( STORE_NAME, storeConfig() );

export { STORE_NAME, store, restrictionDefaults };

declare module '@wordpress/data' {
	function select( key: StoreKey ): Selectors;
	function dispatch( key: StoreKey ): Actions;
	function useDispatch( key: StoreKey ): Actions;
}
