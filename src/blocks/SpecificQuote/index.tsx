/**
 * Specific Quote Block Editor
 */
import { registerBlockType } from '@wordpress/blocks';
import { TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { BlockEditor } from '../components/BlockEditor';
import { DisableStylingControl } from '../components/DisableStylingControl';

interface SpecificQuoteAttributes {
	postId: number;
	legacyId: number;
	disableaspect: boolean;
}

interface SpecificQuoteProps {
	attributes: SpecificQuoteAttributes;
	setAttributes: (attributes: Partial<SpecificQuoteAttributes>) => void;
}

registerBlockType('xv-random-quotes/specific-quote', {
	edit: (props: SpecificQuoteProps) => {
		const { attributes, setAttributes } = props;
		
		return (
			<BlockEditor
				blockName="xv-random-quotes/specific-quote"
				attributes={attributes}
				renderCondition={attributes.postId > 0 || attributes.legacyId > 0}
				placeholderMessage={__('Please enter a Quote ID or Legacy ID in the block settings.', 'xv-random-quotes')}
			>
				<TextControl
					label={__('Quote ID', 'xv-random-quotes')}
					value={attributes.postId || ''}
					onChange={(value) => setAttributes({ postId: value ? parseInt(value) : 0 })}
					type="number"
					help={__('Enter the post ID of the quote to display', 'xv-random-quotes')}
				/>
				<TextControl
					label={__('Legacy ID (optional)', 'xv-random-quotes')}
					value={attributes.legacyId || ''}
					onChange={(value) => setAttributes({ legacyId: value ? parseInt(value) : 0 })}
					type="number"
					help={__('For quotes migrated from old version', 'xv-random-quotes')}
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
