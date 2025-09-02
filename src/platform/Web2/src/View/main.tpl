<!DOCTYPE html>
<html data-bs-theme="dark">

<head>
    <title>Synapse Template Demo</title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1" />

    <link rel="stylesheet" href="{$app.config->asset('app.bundle.css')}" />
</head>

<body>
    <sx:block name="appContainer">

    </sx:block>

    <script type="module" src="{$app.config->asset('app.bundle.js')}"></script>

    <script>
        window.app = {
            config: {
                test: "{$app.config->asset('app.bundle.js')}"
            }
        };
    </script>
</body>

</html>