import resolve from '@rollup/plugin-node-resolve';
import commonjs from '@rollup/plugin-commonjs';
import babel from '@rollup/plugin-babel';
import replace from '@rollup/plugin-replace';
import postcss from 'rollup-plugin-postcss';
import postcssImport from 'postcss-import';

export default [
    {
        input: 'src/assets/chat.jsx',
        output: {
            file: 'src/platform/Web2/assets/app.bundle.js',
            format: 'esm',
            sourcemap: true
        },
        plugins: [
            resolve({ extensions: ['.js', '.jsx', '.mjs'] }),
            commonjs(),
            babel({
                babelHelpers: 'bundled',
                presets: ['@babel/preset-react'],
                extensions: ['.js', '.jsx']
            }),
            replace({
                'process.env.NODE_ENV': JSON.stringify('production'),
                preventAssignment: true
            })
        ],
        external: ['@olton/metroui']
    },
    {
        input: 'src/assets/app.css',
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
