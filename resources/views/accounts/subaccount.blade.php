@extends('layouts.main', [
    'langs' => ['common', 'subs'],
    'title' => 'sub_accounts',
    'active' => 'sub_accounts',
])

@section('content')
    @include('layouts.breadcrumb', [
        'cur' => "sub_accounts",
        'pageArr' => [
            [
                'title' => 'dashboard',
                'href' => 'dashboard',
            ],
        ]
    ])
    <sub-component></sub-component>
@endsection
