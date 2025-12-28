/**
 * Custom webpack configuration for XV Random Quotes
 *
 * Extends the default @wordpress/scripts webpack config
 * to customize the build process for the Block Editor sidebar panel.
 *
 * Output directory changed to src/generated/ to keep all source code
 * in a single top-level directory for cleaner distribution.
 *
 * @package XVRandomQuotes
 */

const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		'quote-details': './src/blocks/quote-details/index.tsx',
	},
	output: {
		path: path.resolve( process.cwd(), 'src/generated' ),
		filename: '[name].js',
	},
};
