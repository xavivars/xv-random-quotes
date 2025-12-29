/**
 * List Quotes Block Editor
 */
import { registerBlockType } from '@wordpress/blocks';
import { TextControl, SelectControl, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { BlockEditor } from '../components/BlockEditor';
import { CategoryControl } from '../components/CategoryControl';
import { DisableStylingControl } from '../components/DisableStylingControl';

interface ListQuotesAttributes {
	categories: string;
	rows: number;
	orderby: string;
	order: string;
	pagination: boolean;
	disableaspect: boolean;
}

interface ListQuotesProps {
	attributes: ListQuotesAttributes;
	setAttributes: (attributes: Partial<ListQuotesAttributes>) => void;
}

registerBlockType('xv-random-quotes/list-quotes', {
	edit: (props: ListQuotesProps) => {
		const { attributes, setAttributes } = props;
		
		return (
			<BlockEditor
				blockName="xv-random-quotes/list-quotes"
				attributes={attributes}
			>
				<CategoryControl
					value={attributes.categories}
					onChange={(value) => setAttributes({ categories: value })}
				/>
				<TextControl
					label={__('Quotes per page', 'stray-quotes')}
					value={attributes.rows}
					onChange={(value) => setAttributes({ rows: parseInt(value) || 5 })}
					type="number"
					min={1}
					help={__('Number of quotes to display per page', 'stray-quotes')}
				/>
				<SelectControl
					label={__('Order by', 'stray-quotes')}
					value={attributes.orderby}
					onChange={(value) => setAttributes({ orderby: value })}
					options={[
						{ label: __('Date', 'stray-quotes'), value: 'date' },
						{ label: __('Title', 'stray-quotes'), value: 'title' },
						{ label: __('Random', 'stray-quotes'), value: 'rand' },
					]}
				/>
				<SelectControl
					label={__('Order', 'stray-quotes')}
					value={attributes.order}
					onChange={(value) => setAttributes({ order: value })}
					options={[
						{ label: __('Ascending', 'stray-quotes'), value: 'ASC' },
						{ label: __('Descending', 'stray-quotes'), value: 'DESC' },
					]}
				/>
				<ToggleControl
					label={__('Show pagination', 'stray-quotes')}
					checked={attributes.pagination}
					onChange={(value) => setAttributes({ pagination: value })}
				/>
				<DisableStylingControl
					checked={attributes.disableaspect}
					onChange={(value) => setAttributes({ disableaspect: value })}
				/>
			</BlockEditor>
		);
	},
	save: () => null, // Server-side rendered
});
