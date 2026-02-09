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
            "**/*.js"
        ]
    },
    js.configs.recommended,
    ...compat.extends(
        "plugin:@typescript-eslint/recommended"
    ),
    {
        files: ["**/*.ts"],
        languageOptions: {
            globals: {
                ...globals.node,
            },
            parserOptions: {
                project: "tsconfig.json",
                tsconfigRootDir: __dirname,
                sourceType: "module",
            },
        },
        rules: {
            "@typescript-eslint/explicit-module-boundary-types": "off",
            "@typescript-eslint/no-explicit-any": "warn"
        },
    },
];
