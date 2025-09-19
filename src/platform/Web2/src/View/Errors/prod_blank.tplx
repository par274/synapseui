<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">

    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        body {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: #000;
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto,
                "Helvetica Neue", Arial, sans-serif;
            text-align: center;
        }

        .message {
            margin-bottom: 7rem;
            font-size: .8rem;
        }

        .spinner-wrapper {
            position: relative;
            width: 100px;
            height: 100px;
        }

        .spinning-box {
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0;
            left: 0;
            animation: animationSpin 2s linear infinite, animationBackground 5s linear infinite;
            mix-blend-mode: screen;
        }

        .spinning-box:nth-child(2) {
            opacity: 0.8;
            animation: animationSpin 3s linear infinite, animationBackground 5s linear infinite;
            animation-delay: 1s;
        }

        .spinning-box:nth-child(3) {
            opacity: 0.6;
            animation: animationSpin 4s linear infinite, animationBackground 5s linear infinite;
            animation-delay: 2s;
        }

        .spinning-box:nth-child(4) {
            opacity: 0.4;
            animation: animationSpin 5s linear infinite, animationBackground 5s linear infinite;
            animation-delay: 3s;
        }

        .spinning-box:nth-child(5) {
            opacity: 0.2;
            animation: animationSpin 6s linear infinite, animationBackground 5s linear infinite;
            animation-delay: 4s;
        }

        .spinning-box:nth-child(6) {
            opacity: 0.4;
            animation: animationSpin 7s linear infinite;
            background: black;
            box-shadow: 0px 0px 25px 12.5px black;
        }

        @keyframes animationSpin {
            from {
                transform: rotateZ(0deg);
            }

            to {
                transform: rotateZ(360deg);
            }
        }

        @keyframes animationBackground {
            0% {
                background: red;
            }

            25% {
                background: yellow;
            }

            50% {
                background: lightblue;
            }

            75% {
                background: slateblue;
            }

            100% {
                background: red;
            }
        }
    </style>
</head>

<body>
    <div class="message">An unexpected error occurred. Please try again later.</div>
    <div class="spinner-wrapper">
        <div class="spinning-box"></div>
        <div class="spinning-box"></div>
        <div class="spinning-box"></div>
        <div class="spinning-box"></div>
        <div class="spinning-box"></div>
        <div class="spinning-box"></div>
    </div>
</body>

</html>