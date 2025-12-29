/**
 * Random Quote Block Editor
 */
import { registerBlockType } from '@wordpress/blocks';
import { ToggleControl, RangeControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { BlockEditor } from '../components/BlockEditor';
import { CategoryControl } from '../components/CategoryControl';
import { DisableStylingControl } from '../components/DisableStylingControl';
interface RandomQuoteAttributes {
	categories: string;
	disableaspect: boolean;
	enableAjax: boolean;
	timer: number;
}

interface RandomQuoteProps {
	attributes: RandomQuoteAttributes;
	setAttributes: (attributes: Partial<RandomQuoteAttributes>) => void;
}

const CustomIcon = (
    <svg width="20" height="20" viewBox="0 0 20 20">
        <circle cx="10" cy="10" r="8" fill="currentColor" />
    </svg>
);

registerBlockType('xv-random-quotes/random-quote', {
	icon: CustomIcon,
	edit: (props: RandomQuoteProps) => {
		const { attributes, setAttributes } = props;
		
		return (
			<BlockEditor
				blockName="xv-random-quotes/random-quote"
				attributes={attributes}
			>
				<CategoryControl
					value={attributes.categories}
					onChange={(value) => setAttributes({ categories: value })}
				/>
				<DisableStylingControl
					checked={attributes.disableaspect}
					onChange={(value) => setAttributes({ disableaspect: value })}
				/>
				<ToggleControl
					label={__('Enable AJAX refresh', 'xv-random-quotes')}
					checked={attributes.enableAjax}
					onChange={(value) => setAttributes({ enableAjax: value })}
				/>
				{attributes.enableAjax && (
					<RangeControl
						label={__('Auto-refresh timer (seconds)', 'xv-random-quotes')}
						value={attributes.timer}
						onChange={(value) => setAttributes({ timer: value })}
						min={0}
						max={300}
						help={__('0 = no auto-refresh', 'xv-random-quotes')}
					/>
				)}
			</BlockEditor>
		);
	},
	save: () => null, // Server-side rendered
});
