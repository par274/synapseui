import resolve from '@rollup/plugin-node-resolve';

export default [
    {
        input: 'node_modules/markdown-it/esm/index.js',
        output: {
            file: 'src/platform/Web2/assets/vendor/markdown-it/index.js',
            format: 'esm'
        },
        plugins: [
            resolve(),
        ]
    }
];