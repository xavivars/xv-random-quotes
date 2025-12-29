/**
 * Category Control Component
 * Shared TextControl for categories attribute
 */
import { TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

interface CategoryControlProps {
	value: string;
	onChange: (value: string) => void;
}

export const CategoryControl = ({ value, onChange }: CategoryControlProps) => (
	<TextControl
		label={__('Categories (comma-separated slugs)', 'stray-quotes')}
		value={value}
		onChange={onChange}
		help={__('Leave empty for all categories', 'stray-quotes')}
	/>
);
