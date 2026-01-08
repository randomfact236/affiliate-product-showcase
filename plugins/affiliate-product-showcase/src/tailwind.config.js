/** @type {import('tailwindcss').Config} */
export default {
	content: [
		'./blocks/**/*.{js,jsx,ts,tsx,php}',
		'./admin/**/*.php',
		'./public/**/*.php',
		'./src/js/**/*.{js,jsx}'
	],
	theme: {
		extend: {}
	},
	plugins: []
};
