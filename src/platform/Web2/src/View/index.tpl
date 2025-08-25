<sx:extends template="main.tpl" />

<sx:block name="appContainer">
    <div id="chatOutput"></div>
    <input id="userMessage" type="text" value="what is that mean in coding hello world!">
    <button id="sendBtn">Send</button>

    <script>
        document.getElementById('sendBtn').addEventListener('click', function () {
            const message = document.getElementById('userMessage').value.trim();
            if (!message) return;

            if (window.chatEventSource) window.chatEventSource.close();

            const output = document.getElementById('chatOutput');
            output.innerHTML = '';

            window.chatEventSource = new EventSource(`/chat?message=${encodeURIComponent(message)}`);

            const tokenQueue = [];
            let running = false;

            function processQueue() {
                if (!tokenQueue.length) {
                    running = false;
                    return;
                }
                running = true;

                const token = tokenQueue.shift();
                const span = document.createElement('span');
                span.textContent = token;
                span.style.opacity = 0;
                span.style.transition = 'opacity 0.12s linear';
                output.appendChild(span);

                requestAnimationFrame(() => {
                    span.style.opacity = 1;
                });

                setTimeout(processQueue, 30);
            }

            window.chatEventSource.onmessage = (event) => {
                console.log(event.data);
                if (event.data === 'END-OF-STREAM') {
                    window.chatEventSource.close();
                    return;
                }

                try {
                    const payload = JSON.parse(event.data);
                    let text = '';

                    if (typeof payload.token === 'string') {
                        text = payload.token;
                    } else if (payload.token?.message?.content) {
                        text = payload.token.message.content;
                    }

                    tokenQueue.push(text);
                    if (!running) processQueue();

                } catch (e) {
                    console.error('JSON parse error', e);
                }
            };

            window.chatEventSource.onerror = (err) => {
                console.error('SSE error', err);
            };
        });
    </script>
</sx:block>