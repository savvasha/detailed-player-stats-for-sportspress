/**
 * ESLint flat config - WordPress coding standards for JavaScript.
 *
 * @package DetailedPlayerStatsForSportsPress
 */

const wordpress = require( '@wordpress/eslint-plugin' );

module.exports = [
	{
		ignores: [ 'assets/vendor/**', 'node_modules/**', '**/*.min.js' ],
	},
	...wordpress.configs[ 'recommended-with-formatting' ],
];
