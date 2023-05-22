import { __ } from '@wordpress/i18n';
import { applyFilters, addFilter } from '@wordpress/hooks';
import { licenseKey } from '@content-control/icons';
import { useLicense } from '@content-control/core-data';

import Section from '../../section';

import LicenseSection from './license';

import type { IconProps } from '@wordpress/icons/build-types/icon';
import type { TabComponent } from '../../../types';

// const { pluginUrl } = contentControlSettingsPage;

addFilter(
	'contentControl.settingsTabSections.upgrade',
	'content-control/general-settings/license-options',
	( sections: { [ key: string ]: any }[] ) => {
		const { isLicenseActive } = useLicense();

		return [
			...sections,
			{
				name: 'license',
				title: (
					<>
						{ __( 'Pro Licensing', 'content-control' ) }
						{ isLicenseActive && (
							<span className="license-status-bubble">
								{ __( 'Activated', 'content-control' ) }
							</span>
						) }
					</>
				),
				icon: licenseKey,
				comp: LicenseSection,
			},
		];
	},
	10
);

const UpgradeView = () => {
	// Filtered & mappable list of TabComponent definitions.
	type SectionList = ( TabComponent & { icon: IconProps[ 'icon' ] } )[];
	const sections: SectionList = applyFilters(
		'contentControl.settingsTabSections.upgrade',
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

export default UpgradeView;