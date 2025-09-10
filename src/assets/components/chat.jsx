import { useTranslations } from "../translationsContext.jsx";
import { useThemeMode } from '../themeContext.jsx';

import React, { useState, useRef, useEffect } from "react";
import Markdown from "react-markdown";
import remarkGfm from 'remark-gfm';
import remarkRehype from 'remark-rehype';
import rehypeRaw from 'rehype-raw';
import { Prism as SyntaxHighlighter } from 'react-syntax-highlighter';
import { dracula, materialLight } from 'react-syntax-highlighter/dist/esm/styles/prism';

export default function ChatComponent() {
    const t = useTranslations();

    const themeMode = useThemeMode();
    const syntaxTheme = themeMode === 'dark' ? dracula : materialLight;

    const [userMessage, setUserMessage] = useState(t["chat.input.text"]);
    const [renderedOutput, setRenderedOutput] = useState("");
    const [liveText, setLiveText] = useState("");
    const [typing, setTyping] = useState(false);

    const eventSourceRef = useRef(null);
    const bufferRef = useRef("");
    const tokenQueueRef = useRef([]);
    const runningRef = useRef(false);
    const endCheckTimerRef = useRef(null);

    const flushBuffer = () => {
        if (bufferRef.current.trim()) {
            const r = bufferRef.current;
            setRenderedOutput(prev => prev + r);
            bufferRef.current = "";
            setLiveText("");
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
        setLiveText(prev => prev + token);

        setTimeout(() => requestAnimationFrame(processQueue), 16);
    };

    const handleSend = async () => {
        const msg = userMessage.trim();
        if (!msg) return;

        setRenderedOutput("");
        setLiveText("");
        setTyping(true);

        if (eventSourceRef.current) eventSourceRef.current.close();

        bufferRef.current = "";
        tokenQueueRef.current = [];
        runningRef.current = false;

        try {
            const res = await fetch("/chat", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ chat_id: 123, message: msg }),
            });

            if (!res.ok) throw new Error("Error for send messages.");
            const { message } = await res.json();

            const es = new EventSource(`/chat?stream&message=${message}`);
            eventSourceRef.current = es;

            const decoders = {
                ollama: (p) => p.message?.content ?? p.token,
                llamacpp: (p) => p.choices?.[0]?.delta?.content ?? "",
            };

            Object.entries(decoders).forEach(([adapter, decode]) => {
                es.addEventListener(adapter, (event) => {
                    setTyping(false);

                    if (event.data === "END-OF-STREAM") {
                        flushBuffer();
                        es.close();
                        return;
                    }

                    try {
                        const payload = JSON.parse(event.data);
                        let token = decode(payload);
                        if (typeof token !== "string") token = JSON.stringify(token);

                        tokenQueueRef.current.push(token);
                        if (!runningRef.current) processQueue();
                    } catch (e) {
                        console.error(`[${adapter}] JSON parse error`, e, event.data);
                    }
                });
            });

            es.onerror = () => {
                flushBuffer();
                setTyping(false);
            };
        } catch (err) {
            console.error("POST error:", err);
            setTyping(false);
            return;
        }

        clearInterval(endCheckTimerRef.current);
        endCheckTimerRef.current = setInterval(() => {
            if (!runningRef.current && tokenQueueRef.current.length === 0) {
                flushBuffer();
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
            <div className="input-group">
                <input
                    className="form-control"
                    type="text"
                    value={userMessage}
                    onChange={(e) => setUserMessage(e.target.value)}
                />
                <button className="btn btn-outline-secondary" onClick={handleSend}>
                    Send
                </button>
            </div>

            <div className="mt-3">
                <div className={`typing-dots ${typing ? "" : "hidden"}`}>
                    <span></span><span></span><span></span>
                </div>

                <div>
                    <Markdown
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
                                    <SyntaxHighlighter style={syntaxTheme} PreTag="pre" language={match[1]} {...props}>
                                        {String(children).replace(/\n$/, '')}
                                    </SyntaxHighlighter>
                                ) : (
                                    <code className={className} {...props}>
                                        {children}
                                    </code>
                                );
                            }
                        }}
                    >
                        {renderedOutput}
                    </Markdown>
                </div>

                {liveText && <div className="output">{liveText}</div>}
            </div>
        </div>
    );
}