<sx:extends template="main.tpl" />

<sx:block name="appContainer">
    <div class="container">
        <div class="w-100 w-75-lg mx-auto-lg px-4 fg-gray">
            <h1 class="text-4xl mb-10">Streaming test</h1>
            <div class="form-group flex-row">
                <input class="mr-1" id="userMessage" type="text" value="what is that mean in coding hello world!">
                <button class="button info" id="sendBtn">Send</button>
            </div>
            <div class="mt-5" id="chatOutput"></div>
            <div class="typing-dots hidden">
                <span></span><span></span><span></span>
            </div>

            <div id="liveDiv" class="text-gray-300 whitespace-pre-wrap break-words">
            </div>
        </div>
    </div>
    <script src="{$app.config->asset('appjs/chat.jsx')}" type="module"></script>
</sx:block>