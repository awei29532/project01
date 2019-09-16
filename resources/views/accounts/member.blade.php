@extends('layouts.main', [
    'langs' => ['common', 'members'],
    'title' => 'members',
    'active' => 'members',
])

@section('content')
    @include('layouts.breadcrumb', [
        'cur' => "members",
        'pageArr' => [
            [
                'title' => 'dashboard',
                'href' => 'dashboard',
            ],
        ]
    ])
    <member-component></member-component>
@endsection
