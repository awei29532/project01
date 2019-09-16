@extends('layouts.main', [
    'langs' => ['common', 'change_password', 'title'],
    'title' => 'change_password',
    'active' => 'change_password',
])

@section('content')
    @include('layouts.breadcrumb', [
        'cur' => "change_password",
        'pageArr' => [
            [
                'title' => 'dashboard',
                'href' => 'dashboard',
            ],
        ]
    ])
    <change-password-component></change-password-component>
@endsection
