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
	sequence: boolean;
	multi: number;
	disableaspect: boolean;
	enableAjax: boolean;
	timer: number;
	cacheBypass: boolean;
}

interface RandomQuoteProps {
	attributes: RandomQuoteAttributes;
	setAttributes: (attributes: Partial<RandomQuoteAttributes>) => void;
}

registerBlockType('xv-random-quotes/random-quote', {
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
				<RangeControl
					label={__('Number of quotes', 'xv-random-quotes')}
					value={attributes.multi}
					onChange={(value) => setAttributes({ multi: value || 1 })}
					min={1}
					max={10}
					help={__('How many quotes to display', 'xv-random-quotes')}
				/>
				<ToggleControl
					label={__('Sequential order', 'xv-random-quotes')}
					checked={attributes.sequence}
					onChange={(value) => setAttributes({ sequence: value })}
					help={__('Show quotes in order instead of random', 'xv-random-quotes')}
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
					<>
					<RangeControl
						label={__('Auto-refresh timer (seconds)', 'xv-random-quotes')}
						value={attributes.timer}
						onChange={(value) => setAttributes({ timer: value })}
						min={0}
						max={300}
						help={__('0 = no auto-refresh', 'xv-random-quotes')}
					/>
					<ToggleControl
						label={__('Bypass page cache', 'xv-random-quotes')}
						checked={attributes.cacheBypass}
						onChange={(value) => setAttributes({ cacheBypass: value })}
						help={__('Fetch a fresh quote on every page load, even when the page is cached', 'xv-random-quotes')}
					/>
					</>
				)}
			</BlockEditor>
		);
	},
	save: () => null, // Server-side rendered
});
