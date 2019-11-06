@extends('layouts.main', [
    'langs' => ['common', 'title', 'log'],
    'title' => 'log',
    'active' => 'log',
])

@section('content')
    @include('layouts.breadcrumb', [
        'cur' => "log",
        'pageArr' => [
            [
                'title' => 'dashboard',
                'href' => 'dashboard',
            ],
        ]
    ])
    <log-component></log-component>
@endsection
