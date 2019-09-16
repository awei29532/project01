@php
    $title = ($agent_id == 0 ? 'agent_add' : 'agent_edit');
@endphp

@extends('layouts.main', [
    'langs' => ['common', 'agents'],
    'title' => $title,
    'active' => 'agents',
])

@section('content')
    <script id="currency-list" type="application/json">@json($currencies)</script>
    <script id="provider-list" type="application/json">@json($providers)</script>
    <script id="agent-data" type="application/json">@json($agent)</script>
    @include('layouts.breadcrumb', [
        'cur' => $title,
        'pageArr' => [
            [
                'title' => 'dashboard',
                'href' => 'dashboard',
            ],
            [
                'title' => 'agents',
                'href' => 'accounts/agent',
            ]
        ]
    ])
    <agentedit-component agentid="{{$agent_id}}"></agentedit-component>
@endsection
