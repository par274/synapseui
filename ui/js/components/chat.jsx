import { useTranslations } from "../helpers/translations.jsx";
import { MarkdownContent } from "../helpers/markdown-content.jsx";

import { useState, useRef, useEffect } from "react";

export default function ChatComponent() {
    const t = useTranslations();

    const [userMessage, setUserMessage] = useState(t["chat.input.text"]);
    const [typing, setTyping] = useState(false);
    const [liveTextNormal, setLiveTextNormal] = useState("");
    const [liveTextThinking, setLiveTextThinking] = useState("");
    const [thinkingAreaExpanded, setThinkingAreaExpanded] = useState(false);

    const eventSourceRef = useRef(null);
    const bufferRef = useRef("");
    const tokenQueueRef = useRef([]);
    const runningRef = useRef(false);
    const endCheckTimerRef = useRef(null);
    const inThinkingRef = useRef(false);
    const lastTokenTimeRef = useRef(0);
    const characterRateRef = useRef(80);
    const totalTokensReceivedRef = useRef(0);
    const streamStartTimeRef = useRef(0);
    const displaySpeedRef = useRef(80);
    const streamEndedRef = useRef(false);

    const processQueue = () => {
        if (tokenQueueRef.current.length === 0 && bufferRef.current.length === 0) {
            runningRef.current = false;
            return;
        }

        runningRef.current = true;

        if (!bufferRef.current.length && tokenQueueRef.current.length > 0) {
            const next = tokenQueueRef.current.shift();
            bufferRef.current = next.text.split("");
            bufferRef.currentThinking = next.thinking;
        }

        if (bufferRef.current.length > 0) {
            const queueLength = tokenQueueRef.current.length;

            let speedMultiplier = 1;

            if (streamEndedRef.current && queueLength === 0) {
                speedMultiplier = 10;
            } else if (queueLength > 20) {
                speedMultiplier = 4;
            } else if (queueLength > 15) {
                speedMultiplier = 3.2;
            } else if (queueLength > 10) {
                speedMultiplier = 2.5;
            } else if (queueLength > 5) {
                speedMultiplier = 1.8;
            } else if (queueLength > 2) {
                speedMultiplier = 1.3;
            } else if (queueLength > 0) {
                speedMultiplier = 2.2;
            } else {
                speedMultiplier = streamEndedRef.current ? 10 : 1.4;
            }

            const baseSpeed = Math.min(180, Math.max(15, characterRateRef.current));
            const targetSpeed = baseSpeed * speedMultiplier;

            const currentDisplaySpeed = displaySpeedRef.current;
            const speedDiff = targetSpeed - currentDisplaySpeed;

            let maxSpeedChange;
            if (speedDiff > 0) {
                if (streamEndedRef.current) {
                    maxSpeedChange = Math.max(20, Math.abs(speedDiff) * 0.6);
                } else {
                    maxSpeedChange = Math.max(10, Math.abs(speedDiff) * 0.35);
                }
            } else {
                maxSpeedChange = Math.max(5, Math.abs(speedDiff) * 0.15);
            }

            let newDisplaySpeed;
            if (Math.abs(speedDiff) <= maxSpeedChange) {
                newDisplaySpeed = targetSpeed;
            } else if (speedDiff > 0) {
                newDisplaySpeed = currentDisplaySpeed + maxSpeedChange;
            } else {
                newDisplaySpeed = currentDisplaySpeed - maxSpeedChange;
            }

            displaySpeedRef.current = newDisplaySpeed;

            let charsPerFrame = Math.min(12, Math.max(1, Math.floor(newDisplaySpeed / 45)));

            if (streamEndedRef.current && (queueLength <= 1 || bufferRef.current.length <= 20)) {
                charsPerFrame = Math.min(20, Math.max(5, Math.floor(newDisplaySpeed / 25)));
            }

            const charsToShow = Math.min(bufferRef.current.length, charsPerFrame);
            const chunk = bufferRef.current.splice(0, charsToShow).join("");

            if (bufferRef.currentThinking) {
                setLiveTextThinking((prev) => prev + chunk);
            } else {
                setLiveTextNormal((prev) => prev + chunk);
            }

            let targetFPS = Math.min(80, Math.max(20, newDisplaySpeed / 3));

            if (streamEndedRef.current && (queueLength <= 1 || bufferRef.current.length <= 20)) {
                targetFPS = Math.min(120, Math.max(40, newDisplaySpeed / 2));
            }

            const frameDelay = 1000 / targetFPS;
            const finalDelay = Math.max(frameDelay, 8);

            setTimeout(processQueue, finalDelay);
        } else if (tokenQueueRef.current.length > 0) {
            setTimeout(processQueue, 5);
        } else {
            runningRef.current = false;
        }
    };

    const enqueueToken = (token) => {
        while (token) {
            if (token.includes("<think>")) {
                const [before, after] = token.split("<think>", 2);
                if (before) tokenQueueRef.current.push({ text: before, thinking: inThinkingRef.current });
                inThinkingRef.current = true;
                token = after;
            } else if (token.includes("</think>")) {
                const [thinkingPart, after] = token.split("</think>", 2);
                if (thinkingPart) tokenQueueRef.current.push({ text: thinkingPart, thinking: true });
                inThinkingRef.current = false;
                tokenQueueRef.current.push({ text: "\n\n", thinking: false });
                token = after;
            } else {
                tokenQueueRef.current.push({ text: token, thinking: inThinkingRef.current });
                token = "";
            }
        }

        if (!runningRef.current) processQueue();
    };

    const flushBuffer = () => {
        streamEndedRef.current = true;

        if (runningRef.current) {
            return;
        }

        if (tokenQueueRef.current.length > 0 || bufferRef.current.length > 0) {
            runningRef.current = true;
            processQueue();
        }
    };

    const handleSend = async () => {
        const msg = userMessage.trim();
        if (!msg) return;

        setLiveTextNormal("");
        setLiveTextThinking("");
        setTyping(true);

        streamStartTimeRef.current = 0;
        totalTokensReceivedRef.current = 0;
        lastTokenTimeRef.current = performance.now();
        characterRateRef.current = 80;
        displaySpeedRef.current = 80;
        streamEndedRef.current = false;

        if (eventSourceRef.current) eventSourceRef.current.close();

        bufferRef.current = "";
        tokenQueueRef.current = [];
        runningRef.current = false;
        inThinkingRef.current = false;

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
                        setTimeout(() => {
                            flushBuffer();
                        }, 50);
                        es.close();
                        return;
                    }

                    let payload;
                    try {
                        payload = JSON.parse(event.data);
                    } catch (e) {
                        console.error(`[${adapter}] JSON parse error`, e, event.data);
                        payload = event.data;
                    }

                    let token;
                    try {
                        token = decode(payload);
                        if (token === null || token === undefined) token = "";
                        else if (typeof token !== "string") token = JSON.stringify(token);
                    } catch (e) {
                        console.error(`[${adapter}] Decode error`, e, payload);
                        token = "";
                    }

                    const now = performance.now();

                    if (totalTokensReceivedRef.current === 0) {
                        streamStartTimeRef.current = now;
                        lastTokenTimeRef.current = now;
                    }

                    totalTokensReceivedRef.current++;

                    if (token.length > 0) {
                        const streamDuration = (now - streamStartTimeRef.current) / 1000;
                        const totalChars = (liveTextNormal.length + liveTextThinking.length + token.length);

                        if (streamDuration > 0.1) {
                            const overallSpeed = totalChars / streamDuration;
                            characterRateRef.current = characterRateRef.current * 0.8 + overallSpeed * 0.2;
                            characterRateRef.current = Math.min(300, Math.max(10, characterRateRef.current));
                        }
                    }

                    lastTokenTimeRef.current = now;
                    enqueueToken(token);
                });
            });

            es.onclose = () => {
                setTyping(false);
                flushBuffer();
            };

            es.onerror = () => {
                setTyping(false);
                flushBuffer();
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
                    {liveTextThinking && (
                        <div className="thinking bg-adaptive pb-0 mb-3 border-start border-3 rounded fs-small">
                            {inThinkingRef.current ? (
                                <div className="d-flex align-items-center mb-2 p-2 pb-0">
                                    <div>
                                        <span aria-hidden="true" className="spinner-border spinner-border-xs text-light"></span>
                                    </div>
                                    <div className="ms-2">
                                        <span className="text-muted">Thinking…</span>
                                    </div>
                                </div>
                            ) : (
                                <div className="d-flex align-items-center mb-2 p-2 pb-0">
                                    <div>
                                        <span className="text-light">Think complete</span>
                                    </div>
                                </div>
                            )}
                            <div
                                className="d-flex flex-column-reverse p-2 pb-0"
                                style={{
                                    maxHeight: thinkingAreaExpanded ? "1000px" : "80px"
                                }}
                            >
                                <div>
                                    <MarkdownContent content={liveTextThinking} />
                                </div>
                            </div>
                            <div className="d-flex align-items-center justify-content-center border-top position-relative">
                                <a
                                    href="#"
                                    className="link-light link-offset-2 link-underline-opacity-25 link-underline-opacity-75-hover mx-auto py-1"
                                    onClick={(e) => {
                                        e.preventDefault();
                                        setThinkingAreaExpanded(prev => !prev);
                                    }}
                                >
                                    <span className="label fs-small">
                                        {thinkingAreaExpanded ? "Daralt" : "Genişlet"}
                                    </span>
                                </a>
                            </div>
                        </div>
                    )}
                    <MarkdownContent content={liveTextNormal} />
                </div>
            </div>
        </div>
    );
}