/** @type {import('eslint').Linter.Config} */
module.exports = {
  root: true,
  env: {
    browser: true,
    es2021: true,
    node: true,
  },
  parserOptions: {
    ecmaVersion: 'latest',
    sourceType: 'module',
    ecmaFeatures: {
      jsx: true,
    },
  },
  extends: ['plugin:@wordpress/eslint-plugin/recommended'],
  ignorePatterns: [
    'vendor/',
    'wp-admin/',
    'wp-includes/',
    'wp-content/uploads/',
    '**/dist/',
    '**/build/',
    '**/node_modules/',
  ],
};
