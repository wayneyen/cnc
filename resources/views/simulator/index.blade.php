<!DOCTYPE html>
<html lang="zh-TW">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>CNC simulator</title>
    <link rel="stylesheet" href="/css/app.css?v=1.0.2-dev">
</head>

<body>
    <div id="app">
        <div class="row m-0 p-0">
            <div class="col-md-3 m-0 p-0">
                <control-panel
                    :type="1"
                    :input="2"
                    @submit="submit">
                </control-panel>
            </div>

            <div class="col-md-9 m-0 p-0">
                <header-panel :runtime="runtime"></header-panel>
                <div class="m-5 position-relative">
                    <grid-layout></grid-layout>
                    <simulator-result :result="result"></simulator-result>
                </div>
            </div>
        </div>
    </div>

    <script src="/js/app.js?v=1.0.2-dev"></script>

    <script>

    </script>
</body>

</html>
