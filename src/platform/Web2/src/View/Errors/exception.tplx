<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <title>Error: {$exception->getMessage()}</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">

    <style>
        :root {
            --bg: #1e1e1e;
            /* VSCode editor background */
            --fg: #d4d4d4;
            /* Default text */
            --muted: #858585;
            /* Line numbers / comments */
            --split: #3a3a3a;
            /* Divider lines */
            --errbg: #2d1a1a;
            /* Error line background */
            --red: #f44747;
            /* Error red */
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            background: var(--bg);
            margin: 0;
            padding: 20px;
            color: var(--fg);
        }

        .container {
            max-width: 960px;
            margin: auto;
            background: #252526;
            /* Panel background (VSCode sidebar/panel) */
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .4);
            padding: 24px;
        }

        h1 {
            color: var(--red);
            margin: 0 0 12px;
            font-size: 22px;
        }

        .message {
            margin: 0 0 18px;
            color: var(--fg);
        }

        .row {
            display: grid;
            grid-template-columns: 64px 1fr;
            align-items: baseline;
        }

        .ln {
            user-select: none;
            text-align: right;
            padding: 3px 12px;
            color: var(--muted);
            border-right: 1px solid var(--split);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 13px;
        }

        .code-view {
            background: #1e1e1e;
            color: var(--fg);
            font-size: 13px;
            overflow: auto;
        }

        .code {
            white-space: pre;
            font-family: Consolas, ui-monospace, SFMono-Regular, Menlo, monospace;
        }

        .code pre {
            margin: 0;
        }

        .keyword {
            color: #569CD6;
        }

        /* function, return, class */
        .string {
            color: #CE9178;
        }

        /* "string" */
        .comment {
            color: #6A9955;
            font-style: italic;
        }

        /* // comment */
        .number {
            color: #B5CEA8;
        }

        .variable {
            color: #9CDCFE;
        }

        .default {
            color: #C586C0;
        }

        .row.error {
            background: var(--errbg);
        }

        .trace {
            margin-top: 24px;
        }

        .trace h2 {
            margin: 0 0 8px;
            font-size: 16px;
            color: var(--fg);
        }

        .trace pre {
            background: #1e1e1e;
            color: var(--fg);
            padding: 12px;
            border-radius: 8px;
            margin: 0;
            overflow: auto;
            white-space: pre-wrap;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 13px;
            height: 250px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>An exception error</h1>
        <p class="message"><strong>Error:</strong> {$exception->getMessage()}</p>

        <h2>Source</h2>
        <div class="code-view">
            <sx:foreach loop="$displayLines" value="$line">
                <div class="row <sx:if is=" $line.isErrorLine">error</sx:if>">
                    <div class="ln">{$line.lineNumber}</div>
                    <div class="code"><code>{$line.code|raw}</code></div>
                </div>
            </sx:foreach>
        </div>

        <div class="trace">
            <h2>Stack Trace</h2>
            <pre>{$traceAsString|raw}</pre>
        </div>
    </div>
</body>

</html>