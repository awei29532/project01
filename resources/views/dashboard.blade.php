@extends('layouts.main', [
    'langs' => [ 'common' ],
    'title' => 'dashboard',
    'active' => 'dashboard',
])

@section('content')
    @include('layouts.breadcrumb', [
        'cur' => 'dashboard',
        'pageArr' => [],
    ])
@endsection
