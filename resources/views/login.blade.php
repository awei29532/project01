@extends('layouts.main', [
    'title' => 'login',
    'loginPage'   => true,
    'bodyClass' => '',
    'langs' => [ 'login', 'common' ],
    'active' => 'login'
])

@section('content')
<script id="captcha" type="text/plain">{{ $captcha }}</script>
<login-component></login-component>
@endsection
