@php
$menus = [
    [
        'title' => 'products',
        'items' => [
            [
                'title' => 'providers',
                'href' => '/products/provider',
                'icon' => 'store-alt',
            ],
            [
                'title' => 'games',
                'href' => '/products/game',
                'icon' => 'gamepad',
            ],
        ],
    ],
    [
        'title' => 'accounts',
        'items' => [
            [
                'title' => 'agents',
                'href'  => '/accounts/agent',
                'icon'  => 'users',
            ],
            [
                'title' => 'members',
                'href'  => '/accounts/member',
                'icon'  => 'address-book',
            ],
            [
                'title' => 'sub_accounts',
                'href'  => '/accounts/sub_account',
                'icon'  => 'user-friends',
            ],
        ],
    ],
    [
        'title' => 'report',
        'items' => [
            [
                'title' => 'win_lose',
                'href'  => '/report/win_lose',
                'icon'  => 'money-bill-wave',
            ],
            [
                'title' => 'bet_history',
                'href'  => '/report/bet_history',
                'icon'  => 'coins',
            ],
            [
                'title' => 'all_report',
                'href'  => '/report/all_report',
                'icon'  => 'chart-bar',
            ],
            [
                'title' => 'all_report_sw',
                'href'  => '/report/all_report_sw',
                'icon'  => 'chart-pie',
            ],
            [
                'title' => 'transfer',
                'href'  => '/report/transfer',
                'icon'  => 'search-dollar',
            ],
            [
                'title' => 'wallet',
                'href'  => '/report/wallet',
                'icon'  => 'wallet',
            ],

        ],
    ],
];
@endphp
<div class="sidebar">
    <nav class="sidebar-nav">
        <ul class="nav">
            <li class="nav-item">
                <a class="nav-link {{$title == 'dashboard' ? 'active' : ''}}" href="/dashboard">
                    <i class="nav-icon fas fa-home"></i> {{trans('title.dashboard')}}
                </a>
            </li>
            @foreach ($menus as $menu)
                <li class="nav-title">{{trans('title.' . $menu['title'])}}</li>
                @foreach ($menu['items'] as $item)
                    <li class="nav-item">
                        <a class="nav-link {{$item['title'] == $active ? 'active' : ''}}" href="{{$item['href']}}">
                            <i class="nav-icon fas fa-{{$item['icon']}}"></i></i> {{trans('title.' . $item['title'])}}
                        </a>
                    </li>
                @endforeach
            @endforeach
        </ul>
    </nav>
</div>