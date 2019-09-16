@extends('layouts.main', [
    'langs' => ['common', 'games'],
    'title' => 'games',
    'active' => 'games',
])

@section('content')
    <script id="provider-list" type="application/json">@json($provider)</script>
    @include('layouts.breadcrumb', [
        'cur' => 'games',
        'pageArr' => [
            [
                'title' => 'dashboard',
                'href' => 'dashboard',
            ]
        ]
    ])
    <game-component></game-component>
@endsection
