/** @type {import('stylelint').Config} */
module.exports = {
	extends: ['stylelint-config-standard', 'stylelint-config-tailwindcss'],
	ignoreFiles: [
		'**/*.min.css',
		'**/dist/**',
		'**/build/**',
		'**/node_modules/**',
		'**/vendor/**',
		'**/*.scss',
		'**/*.sass',
		'**/*.php',
	],
	rules: {},
};
