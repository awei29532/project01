@php
$menus = [
    [
        'title' => 'products',
        'allowRole' => ['admin', 'admin_sub'],
        'items' => [
            [
                'title' => 'providers',
                'href' => '/products/provider',
                'icon' => 'store-alt',
                'allowRole' => ['admin', 'admin_sub'],
            ],
            [
                'title' => 'games',
                'href' => '/products/game',
                'icon' => 'gamepad',
                'allowRole' => ['admin', 'admin_sub'],
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
                'allowRole' => ['admin', 'admin_sub'],
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
                'allowRole' => ['admin', 'agent'],
            ],
            [
                'title' => 'users',
                'href' => '/accounts/user',
                'icon' => 'user',
                'allowRole' => ['admin'],
            ]
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
                @if (isset($menu['allowRole']) && !Auth::user()->hasRole($menu['allowRole']))
                    @continue
                @else
                    <li class="nav-title">{{trans('title.' . $menu['title'])}}</li>
                @endif
                    
                @foreach ($menu['items'] as $item)
                    @if (isset($item['allowRole']) && !Auth::user()->hasRole($item['allowRole']))
                        @continue
                    @else
                        <li class="nav-item">
                            <a class="nav-link {{$item['title'] == $active ? 'active' : ''}}" href="{{$item['href']}}">
                                <i class="nav-icon fas fa-{{$item['icon']}}"></i></i> {{trans('title.' . $item['title'])}}
                            </a>
                        </li>
                    @endif
                @endforeach
            @endforeach
        </ul>
    </nav>
</div>