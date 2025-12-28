/**
 * Type declarations for WordPress packages that don't ship with TypeScript definitions
 */

// @wordpress/edit-post doesn't include TypeScript definitions
declare module '@wordpress/edit-post' {
	import { ReactNode } from 'react';

	export interface PluginDocumentSettingPanelProps {
		name: string;
		title: string;
		className?: string;
		children?: ReactNode;
	}

	export const PluginDocumentSettingPanel: React.ComponentType<
		PluginDocumentSettingPanelProps
	>;
}

// Extend @wordpress/data types to allow simpler useSelect usage
declare module '@wordpress/data' {
	export function useSelect< T = unknown >(
		mapSelect: ( select: any ) => T,
		deps?: any[]
	): T;
}
