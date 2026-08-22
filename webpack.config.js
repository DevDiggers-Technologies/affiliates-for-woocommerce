/** @format */
/**
 * External dependencies
 */
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const { get } = require( 'lodash' );
const path = require( 'path' );
const { DefinePlugin } = require( 'webpack' );
const TerserPlugin = require( 'terser-webpack-plugin' );
const CssMinimizerPlugin = require( 'css-minimizer-webpack-plugin' );

/**
 * WordPress dependencies
 */
const CustomTemplatedPathPlugin = require( '@wordpress/custom-templated-path-webpack-plugin' );

const NODE_ENV = process.env.NODE_ENV || 'development';
const IS_PRODUCTION = NODE_ENV === 'production';
const SOURCE_EXTENSIONS = [ '.json', '.js', '.jsx' ];
const CAMEL_CASE_REPLACE_REGEX = /-([a-z])/g;
const WC_ADMIN_SETTINGS_ALIAS = path.resolve( __dirname, 'src/reports/settings/index.js' );
const JS_ENTRY_POINTS = {
	"admin": "./src/admin/index.js",
	"front": "./src/front/index.js",
	"manage-affiliate": "./src/manage-affiliate/index.js"
};
const WC_SETTINGS = {
	adminUrl : 'http://localhost/wp-admin/',
	locale   : 'en-US',
	currency : {
		code     : 'USD',
		precision: 2,
		symbol   : '$',
	},
	date: {
		dow: 0,
	},
	orderStatuses: {
		pending   : 'Pending payment',
		processing: 'Processing',
		'on-hold' : 'On hold',
		completed : 'Completed',
		cancelled : 'Cancelled',
		refunded  : 'Refunded',
		failed    : 'Failed',
	},
	l10n: {
		userLocale   : 'en_US',
		weekdaysShort: [ 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' ],
	},
	wcAdminSettings: {
		woocommerce_actionable_order_statuses    : [],
		woocommerce_excluded_report_order_statuses: [],
	},
};
const COMMON_EXTERNALS = {
	'@wordpress/api-fetch'    : { this: [ 'wp', 'apiFetch' ] },
	'@wordpress/blocks'       : { this: [ 'wp', 'blocks' ] },
	'@wordpress/data'         : { this: [ 'wp', 'data' ] },
	'@wordpress/editor'       : { this: [ 'wp', 'editor' ] },
	'@wordpress/element'      : { this: [ 'wp', 'element' ] },
	'@wordpress/components'   : { this: [ 'wp', 'components' ] },
	'@wordpress/hooks'        : { this: [ 'wp', 'hooks' ] },
	'@wordpress/url'          : { this: [ 'wp', 'url' ] },
	'@wordpress/html-entities': { this: [ 'wp', 'htmlEntities' ] },
	'@wordpress/i18n'         : { this: [ 'wp', 'i18n' ] },
	'@wordpress/keycodes'     : { this: [ 'wp', 'keycodes' ] },
	'@woocommerce/settings'   : { this: [ 'wc', 'wcSettings' ] },
	'tinymce'                 : 'tinymce',
	'moment'                  : 'moment',
	'lodash'                  : 'lodash',
	'react-dom'               : 'ReactDOM',
	'react'                   : 'React',
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

const toCamelCase = value => value.replace( CAMEL_CASE_REPLACE_REGEX, ( match, letter ) => letter.toUpperCase() );
const createExternals = () => {
	const externals = { ...COMMON_EXTERNALS };

	wcAdminPackages.forEach( name => {
		externals[ `@woocommerce/${ name }` ] = {
			this: [ 'wc', toCamelCase( name ) ],
		};
	} );

	return externals;
};

const createCssLoaders = () => ( [
	MiniCssExtractPlugin.loader,
	{
		loader : 'css-loader',
		options: {
			url      : false,
			sourceMap: false,
		},
	},
	{
		loader : 'less-loader',
		options: {
			sourceMap        : false,
			javascriptEnabled: true,
		},
	},
] );

const createBabelRules = () => ( [
	{
		test   : /\.jsx?$/,
		loader : 'babel-loader',
		exclude: /node_modules/,
	},
	{
		test: /\.(jsx|js)$/,
		use : {
			loader : 'babel-loader',
			options: {
				presets: [
					[ '@babel/preset-env', { loose: true, modules: 'commonjs' } ],
				],
				plugins: [ 'transform-es2015-template-literals' ],
			},
		},
		include: new RegExp(
			'/node_modules/(' +
				'|acorn-jsx' +
				'|d3-array' +
				'|debug' +
				'|regexpu-core' +
				'|unicode-match-property-ecmascript' +
				'|unicode-match-property-value-ecmascript)/'
		),
	},
] );

const createPlugins = () => ( [
	new DefinePlugin( {
		wcSettings: JSON.stringify( WC_SETTINGS ),
	} ),
	new CustomTemplatedPathPlugin( {
		modulename( outputPath, data ) {
			const entryName = get( data, [ 'chunk', 'name' ] );

			if ( entryName ) {
				return toCamelCase( entryName );
			}

			return outputPath;
		},
	} ),
	new MiniCssExtractPlugin( {
		filename: './assets/css/[name].css',
	} ),
] );

const webpackConfig = {
	mode : NODE_ENV,
	entry: JS_ENTRY_POINTS,
	output: {
		filename     : './assets/js/[name].js',
		path         : __dirname,
		libraryTarget: 'this',
		chunkFilename: './assets/js/chunks/[name].js',
	},
	externals: createExternals(),
	module   : {
		rules: [
			...createBabelRules(),
			{
				test: /\.(less|css)$/,
				use : createCssLoaders(),
			},
		],
	},
	resolve: {
		extensions: SOURCE_EXTENSIONS,
		modules   : [
			path.join( __dirname, 'src' ),
			'node_modules',
		],
		alias: {
			'@woocommerce/wc-admin-settings': WC_ADMIN_SETTINGS_ALIAS,
		},
	},
	plugins: createPlugins(),
	optimization: {
		minimize : IS_PRODUCTION,
		minimizer: [ new TerserPlugin(), new CssMinimizerPlugin() ],
		splitChunks: {
			name: false,
		},
	},
};

if ( ! IS_PRODUCTION ) {
	webpackConfig.devtool = 'inline-source-map';
}

module.exports = webpackConfig;
