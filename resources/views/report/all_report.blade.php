@extends('layouts.main', [
    'langs' => ['common', 'report'],
    'title' => 'all_report',
    'active' => 'all_report',
])

@section('content')
    <script id="agent-list" type="application/json">@json($agents)</script>
    @include('layouts.breadcrumb', [
        'cur' => "all_report",
        'pageArr' => [
            [
                'title' => 'dashboard',
                'href' => 'dashboard',
            ],
        ]
    ])
    <allreport-component :userid="{{ $userAgentId }}"></allreport-component>
@endsection
