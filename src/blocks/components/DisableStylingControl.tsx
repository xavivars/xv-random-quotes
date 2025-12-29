/**
 * Disable Styling Control Component
 * Shared ToggleControl for disableaspect attribute
 */
import { ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

interface DisableStylingControlProps {
	checked: boolean;
	onChange: (value: boolean) => void;
}

export const DisableStylingControl = ({ checked, onChange }: DisableStylingControlProps) => (
	<ToggleControl
		label={__('Disable styling', 'stray-quotes')}
		checked={checked}
		onChange={onChange}
		help={__('Remove default HTML wrappers', 'stray-quotes')}
	/>
);
