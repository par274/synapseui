import React, { useState, useRef, useEffect } from "react";

import MarkdownIt from "markdown-it";

const md = new MarkdownIt({
    html: true,
    linkify: true,
    typographer: true,
});

function addStyleClasses(html) {
    return html
        .replace(/<ul>/g, '<ul class="list-disc pl-5 space-y-1">')
        .replace(/<li>/g, '<li class="ml-2">')
        .replace(
            /<pre>/g,
            '<pre class="bg-base-300 text-white p-4 rounded overflow-x-auto">'
        )
        .replace(
            /<code>/g,
            '<code class="font-mono text-sm bg-base-300 px-1 rounded">'
        );
}

export default function Chat() {
    const [userMessage, setUserMessage] = useState("what is that mean in coding hello world!");
    const [renderedOutput, setRenderedOutput] = useState("");
    const [typing, setTyping] = useState(false);

    const eventSourceRef = useRef(null);
    const bufferRef = useRef("");
    const tokenQueueRef = useRef([]);
    const runningRef = useRef(false);
    const endCheckTimerRef = useRef(null);
    const liveDivRef = useRef(null);

    const flushBuffer = (force = false) => {
        const buffer = bufferRef.current;
        const backticks = (buffer.match(/```/g) || []).length;
        const codeBlockOpen = backticks % 2 !== 0;

        if (!force && codeBlockOpen) return;

        if (buffer.trim()) {
            const rendered = addStyleClasses(md.render(buffer));
            setRenderedOutput((prev) => prev + rendered);
            bufferRef.current = "";
        }

        if (liveDivRef.current) {
            liveDivRef.current.textContent = "";
        }
    };

    const processQueue = () => {
        if (tokenQueueRef.current.length === 0) {
            runningRef.current = false;
            return;
        }

        runningRef.current = true;
        const token = tokenQueueRef.current.shift();
        bufferRef.current += token;

        if (liveDivRef.current) {
            for (const ch of token) {
                const span = document.createElement("span");
                span.textContent = ch === " " ? "\u00A0" : ch;
                span.className = "token";
                liveDivRef.current.appendChild(span);
            }
        }

        if (/\n\n$/.test(bufferRef.current) || /```$/.test(bufferRef.current)) {
            flushBuffer();
        }

        setTimeout(processQueue, 25);
    };

    const handleSend = () => {
        const msg = userMessage.trim();
        if (!msg) return;

        setRenderedOutput("");
        setTyping(true);

        if (liveDivRef.current) liveDivRef.current.textContent = "";

        if (eventSourceRef.current) eventSourceRef.current.close();

        bufferRef.current = "";
        tokenQueueRef.current = [];
        runningRef.current = false;

        const es = new EventSource(`/chat?message=${encodeURIComponent(msg)}`);
        eventSourceRef.current = es;

        es.onmessage = (event) => {
            setTyping(false);

            if (event.data === "END-OF-STREAM") {
                flushBuffer(true);
                es.close();
                setTyping(false);
                return;
            }

            try {
                const payload = JSON.parse(event.data);
                let token = payload.token?.message?.content ?? payload.token;
                if (typeof token !== "string") token = JSON.stringify(token);

                tokenQueueRef.current.push(token);
                if (!runningRef.current) processQueue();
            } catch (e) {
                console.error("JSON parse error", e, event.data);
            }
        };

        es.onerror = () => {
            flushBuffer(true);
            setTyping(false);
        };

        clearInterval(endCheckTimerRef.current);
        endCheckTimerRef.current = setInterval(() => {
            if (!runningRef.current && tokenQueueRef.current.length === 0 && bufferRef.current.trim()) {
                flushBuffer(true);
            }
        }, 1000);
    };

    useEffect(() => {
        return () => {
            if (eventSourceRef.current) eventSourceRef.current.close();
            clearInterval(endCheckTimerRef.current);
        };
    }, []);

    return (
        <div>
            <div className="form-group flex-row">
                <input className="mr-1" type="text" value={userMessage} onChange={(e) => setUserMessage(e.target.value)} />
                <button className="button info" onClick={handleSend}>Send</button>
            </div>
            <div class="mt-5">
                <div className={`typing-dots ${typing ? "" : "hidden"}`}>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <div dangerouslySetInnerHTML={{ __html: renderedOutput }}></div>

                <div className="text-gray-300 whitespace-pre-wrap break-words" ref={liveDivRef}></div>
            </div>
        </div>
    );
}