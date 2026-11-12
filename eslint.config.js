import tsParser from '@typescript-eslint/parser';
import vue from 'eslint-plugin-vue';

export default [
    {
        ignores: [
            'resources/js/actions/**',
            'resources/js/components/ui/**',
            'resources/js/routes/**',
            'resources/js/wayfinder/**',
        ],
    },
    ...vue.configs['flat/essential'],
    {
        rules: {
            'vue/multi-word-component-names': 'off',
        },
    },
    {
        files: ['resources/js/**/*.ts', 'resources/js/**/*.tsx'],
        languageOptions: {
            parser: tsParser,
        },
    },
    {
        files: ['resources/js/**/*.vue'],
        languageOptions: {
            parserOptions: {
                parser: tsParser,
            },
        },
    },
];
