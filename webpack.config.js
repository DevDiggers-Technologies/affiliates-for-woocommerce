/** @format */
/**
 * External dependencies
 */
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const { get } = require( 'lodash' );
const path = require( 'path' );
const { DefinePlugin } = require( 'webpack' );
// const FixStyleOnlyEntriesPlugin = require( 'webpack-fix-style-only-entries' );
const TerserPlugin = require( 'terser-webpack-plugin' );
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");

/**
 * WordPress dependencies
 */
const CustomTemplatedPathPlugin = require( '@wordpress/custom-templated-path-webpack-plugin' );

const NODE_ENV = process.env.NODE_ENV || 'development';

// externals = {};

const externals = {
	'@wordpress/api-fetch'         : { this: [ 'wp', 'apiFetch' ] },
	'@wordpress/blocks'            : { this: [ 'wp', 'blocks' ] },
	'@wordpress/data'              : { this: [ 'wp', 'data' ] },
	'@wordpress/editor'            : { this: [ 'wp', 'editor' ] },
	'@wordpress/element'           : { this: [ 'wp', 'element' ] },
	'@wordpress/components'        : { this: [ 'wp', 'components' ] },
	'@wordpress/hooks'             : { this: [ 'wp', 'hooks' ] },
	'@wordpress/url'               : { this: [ 'wp', 'url' ] },
	'@wordpress/html-entities'     : { this: [ 'wp', 'htmlEntities' ] },
	'@wordpress/i18n'              : { this: [ 'wp', 'i18n' ] },
	'@wordpress/keycodes'          : { this: [ 'wp', 'keycodes' ] },
	'@woocommerce/settings'        : { this: [ 'wc', 'wcSettings' ] },
	'tinymce'                      : 'tinymce',
	'moment'                       : 'moment',
	'react'                        : 'React',
	'lodash'                       : 'lodash',
	'react-dom'                    : 'ReactDOM',
	// 'react-notifications-component': 'react-notifications-component',
};

const wcAdminPackages = [
	'components',
	'csv-export',
	'currency',
	'customer-effort-score',
	'date',
	'experimental',
	'explat',
	'navigation',
	'notices',
	'number',
	'data',
	'tracks',
	'onboarding',
];

wcAdminPackages.forEach( name => {
	externals[ `@woocommerce/${ name }` ] = {
		this: [ 'wc', name.replace( /-([a-z])/g, ( match, letter ) => letter.toUpperCase() ) ],
	};
} );

// const externals = {};

const webpackConfig = {
	mode: NODE_ENV,
	entry: {
		'admin'  : './src/admin/index.js',
		'front'  : './src/front/index.js',
	},
	output: {
		filename     : './assets/js/[name].js',
		path         : __dirname,
		libraryTarget: 'this',
		chunkFilename: `./assets/js/chunks/[name].js`,
	},
	// externals,
	module: {
		rules: [
			{
				test: /\.jsx?$/,
				loader: 'babel-loader',
				exclude: /node_modules/,
			},
			{
				test: /\.(jsx|js)$/,
				use: {
					loader: 'babel-loader',
					options: {
						presets: [
							[ '@babel/preset-env', { loose: true, modules: 'commonjs' } ],
						],
						plugins: [ 'transform-es2015-template-literals' ],
					},
				},
				include: new RegExp( '/node_modules\/(' +
					'|acorn-jsx' +
					'|d3-array' +
					'|debug' +
					'|regexpu-core' +
					'|unicode-match-property-ecmascript' +
					'|unicode-match-property-value-ecmascript)/'
				),
			},
			{
				test: /\.(less|css)$/,
				use: [
					MiniCssExtractPlugin.loader,
					{ loader: 'css-loader', options: {
						url: false,
						sourceMap: false,
						// import: {
						// 	filter: (url, media, resourcePath) => {
						// 		// resourcePath - path to css file

						// 		// Don't handle `style.css` import
						// 		if (url.includes("variables.css")) {
						// 			return false;
						// 		}

						// 		return true;
						// 	},
						// },
					} },
					{ loader: 'less-loader', options: { sourceMap: false, javascriptEnabled: true } }
				],
			}
		],
	},
	resolve: {
		extensions: [ '.json', '.js', '.jsx' ],
		modules: [
			path.join( __dirname, 'src' ),
			'node_modules',
		],
		alias: {
			'@woocommerce/wc-admin-settings': path.resolve(
				__dirname,
				'src/reports/settings/index.js'
			),
		},
	},
	plugins: [
		// new FixStyleOnlyEntriesPlugin(),
		// Inject the current feature flags.
		new DefinePlugin({
            wcSettings : JSON.stringify({
                adminUrl: 'http://localhost/wp-admin/',
                locale: 'en-US',
                currency: {
                    code: 'USD',
                    precision: 2,
                    symbol: '$'
                },
                date: {
                    dow: 0,
                },
                orderStatuses: {
                    pending: 'Pending payment',
                    processing: 'Processing',
                    'on-hold': 'On hold',
                    completed: 'Completed',
                    cancelled: 'Cancelled',
                    refunded: 'Refunded',
                    failed: 'Failed',
                },
                l10n: {
                    userLocale: 'en_US',
                    weekdaysShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                },
                wcAdminSettings: {
                    woocommerce_actionable_order_statuses: [],
                    woocommerce_excluded_report_order_statuses: [],
                },
            })
        }),
		new CustomTemplatedPathPlugin( {
			modulename( outputPath, data ) {
				const entryName = get( data, [ 'chunk', 'name' ] );
				if ( entryName ) {
					return entryName.replace( /-([a-z])/g, ( match, letter ) => letter.toUpperCase() );
				}
				return outputPath;
			},
		} ),
		new MiniCssExtractPlugin( {
			filename: './assets/css/[name].css',
		} ),
	],
	optimization: {
		minimize: NODE_ENV !== 'development',
		minimizer: [ new TerserPlugin(), new CssMinimizerPlugin() ],
		splitChunks: {
			name: false,
		},
	},
};

if ( webpackConfig.mode !== 'production' ) {
	webpackConfig.devtool = 'inline-source-map';
}

module.exports = webpackConfig;