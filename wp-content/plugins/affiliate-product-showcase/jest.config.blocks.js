/**
 * Jest Configuration for Blocks
 * 
 * @package AffiliateProductShowcase
 * @since 2.0.0
 */

module.exports = {
    testEnvironment: 'jsdom',
    setupFilesAfterEnv: ['<rootDir>/blocks/shared/__tests__/setup.js'],
    testMatch: ['<rootDir>/blocks/**/__tests__/**/*.test.{js,jsx}'],
    moduleNameMapper: {
        '^@wordpress/(.*)$': '<rootDir>/node_modules/@wordpress/$1',
        '\\.(css|scss)$': 'identity-obj-proxy',
    },
    transform: {
        '^.+\\.(js|jsx)$': 'babel-jest',
    },
    collectCoverageFrom: [
        'blocks/**/*.{js,jsx}',
        '!blocks/**/__tests__/**',
        '!blocks/**/index.js',
    ],
    coverageThreshold: {
        global: {
            branches: 70,
            functions: 80,
            lines: 80,
            statements: 80,
        },
    },
};
