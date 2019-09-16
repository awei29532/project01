@extends('layouts.main', [
    'langs' => ['common', 'providers'],
    'title' => 'providers',
    'active' => 'providers',
])

@section('content')
    @include('layouts.breadcrumb', [
        'cur' => 'providers',
        'pageArr' => [
            [
                'title' => 'dashboard',
                'href' => 'dashboard',
            ]
        ]
    ])
    <provider-component></provider-component>
@endsection
