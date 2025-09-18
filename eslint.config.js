import js from "@eslint/js";
import globals from "globals";
import tseslint from "typescript-eslint";
import pluginReact from "eslint-plugin-react";
import { defineConfig } from "eslint/config";

export default defineConfig([
    {
        files: ["**/*.{js,mjs,cjs,ts,mts,cts,jsx,tsx}"],
        plugins: {
            js,
        },
        extends: [
            js.configs.recommended,
        ],
        languageOptions: {
            globals: {
                ...globals.browser,
                ...globals.node,
            },
        },
        settings: {
            react: {
                version: "detect"
            },
        },
    },

    ...tseslint.configs.recommended,
    pluginReact.configs.flat.recommended,

    {
        rules: {
            "react/react-in-jsx-scope": "off",
            "react/jsx-uses-react": "off",
        },
    },
]);