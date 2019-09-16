@php
    $title = ($game_id == 0 ? 'game_add' : 'game_edit');
@endphp

@extends('layouts.main', [
    'langs' => ['common', 'games'],
    'title' => $title,
    'active' => 'games',
])

@section('content')
<script id="game-data" type="application/json">@json($game)</script>
<script id="providers" type="application/json">@json($providers)</script>
    @include('layouts.breadcrumb', [
        'cur' => $title,
        'pageArr' => [
            [
                'title' => 'dashboard',
                'href' => 'dashboard',
            ],
            [
                'title' => 'games',
                'href' => 'products/game',
            ]
        ]
    ])
    <game-edit-component gameid="{{$game_id}}"></game-edit-component>
@endsection
