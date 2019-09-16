@extends('layouts.main', [
    'langs' => ['common', 'report'],
    'title' => 'win_lose',
    'active' => 'win_lose',
])

@section('content')
    <script id="agent-list" type="application/json">@json($agents)</script>
    @include('layouts.breadcrumb', [
        'cur' => "win_lose",
        'pageArr' => [
            [
                'title' => 'dashboard',
                'href' => 'dashboard',
            ],
        ]
    ])
    <winlose-component :userid="{{ $userAgentId }}"></winlose-component>
@endsection
