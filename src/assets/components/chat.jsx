import { useTranslations } from "../translationsContext.jsx";

import React, { useState, useRef, useEffect } from "react";
import { MarkdownContent } from "../markdownContent.jsx";

export default function ChatComponent() {
    const t = useTranslations();

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
            setRenderedOutput(prev => prev + bufferRef.current);
            bufferRef.current = "";
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

            <div className="mt-3 fs-normal">
                <div className={`typing-dots ${typing ? "" : "hidden"}`}>
                    <span></span><span></span><span></span>
                </div>

                <div>
                    <MarkdownContent
                        id="chat-output"
                        content={liveText}
                    />
                </div>
            </div>
        </div>
    );
}