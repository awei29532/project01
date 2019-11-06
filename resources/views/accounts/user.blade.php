@extends('layouts.main', [
    'langs' => ['common', 'users'],
    'title' => 'users',
    'active' => 'users',
])

@section('content')
    <script id="role-list" type="application/json">@json($roles)</script>
    @include('layouts.breadcrumb', [
        'cur' => "users",
        'pageArr' => [
            [
                'title' => 'dashboard',
                'href' => 'dashboard',
            ],
        ]
    ])
    <user-component></user-component>
@endsection
