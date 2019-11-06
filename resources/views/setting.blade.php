@extends('layouts.main', [
    'langs' => ['common', 'title', 'setting', 'agents'],
    'title' => 'setting',
    'active' => 'setting',
])

@section('content')
    <script id="setting-data" type="application/json">@json($setting)</script>
    @include('layouts.breadcrumb', [
        'cur' => "setting",
        'pageArr' => [
            [
                'title' => 'dashboard',
                'href' => 'dashboard',
            ],
        ]
    ])
    <setting-component></setting-component>
@endsection
