import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import babel from '@rollup/plugin-babel';
import replace from '@rollup/plugin-replace';
import postcss from 'rollup-plugin-postcss';
import postcssImport from 'postcss-import';
import terser from '@rollup/plugin-terser';
import json from '@rollup/plugin-json';

export default [
    {
        input: 'ui/js/app.jsx',
        output: {
            file: 'src/platform/Web2/assets/app.bundle.js',
            format: 'esm',
            sourcemap: true,
            inlineDynamicImports: true
        },
        plugins: [
            resolve({ extensions: ['.js', '.jsx', '.mjs'] }),
            commonjs(),
            babel({
                babelHelpers: 'bundled',
                extensions: ['.js', '.jsx'],
                presets: [
                    [
                        '@babel/preset-react',
                        {
                            runtime: 'automatic'
                        }
                    ]
                ],
            }),
            replace({
                'process.env.NODE_ENV': JSON.stringify('production'),
                preventAssignment: true
            }),
            terser({
                format: {
                    comments: false
                }
            }),
            json()
        ],
        external: []
    },
    {
        input: 'ui/css/app.css',
        output: {
            file: 'src/platform/Web2/assets/app.bundle.css'
        },
        plugins: [
            postcss({
                plugins: [postcssImport()],
                extract: true,
                minimize: true
            })
        ]
    }
]
