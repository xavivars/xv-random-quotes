/**
 * Block Editor Wrapper Component
 * Shared wrapper structure for all quote blocks
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { ReactNode } from 'react';

interface BlockEditorProps {
	blockName: string;
	attributes: Record<string, any>;
	children: ReactNode;
	renderCondition?: boolean;
	placeholderMessage?: string | null;
}

export const BlockEditor = ({ 
	blockName, 
	attributes, 
	children, 
	renderCondition = true,
	placeholderMessage = null 
}: BlockEditorProps) => {
	const blockProps = useBlockProps();
	
	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Quote Settings', 'stray-quotes')}>
					{children}
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				{renderCondition ? (
					<ServerSideRender
						block={blockName}
						attributes={attributes}
					/>
				) : (
					placeholderMessage && <p>{placeholderMessage}</p>
				)}
			</div>
		</>
	);
};
