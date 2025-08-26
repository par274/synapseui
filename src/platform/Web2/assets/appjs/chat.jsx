/**
 * ChatBehavior Component
 * ----------------------
 * Implements a live chat interface with token-by-token streaming and Markdown rendering.
 *
 * Features:
 * 1. Markdown Rendering:
 *    - Uses MarkdownIt to parse user messages or AI responses.
 *    - Applies TailwindCSS classes for styled lists, code blocks, and inline code.
 *
 * 2. Token Streaming Animation:
 *    - Each token is appended to `liveDiv` as a <span> with the class "token".
 *    - Creates a typewriter-style animation effect.
 *    - Spaces are preserved with &nbsp; for proper display.
 *
 * 3. Buffer Management:
 *    - Tokens are accumulated in a `buffer` for Markdown rendering.
 *    - `flushBuffer(force)` renders buffer into `chatOutput` as styled HTML.
 *    - Code blocks are respected: buffer waits for proper closing before rendering.
 *
 * 4. EventSource Integration:
 *    - Listens to server-sent events from `/chat?message=...`.
 *    - Handles each incoming token JSON payload and pushes it to the token queue.
 *    - Detects end of stream ("END-OF-STREAM") and finalizes rendering.
 *
 * 5. Error Handling & Fallbacks:
 *    - On SSE errors, any remaining buffer is flushed.
 *    - A periodic check (1s) ensures leftover buffer is rendered if no new tokens arrive.
 *
 * 6. React Integration:
 *    - Implemented as a React component, but does not render any visible UI itself.
 *    - Mounts into `liveDiv` to manage live token animation and output rendering.
 *
 * Usage:
 * - Ensure the following elements exist in HTML:
 *     - <input id="userMessage" /> for user input
 *     - <button id="sendBtn" /> for sending messages
 *     - <div id="chatOutput"></div> for rendered Markdown output
 *     - <div id="liveDiv"></div> for live token animation
 *
 * Note:
 * - The component relies on TailwindCSS classes defined in `addTailwindClasses`.
 * - Tokens are processed every 25ms to produce smooth animation.
 */

import MarkdownIt from "../vendor/markdown-it/index.js";

const { useEffect, useRef } = React;

const md = new MarkdownIt({
    html: true,
    linkify: true,
    typographer: true,
});

function addTailwindClasses(html) {
    return html
        .replace(/<ul>/g, '<ul class="list-disc pl-5 space-y-1">')
        .replace(/<li>/g, '<li class="ml-2">')
        .replace(/<pre>/g, '<pre class="bg-base-300 text-white p-4 rounded overflow-x-auto">')
        .replace(/<code>/g, '<code class="font-mono text-sm bg-base-300 px-1 rounded">');
}

window.ChatBehavior = function () {
    const eventSourceRef = useRef(null);
    const endCheckTimer = useRef(null);

    useEffect(() => {
        const input = document.getElementById("userMessage");
        const btn = document.getElementById("sendBtn");
        const output = document.getElementById("chatOutput");
        const liveDiv = document.getElementById("liveDiv");

        const handleSend = () => {
            const msg = input.value.trim();
            if (!msg) return;

            output.innerHTML = "";
            liveDiv.textContent = "";
            liveDiv.style.display = "block";

            if (eventSourceRef.current) eventSourceRef.current.close();

            let buffer = "";
            const tokenQueue = [];
            let running = false;

            const appendTokenWithAnimation = (token) => {
                for (const ch of token) {
                    const span = document.createElement("span");
                    span.textContent = ch === " " ? "\u00A0" : ch;
                    span.className = "token";
                    liveDiv.appendChild(span);
                }
            };

            const flushBuffer = (force = false) => {
                const backticks = (buffer.match(/```/g) || []).length;
                const codeBlockOpen = backticks % 2 !== 0;

                if (!force && codeBlockOpen) return;

                if (buffer.trim()) {
                    const rendered = addTailwindClasses(md.render(buffer));
                    output.innerHTML += rendered;
                    buffer = "";
                }

                liveDiv.textContent = "";
            };

            const processQueue = () => {
                if (!tokenQueue.length) { running = false; return; }

                running = true;
                const token = tokenQueue.shift();
                buffer += token;

                appendTokenWithAnimation(token);

                if (/\n\n$/.test(buffer) || /```$/.test(buffer)) {
                    flushBuffer();
                }

                setTimeout(processQueue, 25);
            };

            eventSourceRef.current = new EventSource(`/chat?message=${encodeURIComponent(msg)}`);

            eventSourceRef.current.onmessage = (event) => {
                if (event.data === "END-OF-STREAM") {
                    flushBuffer(true);
                    eventSourceRef.current.close();
                    return;
                }

                try {
                    const payload = JSON.parse(event.data);
                    let token =
                        payload.token?.message?.content ?? payload.token;

                    if (typeof token !== "string")
                        token = JSON.stringify(token);

                    tokenQueue.push(token);
                    if (!running) processQueue();
                } catch (err) {
                    console.error("JSON parse error", err, event.data);
                }
            };

            eventSourceRef.current.onerror = () => {
                flushBuffer(true);
            };

            clearInterval(endCheckTimer.current);
            endCheckTimer.current = setInterval(() => {
                if (!running && tokenQueue.length === 0 && buffer.trim()) {
                    flushBuffer(true);
                }
            }, 1000);

        };

        btn.addEventListener("click", handleSend);

        return () => {
            btn.removeEventListener("click", handleSend);
            if (eventSourceRef.current) eventSourceRef.current.close();
            clearInterval(endCheckTimer.current);
        };
    }, []);

    return null;
};

ReactDOM.createRoot(document.getElementById('liveDiv')).render(
    React.createElement(window.ChatBehavior)
);
