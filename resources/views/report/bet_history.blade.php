@extends('layouts.main', [
    'langs' => ['common', 'report'],
    'title' => 'bet_history',
    'active' => 'bet_history',
])

@section('content')
    <script id="agent-list" type="application/json">@json($agents)</script>
    <script id="game-list" type="application/json">@json($games)</script>
    @include('layouts.breadcrumb', [
        'cur' => "bet_history",
        'pageArr' => [
            [
                'title' => 'dashboard',
                'href' => 'dashboard',
            ],
        ]
    ])
    <bethistory-component></bethistory-component>
@endsection
