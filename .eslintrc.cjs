/* eslint-env node */
require("@rushstack/eslint-patch/modern-module-resolution")

module.exports = {
    root: true,
    env: {
        browser: true,
        es2021: true,
    },
    extends: [
        "plugin:vue/vue3-essential",
        "plugin:vue/vue3-strongly-recommended",
        "plugin:vue/vue3-recommended",
        "eslint:recommended",
        "plugin:@typescript-eslint/recommended-type-checked",
        "@vue/eslint-config-typescript",
        "@vue/eslint-config-prettier/skip-formatting",
    ],
    parser: 'vue-eslint-parser',
    parserOptions: {
        project: ['./tsconfig.json'],
        tsconfigRootDir: __dirname,
        ecmaVersion: "latest",
        parser: '@typescript-eslint/parser'
    },
    plugins: [
        '@typescript-eslint',
        'vue'
    ],
    rules: {
        '@typescript-eslint/no-floating-promises': 'off',
        'vue/multi-word-component-names': 'off',
        'vue/no-v-html': 'off',
        'vue/prop-name-casing': 'off',
        '@typescript-eslint/ban-ts-comment': 'off',
    }
}
