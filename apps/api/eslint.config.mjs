import js from "@eslint/js";
import { FlatCompat } from "@eslint/eslintrc";
import path from "path";
import { fileURLToPath } from "url";
import globals from "globals";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const compat = new FlatCompat({
    baseDirectory: __dirname,
    recommendedConfig: js.configs.recommended,
});

export default [
    {
        ignores: [
            "dist/**",
            ".turbo/**",
            "node_modules/**",
            "**/*.js" // Ignore JS files for now as project is TS
        ]
    },
    js.configs.recommended,
    ...compat.extends(
        "plugin:@typescript-eslint/recommended",
        "plugin:prettier/recommended"
    ),
    {
        files: ["**/*.ts"],
        languageOptions: {
            globals: {
                ...globals.node,
                ...globals.jest,
            },
            parserOptions: {
                project: "tsconfig.json",
                tsconfigRootDir: __dirname,
                sourceType: "module",
            },
        },
        rules: {
            "@typescript-eslint/interface-name-prefix": "off",
            "@typescript-eslint/explicit-function-return-type": "warn",
            "@typescript-eslint/explicit-module-boundary-types": "warn",
            "@typescript-eslint/no-explicit-any": "error",
            "no-console": "warn",
            "no-debugger": "error",
        },
    },
];
