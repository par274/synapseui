<!DOCTYPE html>
<html class="dark-side">

<head>
    <title>Synapse Template Demo</title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1" />

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />

    <link href="{$app.config->asset('appcss/style.css')}?v=1" rel="stylesheet" type="text/css" />

    <link rel="stylesheet" href="{$app.config->asset('vendor/metroui/metroui.css')}" />
</head>

<body class="d-flex flex-align-center flex-justify-center">
    <sx:block name="appContainer">

    </sx:block>
    <script src="{$app.config->asset('vendor/metroui/metroui.js')}"></script>

    <script src="{$app.config->asset('vendor/react/react.production.min.js')}" type="text/javascript"></script>
    <script src="{$app.config->asset('vendor/react/react-dom.production.min.js')}" type="text/javascript"></script>

    <script src="{$app.config->asset('appjs/app.js')}" type="text/javascript"></script>
</body>

</html>