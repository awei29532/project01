@php
    $title = $sub_id == 0 ? 'sub_account_add' : 'sub_account_edit';
@endphp

@extends('layouts.main', [
    'langs' => ['common', 'subs'],
    'title' => $title,
    'active' => 'sub_accounts',
])

@section('content')
    <script id="sub-data" type="application/json">@json($sub)</script>
        @include('layouts.breadcrumb', [
            'cur' => $title,
            'pageArr' => [
                [
                    'title' => 'dashboard',
                    'href' => 'dashboard',
                ],
                [
                    'title' => 'sub_accounts',
                    'href' => 'accounts/sub_account',
                ]
            ]
        ])
    <subedit-component subid="{{$sub_id}}"></subedit-component>
@endsection
