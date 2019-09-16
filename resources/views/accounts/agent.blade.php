@extends('layouts.main', [
    'langs' => ['common', 'agents'],
    'title' => 'agents',
    'active' => 'agents',
])

@section('content')
    @include('layouts.breadcrumb', [
        'cur' => 'agents',
        'pageArr' => [
            [
                'title' => 'dashboard',
                'href' => 'dashboard',
            ]
        ]
    ])
    <agent-component></agent-component>
@endsection
