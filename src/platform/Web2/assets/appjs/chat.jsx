import MarkdownIt from "../vendor/markdown-it/index.js";

const { useState, useEffect, useRef } = React;

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
    const [inputValue, setInputValue] = useState("");
    const [messages, setMessages] = useState([]);
    const [liveMessage, setLiveMessage] = useState("");
    const eventSourceRef = useRef(null);

    useEffect(() => {
        const input = document.getElementById("userMessage");
        const btn = document.getElementById("sendBtn");
        const output = document.getElementById("chatOutput");
        const liveDiv = document.getElementById("liveDiv");

        const handleSend = () => {
            const message = input.value.trim();
            if (!message) return;

            setMessages([]);
            setLiveMessage("");
            if (eventSourceRef.current) eventSourceRef.current.close();

            let buffer = "";
            let html = "";
            const tokenQueue = [];
            let running = false;

            const tryRender = (force = false) => {
                const codeBlockOpen = (buffer.match(/```/g) || []).length % 2 !== 0;
                if (!force && codeBlockOpen) return;
                if (buffer.trim()) {
                    const rendered = addTailwindClasses(md.render(buffer));
                    html += rendered;
                    output.innerHTML = html; // tpl dosyasındaki elemente yazıyoruz
                    buffer = "";
                    liveDiv.textContent = "";
                }
            };

            const processQueue = () => {
                if (!tokenQueue.length) {
                    running = false;
                    return;
                }
                running = true;
                const token = tokenQueue.shift();
                buffer += token;
                liveDiv.textContent += token; // tpl dosyasındaki element
                if (/\n\n$/.test(buffer) || /```$/.test(buffer)) tryRender();
                setTimeout(processQueue, 25);
            };

            eventSourceRef.current = new EventSource(`/chat?message=${encodeURIComponent(message)}`);
            eventSourceRef.current.onmessage = (event) => {
                if (event.data === "END-OF-STREAM") {
                    tryRender(true);
                    eventSourceRef.current.close();
                    return;
                }
                try {
                    const payload = JSON.parse(event.data);
                    let token = payload.token?.message?.content ?? payload.token;
                    if (typeof token !== "string") token = JSON.stringify(token);

                    tokenQueue.push(token);
                    if (!running) processQueue();
                } catch (e) {
                    console.error("JSON parse error", e, event.data);
                }
            };
            eventSourceRef.current.onerror = console.error;
        };

        btn.addEventListener("click", handleSend);

        return () => {
            btn.removeEventListener("click", handleSend);
            if (eventSourceRef.current) eventSourceRef.current.close();
        };
    }, []);

    return null;
}

ReactDOM.createRoot(document.getElementById('liveDiv')).render(React.createElement(window.ChatBehavior));