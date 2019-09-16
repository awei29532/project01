@php
    $title = ($provider_id == 0 ? 'provider_add' : 'provider_edit');
@endphp

@extends('layouts.main', [
    'langs' => ['common', 'providers'],
    'title' => $title,
    'active' => 'providers',
])

@section('content')
    <script id="provider-data" type="application/json">@json($provider)</script>
    @include('layouts.breadcrumb', [
        'cur' => $title,
        'pageArr' => [
            [
                'title' => 'dashboard',
                'href' => 'dashboard',
            ],
            [
                'title' => 'providers',
                'href' => 'products/provider',
            ]
        ]
    ])
    <provider-edit-component providerid="{{$provider_id}}"></provider-edit-component>
@endsection
