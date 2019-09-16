<header class="app-header navbar">
    <sidebar-toggler class="d-lg-none" display="md" mobile></sidebar-toggler>
    <sidebar-toggler class="d-md-down-none" display="lg" :defaultOpen=true></sidebar-toggler>
    <b-navbar-nav>
        <app-header-dropdown right no-caret>
            <template slot="header">
                <i class="fas fa-user"></i>
            </template>
            <template slot="dropdown">
                <b-dropdown-item>
                    <i class="fas fa-cog"></i>{{trans('title.setting')}}
                </b-dropdown-item>
                <b-dropdown-item href="/change-password" no-caret>
                    <i class="fas fa-shield-alt"></i></i>{{trans('title.change_password')}}
                </b-dropdown-item>
                <b-dropdown-item href="/api/logout" no-caret>
                    <i class="fas fa-sign-out-alt"></i></i>{{trans('title.logout')}}
                </b-dropdown-item>
            </template>
        </app-header-dropdown>
    </b-navbar-nav>
</header>