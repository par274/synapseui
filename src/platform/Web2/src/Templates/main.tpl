<!DOCTYPE html>
<html data-theme="dark">

<head>
    <title>Synapse Template Demo</title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1" />

    <sx:foreach loop="$app.ui->getStyles()" key="$i" value="$item">
        <link href="{$item}" rel="stylesheet" type="text/css" />
    </sx:foreach>
</head>

<body class="bg-base-200">
    <sx:block name="appContainer">

    </sx:block>
    <sx:foreach loop="$app.ui->getScripts()" key="$i" value="$item">
        <script src="{$item}" type="text/javascript"></script>
    </sx:foreach>
    <script src="{$app.config->asset('appjs/app.js')}" type="text/javascript"></script>
</body>

</html>