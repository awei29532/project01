@inject('com', 'App\Presenters\CommonPresenter')

<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">

<head>
    <title>IFUN Gaming | {{ trans("title." . $title) }}</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no">
    <meta name="robots" content="noindex">
    <link rel="stylesheet" href="{{ mix('/css/app.css') }}">
</head>

<body class="{{ isset($bodyClass) ? $bodyClass : 'app header-fixed sidebar-fixed sidebar-show breadcrumb-fixed' }}">
    <div id="app" style="display:contents">
        @if (isset($loginPage) && $loginPage)
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
        
        @if (!isset($loginPage) || !$loginPage)
            var user = @json(Auth::user());
            var isAdmin = {{ Auth::user()->hasRole('admin') ? 'true' : 'false' }};
            var isAdminSub = {{ Auth::user()->hasRole('admin_sub') ? 'true' : 'false' }};
        @endif
        
        window.changeLang = function (lang) {
            axios.get('/lang', {
                params: {
                    lang: lang,
                }
            }).then(res => {
                location.reload();
            });
        }
        
    </script>
    <script src="{{mix('/js/app.js')}}"></script>
</body>

</html>