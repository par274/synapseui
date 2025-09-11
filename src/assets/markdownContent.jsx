/**
 * MarkdownContent Component
 * 
 * Original content: https://github.com/shadcn-ui/ui
 *
 * This file was originally adapted from a `.tsx` implementation (TypeScript + React).
 * It was converted to plain `.jsx` because the project does not require TypeScript
 * and to simplify integration with an existing JavaScript-only setup.
 *
 * The component provides:
 * - Streaming-friendly Markdown rendering (splitting into tokenized blocks with `marked`)
 * - Support for tables, GitHub-flavored markdown (remark-gfm)
 * - Safe HTML rendering (rehype-raw)
 * - Syntax highlighting for code blocks (via react-syntax-highlighter)
 * - Custom <PreWithLabel> wrapper for copy-to-clipboard UI in code blocks
 *
 * Why converted from TSX to JSX:
 * - Avoided the need for TypeScript tooling and configuration (tsconfig, type definitions)
 * - Prevented build errors from Rollup related to `.tsx` parsing
 * - Reduced complexity, since type-safety was not a project requirement
 *
 * Note:
 * - If you reintroduce TypeScript in the future, this file can be migrated back to `.tsx`
 *   by restoring type definitions (Props, interfaces, etc.)
 */

import { useThemeMode } from '../../src/assets/themeContext.jsx';

import React, { memo, useMemo } from "react";
import ReactMarkdown from "react-markdown";
import remarkGfm from "remark-gfm";
import remarkRehype from "remark-rehype";
import rehypeRaw from "rehype-raw";
import { Prism as SyntaxHighlighter } from "react-syntax-highlighter";
import { materialDark, materialLight } from "react-syntax-highlighter/dist/esm/styles/prism";

import { marked } from "marked";

const PreWithLabel = ({ children, lang, ...props }) => (
    <pre {...props} className="rounded-4 py-2 px-3 relative">
        <div className="d-flex align-items-center justify-content-between mb-4 ff-override">
            <div>
                <span className="text-light fs-small">{lang}</span>
            </div>
            <div>
                <a className="link-light link-offset-2 link-underline-opacity-0 fs-small" href="#">
                    <i className="bi bi-clipboard-check"></i>
                    <span className="label">Kodu kopyala</span>
                </a>
            </div>
        </div>
        {children}
    </pre>
);

function parseMarkdownIntoBlocks(markdown) {
    if (!markdown) return [];
    const tokens = marked.lexer(markdown);
    return tokens.map(token => token.raw);
}

const MemoizedMarkdownBlock = memo(({ content, className }) => {
    const themeMode = useThemeMode();
    const syntaxTheme = themeMode === "dark" ? materialDark : materialLight;

    return (
        <ReactMarkdown
            remarkPlugins={[remarkGfm, remarkRehype]}
            rehypePlugins={[rehypeRaw]}
            components={{
                table: ({ node, ...props }) => (
                    <table className="table table-striped" {...props} />
                ),
                th: ({ node, ...props }) => <th scope="col" {...props} />,
                td: ({ node, ...props }) => <td scope="row" {...props} />,
                code: ({ node, inline, className, children, ...props }) => {
                    const match = /language-(\w+)/.exec(className || '');
                    return !inline && match ? (
                        <SyntaxHighlighter
                            style={syntaxTheme}
                            PreTag={(p) => <PreWithLabel {...p} lang={match[1]} />}
                            language={match[1]}
                            {...props}
                        >
                            {String(children).toString().replace(/\n$/, "")}
                        </SyntaxHighlighter>
                    ) : (
                        <code className={className} {...props}>
                            {children}
                        </code>
                    );
                }
            }}
            className={className}
        >
            {content}
        </ReactMarkdown>
    );
});

export const MarkdownContent = memo(({ content, id }) => {
    const blocks = useMemo(() => parseMarkdownIntoBlocks(content || ""), [content]);
    return blocks.map((block, index) => (
        <MemoizedMarkdownBlock key={`${id}-block_${index}`} content={block} />
    ));
});