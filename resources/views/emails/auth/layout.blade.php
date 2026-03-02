@extends('emails.layout')

@section('footer-links')
    <a href="{{ url('/') }}">Home</a>
    <a href="{{ url('/login') }}">Login</a>
    <a href="{{ url('/contact') }}">Support</a>
@endsection
