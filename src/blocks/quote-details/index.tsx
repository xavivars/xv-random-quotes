/**
 * Quote Details Sidebar Panel for Block Editor
 *
 * Provides a custom sidebar panel in the Block Editor for editing
 * quote metadata (source field). Uses WordPress components and hooks
 * to integrate with the post meta system.
 *
 * @package
 */

import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { TextareaControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';

/**
 * Post meta structure for xv_quote post type
 */
interface QuoteMeta {
	_quote_source: string;
	[ key: string ]: unknown;
}

/**
 * Quote Details Panel Component
 *
 * Renders a sidebar panel with a textarea for editing the quote source.
 * Supports HTML markup in the source field.
 */
const QuoteDetailsPanel: React.FC = () => {
	// Get the current post type
	const postType = useSelect< string >(
		( select: any ) => select( 'core/editor' ).getCurrentPostType(),
		[]
	);

	// Get the meta value and setter for _quote_source
	const [ meta, setMeta ] = useEntityProp( 'postType', postType, 'meta' );

	// Get the current source value
	const quoteSource = ( meta as QuoteMeta )._quote_source || '';

	// Update the source value
	const updateQuoteSource = ( newValue: string ) => {
		setMeta( { ...( meta as QuoteMeta ), _quote_source: newValue } );
	};

	return (
		<PluginDocumentSettingPanel
			name="quote-details"
			title={ __( 'Quote Details', 'stray-quotes' ) }
			className="quote-details-panel"
		>
			<TextareaControl
				label={ __( 'Source', 'stray-quotes' ) }
				value={ quoteSource }
				onChange={ updateQuoteSource }
				rows={ 4 }
				help={ __(
					'Enter the source or citation for this quote (e.g., author name, book title, URL). HTML is allowed.',
					'stray-quotes'
				) }
			/>
		</PluginDocumentSettingPanel>
	);
};

// Register the plugin
registerPlugin( 'xv-quote-details', {
	render: QuoteDetailsPanel,
	icon: 'format-quote',
} );
