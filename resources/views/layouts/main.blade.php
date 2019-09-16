@inject('com', 'App\Presenters\CommonPresenter')

<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">

<head>
    <title>IFUN Gaming | {{ trans("title." . $title) }}</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">

    <link rel="stylesheet" href="/css/app.css">
</head>

<body class="{{ isset($bodyClass) ? $bodyClass : 'app header-fixed sidebar-fixed sidebar-show breadcrumb-fixed' }}">
    <div id="app" style="display:contents">
        @if (isset($isLogin) && $isLogin)
            @yield('content')
        @else
            @include('layouts.header')
            <div class="app-body">
                @include('layouts.aside_menu')
                <main class="main">
                    @yield('content')
                </main>
            </div>
        @endif
    </div>
    <script>
        window.langs = [ 'en', 'zh-Hans' ];
        @if(isset($langs))
            window.i18n = @json($com->getLangPacks($langs));
        @else
            window.i18n = {};
        @endif
    </script>
    <script src="/js/app.js"></script>
</body>

</html>