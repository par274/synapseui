<sx:extends template="main.tpl" />

<sx:block name="appContainer">
    <div class="m-10 w-lg fg-gray">
        <h1 class="text-4xl mb-10">Streaming test</h1>
        <div class="form-group flex-row">
            <input class="mr-1" id="userMessage" type="text" value="what is that mean in coding hello world!">
            <button class="button info" id="sendBtn">Send</button>
        </div>
        <div class="mt-5" id="chatOutput"></div>

        <div id="liveDiv" class="text-gray-300 whitespace-pre-wrap break-words"></div>
    </div>

    <script src="{$app.config->asset('appjs/chat.jsx')}" type="module"></script>
</sx:block>