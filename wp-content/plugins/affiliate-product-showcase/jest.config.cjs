module.exports = {
    testEnvironment: 'jsdom',
    moduleNameMapper: {
        '\\.(css|less|scss|sass)$': '<rootDir>/blocks/shared/__tests__/styleMock.cjs',
    },
    setupFilesAfterEnv: ['<rootDir>/blocks/shared/__tests__/setup.js'],
    testMatch: ['**/blocks/**/*.test.js', '**/blocks/**/*.test.jsx'],
    transform: {
        '^.+\\.(js|jsx)$': ['babel-jest', { configFile: './.babelrc' }],
    },
    transformIgnorePatterns: [
        'node_modules/(?!(@wordpress|@babel)/)',
    ],
};
