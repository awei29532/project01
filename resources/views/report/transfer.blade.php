@extends('layouts.main', [
    'langs' => ['common', 'report'],
    'title' => 'transfer',
    'active' => 'transfer',
])

@section('content')
    <script id="agent-list" type="application/json">@json($agents)</script>
    @include('layouts.breadcrumb', [
        'cur' => "transfer",
        'pageArr' => [
            [
                'title' => 'dashboard',
                'href' => 'dashboard',
            ],
        ]
    ])
    <transfer-component :userid="{{ $userAgentId }}"></transfer-component>
@endsection
