@extends('layouts.main', [
    'langs' => [ 'dashboard' ],
    'title' => 'dashboard',
    'active' => 'dashboard',
])

@section('content')
    <script id="currencys" type="application/json">@json($currencys)</script>
    <script id="players" type="application/json">@json($players)</script>
    <script id="profit" type="application/json">@json($profit)</script>
    @include('layouts.breadcrumb', [
        'cur' => 'dashboard',
        'pageArr' => [],
    ])
    <dashboard-component></dashboard-component>
@endsection
