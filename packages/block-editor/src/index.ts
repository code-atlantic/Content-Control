import './block-extensions';

/* Global Var Declarations */
declare global {
	const contentControlBlockEditor: {
		adminUrl: string;
		pluginUrl: string;
		advancedMode: boolean;
		allowedBlocks: string[];
		excludedBlocks: string[];
	};
}