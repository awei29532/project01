@extends('layouts.main', [
    'langs' => ['common', 'report'],
    'title' => 'all_report_sw',
    'active' => 'all_report_sw',
])

@section('content')
    <script id="agent-list" type="application/json">@json($agents)</script>
    <script id="provider-list" type="application/json">@json($providers)</script>
    @include('layouts.breadcrumb', [
        'cur' => "all_report_sw",
        'pageArr' => [
            [
                'title' => 'dashboard',
                'href' => 'dashboard',
            ],
        ]
    ])
    <allreportsw-component :userid="{{ $userAgentId }}"></allreportsw-component>
@endsection
