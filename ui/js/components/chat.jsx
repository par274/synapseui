import { useTranslations } from "../helpers/translations.jsx";
import { MarkdownContent } from "../helpers/markdown-content.jsx";

import { useState, useRef, useEffect } from "react";

export default function ChatComponent() {
    const t = useTranslations();

    const [userMessage, setUserMessage] = useState(t["chat.input.text"]);
    const [liveText, setLiveText] = useState("");
    const [typing, setTyping] = useState(false);

    const eventSourceRef = useRef(null);
    const bufferRef = useRef("");
    const tokenQueueRef = useRef([]);
    const runningRef = useRef(false);
    const endCheckTimerRef = useRef(null);

    const processQueue = () => {
        if (tokenQueueRef.current.length === 0 && bufferRef.current.length === 0) {
            runningRef.current = false;
            return;
        }

        runningRef.current = true;

        // Buffer boşsa queue'dan token al
        if (!bufferRef.current.length && tokenQueueRef.current.length > 0) {
            const nextToken = tokenQueueRef.current.shift();
            if (typeof nextToken === "string") {
                bufferRef.current = nextToken.split("");
            }
        }

        // Buffer’dan küçük batch al
        if (bufferRef.current.length > 0) {
            const batchSize = Math.min(4, bufferRef.current.length);
            const chunk = bufferRef.current.splice(0, batchSize);
            setLiveText(prev => prev + chunk.join(""));

            // Çok kısa micro-delay ile bir sonraki frame’e geç
            setTimeout(() => requestAnimationFrame(processQueue), 2);
        } else {
            requestAnimationFrame(processQueue);
        }
    };

    const handleSend = async () => {
        const msg = userMessage.trim();
        if (!msg) return;

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
                setTyping(false);
            };
        } catch (err) {
            console.error("POST error:", err);
            setTyping(false);
            return;
        }
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

                <div className="output">
                    <MarkdownContent content={liveText} />
                </div>
            </div>
        </div>
    );
}