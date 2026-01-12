/** @type {import('tailwindcss').Config} */
module.exports = {
	prefix: 'aps-',
	important: '.aps-root',
	content: [
		'./src/**/*.{js,jsx,ts,tsx}',
		'./frontend/**/*.{js,jsx,ts,tsx}',
		'./blocks/**/*.{js,jsx,php}',
		'./**/*.php',
		'!./vendor/**',
		'!./node_modules/**',
		'!./dist/**',
		'!./build/**',
		'!./wp-admin/**',
		'!./wp-includes/**',
		'!./wp-content/uploads/**',
	],
	theme: {
		extend: {},
	},
	corePlugins: {
		preflight: false,
	},
	plugins: [],
};
