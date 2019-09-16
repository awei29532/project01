@extends('layouts.main', [
    'langs' => ['common', 'report'],
    'title' => 'wallet',
    'active' => 'wallet',
])

@section('content')
    <script id="agent-list" type="application/json">@json($agents)</script>
    <script id="provider-list" type="application/json">@json($providers)</script>
    <script id="game-list" type="application/json">@json($games)</script>
    @include('layouts.breadcrumb', [
        'cur' => "wallet",
        'pageArr' => [
            [
                'title' => 'dashboard',
                'href' => 'dashboard',
            ],
        ]
    ])
    <wallet-component></wallet-component>
@endsection
