@extends('emails.layout')

@section('footer-links')
    <a href="{{ url('/') }}">Home</a>
    <a href="{{ url('/my-orders') }}">My Orders</a>
    <a href="{{ url('/contact') }}">Support</a>
@endsection
