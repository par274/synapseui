<sx:extends template="main.tpl" />

<sx:block name="appContainer">
    <div class="container">
        <div class="w-100 w-75-lg mx-auto-lg px-4 fg-gray">
            <h1 class="text-4xl mb-10">Streaming test</h1>
            <div class="chat-root"></div>
        </div>
    </div>
    <script src="{$app.config->asset('appjs/chat.jsx')}" type="text/babel" data-type="module"></script>
</sx:block>