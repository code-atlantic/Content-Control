import { permissions as permissionsIcon } from '@content-control/icons';
import { applyFilters, addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

import Section from '../../section';
import PermissionsSection from './permissions';
import LogViewer from './log-viewer';

import type { IconProps } from '@wordpress/icons/build-types/icon';
import type { TabComponent } from '../../../types';

const { logUrl = false } = contentControlSettingsPage;

addFilter(
	'contentControl.settingsTabSections.general',
	'content-control/general-settings/permissions-options',
	( sections: { [ key: string ]: any }[] ) => {
		return [
			...sections,
			{
				name: 'permissions',
				title: __( 'Plugin Permissions', 'content-control' ),
				icon: permissionsIcon,
				comp: PermissionsSection,
			},
		];
	},
	10
);

addFilter(
	'contentControl.settingsTabSections.general',
	'content-control/general-settings/log-viewer',
	( sections: { [ key: string ]: any }[] ) => {
		return [
			...sections,
			logUrl !== false && {
				name: 'log',
				title: __( 'Log Viewer', 'content-control' ),
				icon: 'editor-code',
				comp: LogViewer,
			},
		];
	},
	50
);

const GeneralTab = () => {
	// Filtered & mappable list of TabComponent definitions.
	type SectionList = ( TabComponent & { icon: IconProps[ 'icon' ] } )[];

	/**
	 * Filter the list of sections on the "General" tab.
	 *
	 * @param {SectionList} sections List of sections.
	 *
	 * @return {SectionList} Filtered list of sections.
	 */
	const sections: SectionList = applyFilters(
		'contentControl.settingsTabSections.general',
		[]
	) as SectionList;

	return (
		<>
			{ sections.map( ( { name, title, icon, comp: Component } ) => (
				<Section key={ name } title={ title } icon={ icon }>
					{ Component ? <Component /> : title }
				</Section>
			) ) }
		</>
	);
};

export default GeneralTab;
