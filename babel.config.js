module.exports = function( api ) {
	api.cache( true );

	return {
		presets: [ '@wordpress/babel-preset-default' ],
		plugins: [
			'@babel/plugin-transform-runtime',
			'@babel/plugin-transform-async-to-generator',
			'transform-class-properties',
			[ '@babel/transform-react-jsx', {
				pragma: 'createElement',
			} ],
			[
				'@wordpress/babel-plugin-import-jsx-pragma',
				{
					scopeVariable: 'createElement',
					source: '@wordpress/element',
					isDefault: false,
				},
			],
		],
		sourceType: 'unambiguous',
		env: {
			production: {
				plugins: [
					[
						'@wordpress/babel-plugin-makepot',
						{
							output: 'i18n/affiliates-for-woocommerce.po',
						},
					],
				],
			},
		},
	};
};
