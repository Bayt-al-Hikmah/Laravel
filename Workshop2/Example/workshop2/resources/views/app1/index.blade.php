@extends('layouts.app')

@section('content')
    <h1>Welcome {{ $username }}!</h1>
    <p>You are {{ $age }} years old.</p>
@endsection