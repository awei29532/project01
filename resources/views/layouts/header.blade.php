<header class="app-header navbar">
    <sidebar-toggler class="d-lg-none" display="md" mobile></sidebar-toggler>
    <sidebar-toggler class="d-md-down-none" display="lg" :defaultOpen=true></sidebar-toggler>
    <b-navbar-nav>
        <header-dropdown right>
            <template slot="header">
                <i class="fas fa-user"></i> {{ Auth::user()->username }}
            </template>
            <template slot="dropdown">
                <b-dropdown-header tag="div" class="text-center">
                    <strong>{{trans('title.setting')}}</strong>
                </b-dropdown-header>

                @if (Auth::user()->hasRole('agent'))
                    <b-dropdown-item href="/setting">
                        <i class="fas fa-cog"></i>{{trans('title.setting')}}
                    </b-dropdown-item>
                @endif

                <b-dropdown-item href="/log">
                    <i class="fas fa-history"></i>{{trans('title.log')}}
                </b-dropdown-item>

                <b-dropdown-item href="/change-password">
                    <i class="fas fa-shield-alt"></i></i>{{trans('title.change_password')}}
                </b-dropdown-item>
                <b-dropdown-item href="/api/logout">
                    <i class="fas fa-sign-out-alt"></i></i>{{trans('title.logout')}}
                </b-dropdown-item>
            </template>
        </header-dropdown>
        <header-dropdown right class="m-2">
            <template slot="header">
                <i class="fas fa-language"></i> {{trans('login.lang')}}
            </template>
            <template slot="dropdown">
                <b-dropdown-item href="javascript:" onclick="window.changeLang('en')">{{trans('common.lang.en')}}</b-dropdown-item>
                <b-dropdown-item href="javascript:" onclick="window.changeLang('zh-Hans')">{{trans('common.lang.zh-Hans')}}</b-dropdown-item>
            </template>
        </header-dropdown>
    </b-navbar-nav>
</header>