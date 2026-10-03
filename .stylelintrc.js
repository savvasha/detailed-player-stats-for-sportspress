/**
 * Stylelint config - WordPress coding standards for CSS.
 *
 * @package DetailedPlayerStatsForSportsPress
 */

module.exports = {
	extends: '@wordpress/stylelint-config/stylistic',
	ignoreFiles: [ 'assets/vendor/**', 'node_modules/**', '**/*.min.css' ],
	rules: {
		// Advisory rule disabled: reordering selectors to satisfy it risks
		// changing the cascade/intent without changing the rendered result.
		'no-descending-specificity': null,
		// The only id selectors here target WordPress ThickBox's own core
		// elements ( #TB_window, #TB_ajaxContent ), whose ids cannot be renamed.
		'selector-id-pattern': null,
	},
};
